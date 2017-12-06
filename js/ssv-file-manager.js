let $fileManager;
function updateFileManager(path) {
    jQuery(function ($) {
        $.ajax({
            method: 'POST',
            url: urls.admin,
            data: {
                'action': 'mp_ssv_ajax_file_manager',
                'path': path,
            },
            success: function (data) {
                $fileManager.html(data);
                fileManagerLoaded($fileManager);
            }
        });
    });
}

function fileManagerInit(fileManagerId, path) {
    jQuery(function ($) {
        $fileManager = $('#' + fileManagerId);
        updateFileManager(path);
    });
}

function fileManagerLoaded() {
    jQuery(function ($) {
        let $itemList = $fileManager.find('.item-list');

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
            items: {
                'download': {name: 'Download', icon: 'download'},
                'rename': {name: 'Rename', icon: 'edit'},
                'delete': {name: 'Delete', icon: 'delete'}
            }
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
                    'action': 'mp_ssv_ajax_file_manager',
                    'path': path,
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
        $fileManager.find('tr td span[data-location]').click(function () {
            if ($(this).has('form').length === 0) {
                let path = $(this).data('location');
                let item = $(this).data('item');
                if (item) {
                    path += '/' + $(this).data('item')
                }
                $.ajax({
                    method: 'POST',
                    url: urls.admin,
                    data: {
                        'action': 'mp_ssv_ajax_file_manager',
                        'path': path,
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
