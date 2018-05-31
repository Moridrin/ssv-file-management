let ssvFileManager = {
    $fileManager: undefined,
    options: {
        'showFolderUp': true,
        'showFolders': true,
        'showFiles': true,
        'allowCreateFolder': true,
        'allowDownload': true,
        'allowRename': true,
        'allowDelete': true,
        'selectableFolders': true,
        'selectableFiles': true,
        'multiSelect': true,
    },

    init: function (fileManagerId, path, options) {
        if (updateCallback instanceof Function) {
            this.updateCallback = updateCallback;
        }
        if (options === undefined) {
            options = {};
        }
        jQuery.extend(this.options, options);
        this.$fileManager = jQuery('#' + fileManagerId);
        ssvFileManager.update(path);
    },

    update: function (path) {
        jQuery.ajax({
            method: 'POST',
            url: urls.ajax,
            data: {
                action: 'mp_ssv_ajax_file_manager',
                path: path,
                options: ssvFileManager.options,
            },
            success: function (data) {
                ssvFileManager.$fileManager.html(data);
                ssvFileManager.loaded(this.$fileManager);
                ssvFileManager.updateCallback(path);
            }
        });
    },

    loaded: function () {
        let $itemList = this.$fileManager.find('.item-list');

        let items = {};
        if (this.options['allowDownload']) {
            items['download'] = {name: 'Download', icon: 'download'};
        }
        if (this.options['allowRename']) {
            items['rename'] = {name: 'Rename', icon: 'edit'};
        }
        if (this.options['allowDelete']) {
            items['delete'] = {name: 'Delete', icon: 'delete'};
        }

        let contextMenu = {
            callback: function (key, data) {
                if (key === 'delete') {
                    let path = data.$trigger.data('location');
                    let item = data.$trigger.data('item');
                    jQuery.ajax({
                        type: "POST",
                        url: urls.ajax,
                        data: {
                            'action': 'mp_ssv_file_manager_delete_item',
                            'path': path,
                            'item': item,
                        },
                        success: function (data) {
                            ssvFileManager.update($itemList.data('path'));
                        }
                    });
                } else if (key === 'download') {
                    let path = data.$trigger.data('location');
                    let item = data.$trigger.data('item');
                    path = path.replace(urls.basePath, '');
                    let a = jQuery("<a>")
                        .attr("href", urls.base + '/' + path + '/' + item)
                        .attr("download", item)
                        .appendTo("body");
                    a[0].click();
                    a.remove();
                } else if (key === 'rename') {
                    let oldName = data.$trigger.find('span span').text();
                    let row = '<tr id="rename-item">' +
                        '<td class="item-name">' +
                        '<svg id="rename-item-icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                        '<form id="renameForm">' +
                        '<input type="hidden" name="action" value="mp_ssv_file_manager_rename_item">' +
                        '<input type="hidden" name="path" value="' + data.$trigger.data('location') + '">' +
                        '<input type="hidden" name="oldItemName" value="' + oldName + '">' +
                        '<input type="text" name="newItemName" style="height: 35px; width: calc(100% - 90px); float: left; margin: 4px 0;">' +
                        '<button type="submit" class="inline" style="margin: 4px 0;"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
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
                            url: urls.ajax,
                            data: jQuery("#renameForm").serialize(),
                            success: function (data) {
                                ssvFileManager.update($itemList.data('path'));
                            }
                        });
                    });
                } else {
                    let m = 'clicked: ' + key;
                    window.console && console.log(m) || alert(m);
                }
            },
            items: items
        };
        jQuery('ul.context-menu-root').remove();
        $itemList.contextMenu({
            selector: 'tr:not(.no-menu)',
            callback: contextMenu.callback,
            items: contextMenu.items,
        });
        this.$fileManager.contextMenu({
            selector: '.item-actions',
            trigger: 'left',
            callback: contextMenu.callback,
            items: contextMenu.items,
        });
        if (this.options['selectableFiles']) {
            this.$fileManager.find('tr.selectable.file').click(function (event) {
                if (!event.ctrlKey || !this.options['multiSelect']) {
                    this.$fileManager.find('tr.selectable.file').removeClass('selected');
                    jQuery(this).addClass('selected');
                } else {
                    jQuery(this).toggleClass('selected');
                }
            });
        }

        if (this.options['selectableFolders']) {
            this.$fileManager.find('tr.selectable.folder').click(function (event) {
                if (!event.ctrlKey || !this.options['multiSelect']) {
                    this.$fileManager.find('tr.selectable.folder').removeClass('selected');
                    jQuery(this).addClass('selected');
                } else {
                    jQuery(this).toggleClass('selected');
                }
            });
        }
        jQuery('.dbclick-navigate').dblclick(function () {
            let path = jQuery(this).data('location');
            let item = jQuery(this).data('item');
            if (item) {
                path += '/' + jQuery(this).data('item')
            }
            ssvFileManager.update(path);
        });
        jQuery('.dbclick-download').dblclick(function () {
            let path = jQuery(this).data('location');
            let item = jQuery(this).data('item');
            path = path.replace(urls.basePath, '');
            let a = jQuery("<a>")
                .attr("href", urls.base + '/' + path + '/' + item)
                .attr("download", item)
                .appendTo("body");
            a[0].click();
            a.remove();
        });
        this.$fileManager.find('tr td span[data-location]').click(function (event) {
            if (!event.ctrlKey && jQuery(this).has('form').length === 0) {
                let path = jQuery(this).data('location');
                let item = jQuery(this).data('item');
                if (item) {
                    path += '/' + jQuery(this).data('item')
                }
                ssvFileManager.update(path);
            }
        });
        jQuery('#addFolder').click(function () {
            let path = jQuery(this).data('path');
            let row = '<tr id="new-folder">' +
                '<td class="item-name">' +
                '<svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                '<form id="newFolderForm">' +
                '<input type="hidden" name="action" value="mp_ssv_file_manager_create_folder">' +
                '<input type="hidden" name="path" value="' + path + '">' +
                '<input type="text" name="newFolderName" style="height: 35px; width: calc(100% - 90px); float: left; margin: 0;">' +
                '<button type="submit" class="inline"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
                '</form>' +
                '</td>' +
                '<td></td>' +
                '</tr>';
            $itemList.find('tr')[0].before(jQuery.parseHTML(row)[0]);
            jQuery("#newFolderForm").submit(function (event) {
                event.preventDefault();
                jQuery.ajax({
                    type: "POST",
                    url: urls.ajax,
                    data: jQuery("#newFolderForm").serialize(),
                    success: function (data) {
                        ssvFileManager.update(path);
                    }
                });
            });
        });
        jQuery('#uploadPath').val(this.$fileManager.children('.item-list').data('path'));
    }
};
