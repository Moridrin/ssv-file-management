let $fileManager;
let $options;
function updateFileManager(path) {
    jQuery(function ($) {
        $.ajax({
            method: 'POST',
            url: urls.admin,
            data: {
                action: 'mp_ssv_ajax_file_manager',
                path: path,
                options: $options,
            },
            success: function (data) {
                $fileManager.html(data);
                fileManagerLoaded($fileManager);
            }
        });
    });
}

function fileManagerInit(fileManagerId, path, options) {
    $options = options;
    if ($options === undefined) {
        $options = {};
    }
    if ($options['showFolderUp'] === undefined) {
        $options['showFolderUp'] = true;
    }
    if ($options['showFolders'] === undefined) {
        $options['showFolders'] = true;
    }
    if ($options['showFiles'] === undefined) {
        $options['showFiles'] = true;
    }
    if ($options['allowCreateFolder'] === undefined) {
        $options['allowCreateFolder'] = true;
    }
    if ($options['allowDownload'] === undefined) {
        $options['allowDownload'] = true;
    }
    if ($options['allowRename'] === undefined) {
        $options['allowRename'] = true;
    }
    if ($options['allowDelete'] === undefined) {
        $options['allowDelete'] = true;
    }
    if ($options['selectableFolders'] === undefined) {
        $options['selectableFolders'] = true;
    }
    if ($options['selectableFiles'] === undefined) {
        $options['selectableFiles'] = true;
    }
    if ($options['multiSelect'] === undefined) {
        $options['multiSelect'] = true;
    }
    jQuery(function ($) {
        $fileManager = $('#' + fileManagerId);
        updateFileManager(path);
    });
}

function fileManagerLoaded() {
    jQuery(function ($) {
        let $itemList = $fileManager.find('.item-list');

        let items = {};
        if ($options['allowDownload']) {
            items['download'] = {name: 'Download', icon: 'download'};
        }
        if ($options['allowRename']) {
            items['rename'] = {name: 'Rename', icon: 'edit'};
        }
        if ($options['allowDelete']) {
            items['delete'] = {name: 'Delete', icon: 'delete'};
        }

        let contextMenu = {
            callback: function (key, data) {
                if (key === 'delete') {
                    let path = data.$trigger.data('location');
                    let item = data.$trigger.data('item');
                    $.ajax({
                        type: "POST",
                        url: urls.admin,
                        data: {
                            'action': 'mp_ssv_delete_item',
                            'path': path,
                            'item': item,
                        },
                        success: function (data) {
                            updateFileManager($itemList.data('path'));
                        }
                    });
                } else if (key === 'download') {
                    let path = data.$trigger.data('location');
                    let item = data.$trigger.data('item');
                    path = path.replace(urls.basePath, '');
                    let a = $("<a>")
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
                        '<input type="hidden" name="action" value="mp_ssv_rename_item">' +
                        '<input type="hidden" name="path" value="' + data.$trigger.data('location') + '">' +
                        '<input type="hidden" name="oldItemName" value="' + oldName + '">' +
                        '<input type="text" name="newItemName" style="height: 35px; width: calc(100% - 90px); float: left; margin: 4px 0;">' +
                        '<button type="submit" class="inline" style="margin: 4px 0;"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
                        '</form>' +
                        '</td>' +
                        '<td></td>' +
                        '</tr>';
                    data.$trigger.replaceWith(row);
                    $('#rename-item-icon').css('margin', '4px 10px');
                    let $newNameInput = $("input[name='newItemName']");
                    $newNameInput.focus();
                    $newNameInput.val(oldName);
                    $("#renameForm").submit(function (event) {
                        event.preventDefault();
                        $.ajax({
                            type: "POST",
                            url: urls.admin,
                            data: $("#renameForm").serialize(),
                            success: function (data) {
                                updateFileManager($itemList.data('path'));
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
        $('ul.context-menu-root').remove();
        $itemList.contextMenu({
            selector: 'tr.selectable',
            callback: contextMenu.callback,
            items: contextMenu.items,
        });
        $fileManager.contextMenu({
            selector: '.item-actions',
            trigger: 'left',
            callback: contextMenu.callback,
            items: contextMenu.items,
        });
        if ($options['selectableFiles']) {
            $fileManager.find('tr.selectable.file').click(function (event) {
                if (!event.ctrlKey || !$options['multiSelect']) {
                    $fileManager.find('tr.selectable.file').removeClass('selected');
                    $(this).addClass('selected');
                } else {
                    $(this).toggleClass('selected');
                }
            });
        }

        if ($options['selectableFolders']) {
            $fileManager.find('tr.selectable.folder').click(function (event) {
                if (!event.ctrlKey || !$options['multiSelect']) {
                    $fileManager.find('tr.selectable.folder').removeClass('selected');
                    $(this).addClass('selected');
                } else {
                    $(this).toggleClass('selected');
                }
            });
        }
        $('.dbclick-navigate').dblclick(function () {
            let path = $(this).data('location');
            let item = $(this).data('item');
            if (item) {
                path += '/' + $(this).data('item')
            }
            $.ajax({
                method: 'POST',
                url: urls.admin,
                data: {
                    action: 'mp_ssv_ajax_file_manager',
                    path: path,
                    options: $options
                },
                success: function (data) {
                    $fileManager.html(data);
                    fileManagerLoaded($fileManager);
                }
            });
        });
        $('.dbclick-download').dblclick(function () {
            let path = $(this).data('location');
            let item = $(this).data('item');
            path = path.replace(urls.basePath, '');
            let a = $("<a>")
                .attr("href", urls.base + '/' + path + '/' + item)
                .attr("download", item)
                .appendTo("body");
            a[0].click();
            a.remove();
        });
        $fileManager.find('tr td span[data-location]').click(function (event) {
            if (!event.ctrlKey && $(this).has('form').length === 0) {
                let path = $(this).data('location');
                let item = $(this).data('item');
                if (item) {
                    path += '/' + $(this).data('item')
                }
                $.ajax({
                    method: 'POST',
                    url: urls.admin,
                    data: {
                        action: 'mp_ssv_ajax_file_manager',
                        path: path,
                        options: $options
                    },
                    success: function (data) {
                        $fileManager.html(data);
                        fileManagerLoaded($fileManager);
                    }
                });
            }
        });
        $('#addFolder').click(function () {
            let path = $(this).data('path');
            let row = '<tr id="new-folder">' +
                '<td class="item-name">' +
                '<svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                '<form id="newFolderForm">' +
                '<input type="hidden" name="action" value="mp_ssv_create_folder">' +
                '<input type="hidden" name="path" value="' + path + '">' +
                '<input type="text" name="newFolderName" style="height: 35px; width: calc(100% - 90px); float: left;">' +
                '<button type="submit" class="inline"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
                '</form>' +
                '</td>' +
                '<td></td>' +
                '</tr>';
            $itemList.find('tr')[0].before($.parseHTML(row)[0]);
            $("#newFolderForm").submit(function (event) {
                event.preventDefault();
                $.ajax({
                    type: "POST",
                    url: urls.admin,
                    data: $("#newFolderForm").serialize(),
                    success: function (data) {
                        updateFileManager(path);
                    }
                });
            });
        });
        $('#uploadPath').val($fileManager.children('.item-list').data('path'));
    });
}
Dropzone.options.uploadFile = {
    init: function() {
        this.on("success", function(file) {
            let path = $fileManager.children('.item-list').data('path');
            updateFileManager(path);
            this.removeAllFiles();
        });
    }
};
