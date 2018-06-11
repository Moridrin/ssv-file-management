// noinspection JSUnresolvedVariable

let FileManager = {
    params: mp_ssv_file_manager_params,
    $fileManager: undefined,
    allowEdit: false,
    maxUploadSize: undefined,
    currentPath: undefined,

    uploader: {

        fileCount: 0,

        FileSelectHandler: function (event) {
            event.preventDefault();
            document.getElementById('dropFilesLabel').style.display = 'none';
            let items = event.target.files;
            if (items) {
                for (let i = 0, item; item = items[i]; i++) {
                    FileManager.uploader.uploadFile(item);
                }
            } else {
                items = event.dataTransfer.items;
                for (let i = 0, item; item = items[i]; i++) {
                    FileManager.uploader.TraverseFileTree(item.webkitGetAsEntry(), FileManager.currentPath);
                }
            }
        },

        TraverseFileTree: function (item, path) {
            if (item.isFile) {
                item.file(function (file) {
                    FileManager.uploader.uploadFile(file, path);
                });
            } else if (item.isDirectory) {
                let formData = new FormData();
                formData.append('action', FileManager.params.actions['createFolder']);
                formData.append('path', path);
                formData.append('newFolderName', item.name);
                jQuery.ajax({
                    method: 'POST',
                    url: FileManager.params.urls.ajax,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function () {
                        let dirReader = item.createReader();
                        dirReader.readEntries(function (entries) {
                            for (let i = 0; i < entries.length; i++) {
                                FileManager.uploader.TraverseFileTree(entries[i], path + item.name + "/");
                            }
                        });
                    }
                });
            }
        },

        uploadFile: function (item, path) {
            // Create Upload Row
            let itemStateContainer = FileManager.uploader.createUploadProgressBar(item);
            document.getElementById('uploadingFilesList').appendChild(FileManager.uploader.createUploadProgressRow(item, itemStateContainer));

            // Check File Limitations
            if (item.size > FileManager.maxUploadSize) {
                itemStateContainer.innerHTML = '<span title="File too large">&#10060;</span>';
                return;
            }

            // Add file to queue
            ++FileManager.uploader.fileCount;

            // Create Form Data
            let formData = new FormData();
            formData.append('action', FileManager.params.actions['uploadFile']);
            formData.append('path', path || FileManager.currentPath);
            formData.append('file', item);
            formData.append('fileName', item.name);

            // Ajax Call
            jQuery.ajax({
                method: 'POST',
                url: FileManager.params.urls.ajax,
                data: formData,
                contentType: false,
                processData: false,
                success: function (data) {
                    // Finished
                    generalFunctions.ajaxResponse(data);
                    itemStateContainer.innerHTML = '<span class="checkMark"></span>';
                    --FileManager.uploader.fileCount;
                    if (FileManager.uploader.fileCount === 0) {
                        FileManager.update()
                    }
                },
                xhr: function () {
                    // Progress
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (event) {
                        if (event.lengthComputable) {
                            if (event.loaded < item.size) {
                                itemStateContainer.firstElementChild.setAttribute('value', event.loaded)
                            } else {
                                itemStateContainer.innerHTML = '<div class="cssLoader"></div>';
                            }
                        }
                    }, false);
                    return xhr;
                },
            });
        },

        createUploadProgressBar: function (item) {
            let progressBar = document.createElement('progress');
            progressBar.setAttribute('value', '0');
            progressBar.setAttribute('max', item.size);
            let tdProgress = document.createElement('td');
            tdProgress.setAttribute('class', 'progressBarContainer');
            tdProgress.setAttribute('style', 'padding: 0 10px;');
            tdProgress.appendChild(progressBar);
            return tdProgress;
        },

        createUploadProgressRow: function (item, progressBarContainer) {
            if (progressBarContainer === undefined) {
                progressBarContainer = FileManager.uploader.createUploadProgressBar(item);
            }
            let tr = document.createElement('tr');
            let tdName = document.createElement('td');
            tdName.innerText = item.name;
            tr.appendChild(tdName);
            tr.appendChild(progressBarContainer);
            return tr;
        },
    },

    init: function (fileManagerId, path, allowEdit, maxUploadSize) {
        FileManager.$fileManager = jQuery('#' + fileManagerId);
        FileManager.update(path);
        FileManager.allowEdit = allowEdit;
        FileManager.maxUploadSize = maxUploadSize;
    },

    update: function (path) {
        if (path === undefined) {
            path = FileManager.currentPath;
        }
        FileManager.showLoader();
        jQuery.ajax({
            method: 'POST',
            url: FileManager.params.urls.ajax,
            data: {
                action: FileManager.params.actions['listFolder'],
                path: path,
                options: FileManager.options,
            },
            success: function (data) {
                if (generalFunctions.ajaxResponse(data, true)) {
                    FileManager.$fileManager.html(data);
                    let path = document.getElementById('currentFolderTitle').dataset.path;
                    if (!path.endsWith('/')) {
                        path += '/';
                    }
                    window.history.pushState('', '', '?path=' + encodeURIComponent(path));
                    FileManager.currentPath = path;
                    FileManager.loaded();
                }
            },
            complete: function () {
                FileManager.hideLoader();
            }
        });
    },

    loaded: function () {
        let $itemList = FileManager.$fileManager.find('.item-list');

        let fileItems = {};
        let folderItems = {};
        if (FileManager.params.rights['download']) {
            fileItems.download = {name: 'Download', icon: 'fa-download'};
        }
        if (FileManager.params.rights['edit']) {
            fileItems.edit_file = {name: 'Edit', icon: 'fa-edit'};
        }
        if (FileManager.params.rights['delete']) {
            fileItems.delete_file = {name: 'Delete', icon: 'fa-trash'};
            folderItems.delete_folder = {name: 'Delete', icon: 'fa-trash'};
        }
        if (Object.keys(fileItems).length === 0) {
            fileItems.no_actions = {name: 'No Actions'};
        }
        if (Object.keys(folderItems).length === 0) {
            folderItems.no_actions = {name: 'No Actions'};
        }
        let contextMenu = function (key, data) {
            if (key === 'delete_file') {
                FileManager.showLoader();
                let path = data.$trigger.data('path');
                if (path === undefined) {
                    path = data.$trigger.parent().children().first().data('path');
                }
                jQuery.ajax({
                    type: "POST",
                    url: FileManager.params.urls.ajax,
                    data: {
                        action: FileManager.params.actions['deleteFile'],
                        path: path,
                    },
                    success: function () {
                        FileManager.update();
                    }
                });
            } else if (key === 'delete_folder') {
                FileManager.showLoader();
                let path = data.$trigger.data('path');
                if (path === undefined) {
                    path = data.$trigger.parent().children().first().data('path');
                }
                jQuery.ajax({
                    type: "POST",
                    url: FileManager.params.urls.ajax,
                    data: {
                        path: path,
                        action: FileManager.params.actions['deleteFolder'],
                    },
                    success: function (data) {
                        generalFunctions.ajaxResponse(data);
                        FileManager.update($itemList.data('path'));
                    }
                });
            } else if (key === 'download' && FileManager.params.rights['download']) {
                let path = data.$trigger.data('path');
                let filename = data.$trigger.data('fileName');
                if (path === undefined) {
                    path = data.$trigger.parent().children().first().data('path');
                    filename = data.$trigger.parent().children().first().data('fileName');
                }
                let a = jQuery("<a>")
                    .attr('href', FileManager.params.urls.ajax + '?action=' + FileManager.params.actions['downloadFile'] + '&path=' + encodeURIComponent(path))
                    .attr('download', filename)
                    .appendTo('body');
                a[0].click();
                a.remove();
            } else if (key === 'edit_file') {
                let svgHtml = data.$trigger.parent().find('svg')[0].outerHTML;
                let oldPath = data.$trigger.data('path');
                if (oldPath === undefined) {
                    oldPath = data.$trigger.parent().children().first().data('path');
                }
                let row = '' +
                    '<tr id="edit-item">' +
                    '   <td class="item-name">' + svgHtml +
                    '       <form id="editForm">' +
                    '           <input type="hidden" name="action" value="' + FileManager.params.actions['editFile'] + '">' +
                    '           <input type="hidden" name="oldPath" value="' + oldPath + '">' +
                    '           <input type="text" name="newPath" style="height: 35px; width: calc(100% - 90px); float: left; margin: 4px 0;">' +
                    '           <button type="submit" class="inline" style="margin: 4px 0;"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + FileManager.params.urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
                    '       </form>' +
                    '   </td>' +
                    '   <td></td>' +
                    '</tr>';
                data.$trigger.parent().replaceWith(row);
                jQuery('#edit-item-icon').css('margin', '4px 10px');
                let $newNameInput = jQuery("input[name='newPath']");
                $newNameInput.focus();
                $newNameInput.val(oldPath);
                jQuery("#editForm").submit(function (event) {
                    event.preventDefault();
                    jQuery.ajax({
                        type: "POST",
                        url: FileManager.params.urls.ajax,
                        data: jQuery("#editForm").serialize(),
                        success: function (data) {
                            generalFunctions.ajaxResponse(data);
                            FileManager.update();
                        }
                    });
                });
            }
        };

        $itemList.contextMenu({
            selector: '.click-navigate:not(.no-menu)',
            callback: contextMenu,
            items: folderItems,
        });
        $itemList.contextMenu({
            selector: '.click-download:not(.no-menu)',
            callback: contextMenu,
            items: fileItems,
        });
        FileManager.$fileManager.contextMenu({
            selector: '.folder-actions',
            trigger: 'left',
            callback: contextMenu,
            items: folderItems,
        });
        FileManager.$fileManager.contextMenu({
            selector: '.item-actions',
            trigger: 'left',
            callback: contextMenu,
            items: fileItems,
        });

        jQuery('.click-navigate').click(function () {
            FileManager.update(this.dataset.path);
        });
        if (FileManager.params.rights['download']) {
            jQuery('.click-download').click(function () {
                let path = this.dataset.path;
                let a = jQuery("<a>")
                    .attr('href', FileManager.params.urls.ajax + '?action=' + FileManager.params.actions['openFile'] + '&path=' + encodeURIComponent(path))
                    .attr('target', '_blank')
                    .appendTo('body');
                console.log(a[0]);
                a[0].click();
                a.remove();
            });
        }
        jQuery('#addFolder').click(function () {
            let row = '' +
                '<tr id="new-folder">' +
                '   <td class="item-name">' +
                '       <svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + FileManager.params.urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                '       <form id="newFolderForm">' +
                '           <input type="hidden" name="action" value="' + FileManager.params.actions['createFolder'] + '">' +
                '           <input type="hidden" name="path" value="' + FileManager.currentPath + '">' +
                '           <input type="text" name="newFolderName" style="height: 35px; width: calc(100% - 90px); float: left; margin: 0;">' +
                '           <button type="submit" class="inline"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + FileManager.params.urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
                '       </form>' +
                '   </td>' +
                '   <td></td>' +
                '</tr>'
            ;
            let firstItem = $itemList.find('tr')[0];
            if (firstItem === undefined) {
                $itemList.append(jQuery.parseHTML(row)[0]);
            } else {
                firstItem.before(jQuery.parseHTML(row)[0]);
            }
            jQuery("#newFolderForm").submit(function (event) {
                event.preventDefault();
                jQuery.ajax({
                    type: "POST",
                    url: FileManager.params.urls.ajax,
                    data: jQuery("#newFolderForm").serialize(),
                    success: function (data) {
                        generalFunctions.ajaxResponse(data);
                        FileManager.update();
                    }
                });
            });
        });
    },

    showLoader: function () {
        document.getElementById('itemListContainer').classList.add('loading');
        document.getElementById('itemListLoader').style.display = 'block';
    },

    hideLoader: function () {
        document.getElementById('itemListContainer').classList.remove('loading');
        document.getElementById('itemListLoader').style.display = 'none';
    },
};
