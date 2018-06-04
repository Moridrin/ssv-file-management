// noinspection JSUnresolvedVariable
let FileManager = {
    params: mp_ssv_file_manager_params,
    $fileManager: undefined,
    allowEdit: false,
    currentPath: undefined,

    uploader: {

        fileCount: 0,

        FileSelectHandler: function (event) {
            event.preventDefault();
            let items = event.target.files;
            if (items) {
                for (let i = 0, item; item = items[i]; i++) {
                    console.log(item);
                    ++FileManager.uploader.fileCount;
                    let formData = new FormData();
                    formData.append('action', FileManager.params.actions['uploadFile']);
                    formData.append('path', FileManager.currentPath);
                    formData.append('file', item);
                    formData.append('fileName', item.name);
                    jQuery.ajax({
                        method: 'POST',
                        url: FileManager.params.urls.ajax,
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            console.log(JSON.parse(data));
                            --FileManager.uploader.fileCount;
                            if (FileManager.uploader.fileCount === 0) {
                                FileManager.update(FileManager.currentPath)
                            }
                        }
                    });
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
                ++FileManager.uploader.fileCount;
                item.file(function (file) {
                    let formData = new FormData();
                    formData.append('action', FileManager.params.actions['uploadFile']);
                    formData.append('path', path);
                    formData.append('file', file);
                    formData.append('fileName', item.name);
                    jQuery.ajax({
                        method: 'POST',
                        url: FileManager.params.urls.ajax,
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function () {
                            --FileManager.uploader.fileCount;
                            if (FileManager.uploader.fileCount === 0) {
                                FileManager.update(document.getElementById('itemList').dataset['path'])
                            }
                        }
                    });
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
    },

    init: function (fileManagerId, path, allowEdit) {
        FileManager.$fileManager = jQuery('#' + fileManagerId);
        FileManager.update(path);
        FileManager.allowEdit = allowEdit;
    },

    update: function (path) {
        jQuery.ajax({
            method: 'POST',
            url: FileManager.params.urls.ajax,
            data: {
                action: FileManager.params.actions['listFolder'],
                path: path,
                options: FileManager.options,
            },
            success: function (data) {
                FileManager.$fileManager.html(data);
                let path = document.getElementById('currentFolderTitle').dataset.path;
                if (!path.endsWith('/')) {
                    path += '/';
                }
                window.history.pushState('', '', '?path=' + path);
                FileManager.currentPath = path;
                FileManager.loaded();
            }
        });
    },

    loaded: function () {
        let $itemList = FileManager.$fileManager.find('.item-list');

        let fileItems = {};
        let folderItems = {};
        if (FileManager.allowEdit) {
            fileItems = {
                open: {name: 'Open', icon: 'fa-external-link-alt'},
                edit_file: {name: 'Edit', icon: 'fa-edit'},
                delete_file: {name: 'Delete', icon: 'fa-trash'},
            };
            folderItems = {
                delete_folder: {name: 'Delete', icon: 'fa-trash'},
            };
        } else {
            fileItems = {
                open: {name: 'Open', icon: 'fa-external-link-alt'},
            };
        }
        let contextMenu = function (key, data) {
            if (key === 'delete_file') {
                let path = data.$trigger.find('[data-path]').data('path');
                jQuery.ajax({
                    type: "POST",
                    url: FileManager.params.urls.ajax,
                    data: {
                        action: FileManager.params.actions['deleteFile'],
                        path: path,
                    },
                    success: function () {
                        FileManager.update(FileManager.currentPath);
                    }
                });
            } else if (key === 'delete_folder') {
                let path = data.$trigger.data('path');
                jQuery.ajax({
                    type: "POST",
                    url: FileManager.params.urls.ajax,
                    data: {
                        action: FileManager.params.actions['deleteFolder'],
                        path: path,
                    },
                    success: function (data) {
                        console.log(JSON.parse(data));
                        FileManager.update($itemList.data('path'));
                    }
                });
            } else if (key === 'open') {
                let path = jQuery(this).data('path');
                let filename = jQuery(this).data('filename');
                if (filename === undefined) {
                    return;
                }
                let a = jQuery("<a>")
                    .attr("href", FileManager.params.urls.base + path)
                    // .attr("target", "_blank")
                    .attr("download", "test.txt")
                    .attr("open", filename)
                    .appendTo("body");
                a[0].click();
                a.remove();
            } else if (key === 'edit_file') {
                let oldPath = data.$trigger.data('path');
                let row = '' +
                    '<tr id="edit-item">' +
                    '   <td class="item-name">' +
                    '       <svg id="edit-item-icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + FileManager.params.urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                    '       <form id="editForm">' +
                    '           <input type="hidden" name="action" value="' + FileManager.params.actions['editFile'] + '">' +
                    '           <input type="hidden" name="oldPath" value="' + oldPath + '">' +
                    '           <input type="text" name="newPath" style="height: 35px; width: calc(100% - 90px); float: left; margin: 4px 0;">' +
                    '           <button type="submit" class="inline" style="margin: 4px 0;"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + FileManager.params.urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
                    '       </form>' +
                    '   </td>' +
                    '   <td></td>' +
                    '</tr>';
                data.$trigger.replaceWith(row);
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
                            console.log(JSON.parse(data));
                            FileManager.update($itemList.data('path'));
                        }
                    });
                });
            } else {
                let m = 'clicked: ' + key;
                window.console && console.log(m) || alert(m);
            }
        };

        jQuery('ul.context-menu-root').remove();
        if (FileManager.allowEdit) {
            $itemList.contextMenu({
                selector: '.click-navigate',
                callback: contextMenu,
                items: folderItems,
            });
        }
        $itemList.contextMenu({
            selector: 'tr:not(.no-menu):not(.click-navigate)',
            callback: contextMenu,
            items: fileItems,
        });
        if (FileManager.allowEdit) {
            FileManager.$fileManager.contextMenu({
                selector: '.folder-actions',
                trigger: 'left',
                callback: contextMenu,
                items: folderItems,
            });
        }
        FileManager.$fileManager.contextMenu({
            selector: '.item-actions',
            trigger: 'left',
            callback: contextMenu,
            items: fileItems,
        });

        jQuery('.click-navigate').click(function () {
            FileManager.update(this.dataset.path);
        });
        jQuery('.click-open').click(function () {
            let path = this.dataset.path;
            let filename = this.dataset.filename;
            let a = jQuery("<a>")
                .attr('href', FileManager.params.urls.ajax + '?action=' + FileManager.params.actions['downloadFile'] + '&path=' + path)
                .attr('open', filename)
                .appendTo('body');
            a[0].click();
            a.remove();
        });
        jQuery('#addFolder').click(function () {
            let path = jQuery(this).data('path');
            let row = '' +
                '<tr id="new-folder">' +
                '   <td class="item-name">' +
                '       <svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + FileManager.params.urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                '       <form id="newFolderForm">' +
                '           <input type="hidden" name="action" value="' + FileManager.params.actions['createFolder'] + '">' +
                '           <input type="hidden" name="path" value="' + path + '">' +
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
                        console.log(JSON.parse(data));
                        FileManager.update(path);
                    }
                });
            });
        });
        // jQuery('#uploadForm').submit(function (event) {
        //     event.preventDefault();
        //     let path = jQuery(this).data('path');
        //     let formData = new FormData();
        //     formData.append('action', FileManager.params.actions['uploadFile']);
        //     formData.append('path', jQuery(this).data('path'));
        //     formData.append('file', jQuery('#uploadFile').prop('files')[0]);
        //     jQuery.ajax({
        //         type: "POST",
        //         url: FileManager.params.urls.ajax,
        //         contentType: false,
        //         processData: false,
        //         data: formData,
        //         success: function (data) {
        //             console.log(JSON.parse(data));
        //             FileManager.update(path);
        //         }
        //     });
        // });
        // jQuery('#uploadPath').val(this.$FileManager.children('.item-list').data('path'));
    },
};
