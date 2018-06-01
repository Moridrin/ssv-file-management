let fileManager = {
    params: mp_ssv_file_manager_params,
    $fileManager: undefined,
    allowEdit: false,

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
                action: 'mp_ssv_ajax_list_folder',
                path: path,
                options: fileManager.options,
            },
            success: function (data) {
                fileManager.$fileManager.html(data);
                fileManager.loaded();
            }
        });
    },

    loaded: function () {
        let $itemList = this.$fileManager.find('.item-list');

        let fileItems = {};
        let folderItems = {};
        if (this.allowEdit) {
            fileItems = {
                download: {name: 'Download', icon: 'download'},
                rename: {name: 'Rename', icon: 'edit'},
                delete: {name: 'Delete', icon: 'delete'},
            };
            folderItems = {
                rename: {name: 'Rename', icon: 'edit'},
                delete: {name: 'Delete', icon: 'delete'},
            };
        } else {
            fileItems = {
                download: {name: 'Download', icon: 'download'},
            };
        }
        let contextMenu = function (key, data) {
            if (key === 'delete') {
                let path = data.$trigger.data('path');
                let item = data.$trigger.data('item');
                jQuery.ajax({
                    type: "POST",
                    url: fileManager.params.urls.ajax,
                    data: {
                        'action': 'mp_ssv_file_manager_delete_item',
                        'path': path,
                        'item': item,
                    },
                    success: function (data) {
                        fileManager.update($itemList.data('path'));
                    }
                });
            } else if (key === 'download') {
                let path = jQuery(this).data('path');
                let filename = jQuery(this).data('filename');
                if (filename === undefined) {
                    return;
                }
                path = path.replace(fileManager.params.urls.basePath, '');
                let a = jQuery("<a>")
                    .attr("href", fileManager.params.urls.base + path)
                    .attr("target", "_blank")
                    .attr("download", filename)
                    .appendTo("body");
                a[0].click();
                a.remove();
            } else if (key === 'rename') {
                let oldName = data.$trigger.find('span span').text();
                let row = '<tr id="rename-item">' +
                    '<td class="item-name">' +
                    '<svg id="rename-item-icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + fileManager.params.urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                    '<form id="renameForm">' +
                    '<input type="hidden" name="action" value="mp_ssv_file_manager_rename_item">' +
                    '<input type="hidden" name="path" value="' + data.$trigger.data('path') + '">' +
                    '<input type="hidden" name="oldItemName" value="' + oldName + '">' +
                    '<input type="text" name="newItemName" style="height: 35px; width: calc(100% - 90px); float: left; margin: 4px 0;">' +
                    '<button type="submit" class="inline" style="margin: 4px 0;"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + fileManager.params.urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
                    '</form>' +
                    '</td>' +
                    '<td></td>' +
                    '</tr>';
                data.$trigger.replaceWith(row);
                jQuery('#rename-item-icon').css('margin', '4px 10px');
                let $newNameInput = jQuery("input[name='newItemName']");
                $newNameInput.focus();
                $newNameInput.val(oldName);
                jQuery("#renameForm").submit(function (event) {
                    event.preventDefault();
                    jQuery.ajax({
                        type: "POST",
                        url: fileManager.params.urls.ajax,
                        data: jQuery("#renameForm").serialize(),
                        success: function (data) {
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
        $itemList.contextMenu({
            selector: 'tr:not(.no-menu).dbclick-navigate',
            callback: contextMenu,
            items: folderItems,
        });
        $itemList.contextMenu({
            selector: 'tr:not(.no-menu):not(.dbclick-navigate)',
            callback: contextMenu,
            items: fileItems,
        });
        if (this.allowEdit) {
            this.$fileManager.contextMenu({
                selector: '.folder-actions',
                trigger: 'left',
                callback: contextMenu,
                items: folderItems,
            });
        }
        this.$fileManager.contextMenu({
            selector: '.item-actions',
            trigger: 'left',
            callback: contextMenu,
            items: fileItems,
        });

        jQuery('.dbclick-navigate').dblclick(function () {
            let path = jQuery(this).data('path');
            fileManager.update(path);
        });
        jQuery('.dbclick-download').dblclick(function () {
            let path = jQuery(this).data('path');
            let filename = jQuery(this).data('filename');
            path = path.replace(fileManager.params.urls.basePath, '');
            let a = jQuery("<a>")
                .attr("href", fileManager.params.urls.base + path)
                .attr("target", "_blank")
                .attr("download", filename)
                .appendTo("body");
            a[0].click();
            a.remove();
        });
        this.$fileManager.find('tr td span[data-path]').click(function (event) {
            if (jQuery(this).has('form').length === 0) {
                let path = jQuery(this).data('path');
                fileManager.update(path);
            }
        });
        jQuery('#addFolder').click(function () {
            let path = jQuery(this).data('path');
            let row = '<tr id="new-folder">' +
                '<td class="item-name">' +
                '<svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + fileManager.params.urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                '<form id="newFolderForm">' +
                '<input type="hidden" name="action" value="mp_ssv_file_manager_create_folder">' +
                '<input type="hidden" name="path" value="' + path + '">' +
                '<input type="text" name="newFolderName" style="height: 35px; width: calc(100% - 90px); float: left; margin: 0;">' +
                '<button type="submit" class="inline"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + fileManager.params.urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
                '</form>' +
                '</td>' +
                '<td></td>' +
                '</tr>';
            $itemList.find('tr')[0].before(jQuery.parseHTML(row)[0]);
            jQuery("#newFolderForm").submit(function (event) {
                event.preventDefault();
                jQuery.ajax({
                    type: "POST",
                    url: fileManager.params.urls.ajax,
                    data: jQuery("#newFolderForm").serialize(),
                    success: function (data) {
                        fileManager.update(path);
                    }
                });
            });
        });
        jQuery('#uploadPath').val(this.$fileManager.children('.item-list').data('path'));
    }
};
