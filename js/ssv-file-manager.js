// noinspection JSUnresolvedVariable
let fileManager = {
    params: mp_ssv_file_manager_params,
    $fileManager: undefined,
    allowEdit: false,

    uploader: {

        fileCount: 0,

        traverseFileTree: function (item, path) {
            path = path || '';
            if (item.isFile) {
                ++fileManager.uploader.fileCount;
                item.file(function (file) {
                    let formData = new FormData();
                    formData.append('action', fileManager.params.actions['uploadFile']);
                    formData.append('path', path);
                    formData.append('file', file);
                    formData.append('fileName', item.name);
                    jQuery.ajax({
                        method: 'POST',
                        url: fileManager.params.urls.ajax,
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function () {
                            --fileManager.uploader.fileCount;
                            if (fileManager.uploader.fileCount === 0) {
                                fileManager.update(document.getElementById('itemList').dataset['path'])
                            }
                        }
                    });
                });
            } else if (item.isDirectory) {
                let formData = new FormData();
                formData.append('action', fileManager.params.actions['createFolder']);
                formData.append('path', path);
                formData.append('newFolderName', item.name);
                jQuery.ajax({
                    method: 'POST',
                    url: fileManager.params.urls.ajax,
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function () {
                        let dirReader = item.createReader();
                        dirReader.readEntries(function (entries) {
                            for (let i = 0; i < entries.length; i++) {
                                fileManager.uploader.traverseFileTree(entries[i], path + item.name + "/");
                            }
                        });
                    }
                });
            }
        },

        FileSelectHandler: function (event) {
            event.preventDefault();
            let path = document.getElementById('currentFolderTitle').dataset.path;
            let items = event.target.files;
            if (items) {
                for (let i = 0, item; item = items[i]; i++) {
                    ++fileManager.uploader.fileCount;
                    let formData = new FormData();
                    formData.append('fileName', item.name);
                    formData.append('path', path);
                    formData.append('file', item);
                    jQuery.ajax({
                        method: 'POST',
                        url: fileManager.params.urls.ajax + '?action=' + encodeURIComponent(fileManager.params.actions.uploadFile),
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            console.log(JSON.parse(data));
                            --fileManager.uploader.fileCount;
                            if (fileManager.uploader.fileCount === 0) {
                                fileManager.update(document.getElementById('itemList').dataset['path'])
                            }
                        }
                    });
                }
            } else {
                items = event.dataTransfer.items;
                for (let i = 0, item; item = items[i]; i++) {
                    fileManager.uploader.traverseFileTree(item.webkitGetAsEntry(), path);
                }
            }
        },
    },

    init: function (fileManagerId, path, allowEdit) {
        fileManager.$fileManager = jQuery('#' + fileManagerId);
        fileManager.update(path);
        fileManager.allowEdit = allowEdit;
    },

    update: function (path) {
        jQuery.ajax({
            method: 'POST',
            url: fileManager.params.urls.ajax,
            data: {
                action: fileManager.params.actions['listFolder'],
                path: path,
                options: fileManager.options,
            },
            success: function (data) {
                fileManager.$fileManager.html(data);
                fileManager.loaded();
                window.history.pushState('', '', '?path='+path);
            }
        });
    },

    loaded: function () {
        let $itemList = fileManager.$fileManager.find('.item-list');

        let fileItems = {};
        let folderItems = {};
        if (fileManager.allowEdit) {
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
                let path = data.$trigger.data('path');
                jQuery.ajax({
                    type: "POST",
                    url: fileManager.params.urls.ajax,
                    data: {
                        action: fileManager.params.actions['deleteFile'],
                        path: path,
                    },
                    success: function () {
                        fileManager.update($itemList.data('path'));
                    }
                });
            } else if (key === 'delete_folder') {
                let path = data.$trigger.data('path');
                jQuery.ajax({
                    type: "POST",
                    url: fileManager.params.urls.ajax,
                    data: {
                        action: fileManager.params.actions['deleteFolder'],
                        path: path,
                    },
                    success: function (data) {
                        console.log(JSON.parse(data));
                        fileManager.update($itemList.data('path'));
                    }
                });
            } else if (key === 'open') {
                let path = jQuery(this).data('path');
                let filename = jQuery(this).data('filename');
                if (filename === undefined) {
                    return;
                }
                let a = jQuery("<a>")
                    .attr("href", fileManager.params.urls.base + path)
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
                    '       <svg id="edit-item-icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + fileManager.params.urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                    '       <form id="editForm">' +
                    '           <input type="hidden" name="action" value="' + fileManager.params.actions['editFile'] + '">' +
                    '           <input type="hidden" name="oldPath" value="' + oldPath + '">' +
                    '           <input type="text" name="newPath" style="height: 35px; width: calc(100% - 90px); float: left; margin: 4px 0;">' +
                    '           <button type="submit" class="inline" style="margin: 4px 0;"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + fileManager.params.urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
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
                        url: fileManager.params.urls.ajax,
                        data: jQuery("#editForm").serialize(),
                        success: function (data) {
                            console.log(JSON.parse(data));
                            fileManager.update($itemList.data('path'));
                        }
                    });
                });
            } else {
                let m = 'clicked: ' + key;
                window.console && console.log(m) || alert(m);
            }
        };

        jQuery('ul.context-menu-root').remove();
        if (fileManager.allowEdit) {
            $itemList.contextMenu({
                selector: 'tr:not(.no-menu).click-navigate',
                callback: contextMenu,
                items: folderItems,
            });
        }
        $itemList.contextMenu({
            selector: 'tr:not(.no-menu):not(.click-navigate)',
            callback: contextMenu,
            items: fileItems,
        });
        if (fileManager.allowEdit) {
            fileManager.$fileManager.contextMenu({
                selector: '.folder-actions',
                trigger: 'left',
                callback: contextMenu,
                items: folderItems,
            });
        }
        fileManager.$fileManager.contextMenu({
            selector: '.item-actions',
            trigger: 'left',
            callback: contextMenu,
            items: fileItems,
        });

        jQuery('.click-navigate').click(function () {
            let path = jQuery(this).data('path');
            fileManager.update(path);
        });
        jQuery('.click-open').click(function () {
            let path = jQuery(this).data('path');
            let filename = jQuery(this).data('filename');
            let a = jQuery("<a>")
                .attr('href', fileManager.params.urls.ajax + '?action=' + fileManager.params.actions['downloadFile'] + '&path=' + path)
                .attr('target', '_blank')
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
                '       <svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + fileManager.params.urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                '       <form id="newFolderForm">' +
                '           <input type="hidden" name="action" value="' + fileManager.params.actions['createFolder'] + '">' +
                '           <input type="hidden" name="path" value="' + path + '">' +
                '           <input type="text" name="newFolderName" style="height: 35px; width: calc(100% - 90px); float: left; margin: 0;">' +
                '           <button type="submit" class="inline"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + fileManager.params.urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
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
                    url: fileManager.params.urls.ajax,
                    data: jQuery("#newFolderForm").serialize(),
                    success: function (data) {
                        console.log(JSON.parse(data));
                        fileManager.update(path);
                    }
                });
            });
        });
        jQuery('#uploadForm').submit(function (event) {
            event.preventDefault();
            let path = jQuery(this).data('path');
            let formData = new FormData();
            formData.append('action', fileManager.params.actions['uploadFile']);
            formData.append('path', jQuery(this).data('path'));
            formData.append('file', jQuery('#uploadFile').prop('files')[0]);
            jQuery.ajax({
                type: "POST",
                url: fileManager.params.urls.ajax,
                contentType: false,
                processData: false,
                data: formData,
                success: function (data) {
                    console.log(JSON.parse(data));
                    fileManager.update(path);
                }
            });
        });
        // jQuery('#uploadPath').val(this.$fileManager.children('.item-list').data('path'));
    },
};
