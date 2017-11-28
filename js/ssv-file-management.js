jQuery(function ($) {
    let $itemList = $('.item-list');

    let contextMenu = {
        callback: function(key, options) {
            var m = 'clicked: ' + key;
            window.console && console.log(m) || alert(m);
        },
        items: {
            'rename': {name: 'Rename', icon: 'edit'},
            'delete': {name: 'Delete', icon: 'delete'},
            'sep1': '---------',
            'quit': {name: 'Quit', icon: function($element, key, item){ return 'context-menu-icon context-menu-icon-quit'; }}
        }
    };
    $itemList.contextMenu({
        selector: 'tr',
        callback: contextMenu.callback,
        items: contextMenu.items,
    });
    $.contextMenu({
        selector: '.item-actions',
        trigger: 'left',
        callback: contextMenu.callback,
        items: contextMenu.items,
    });
    $('[data-folder-location]').dblclick(function () {
        window.location.href = $(this).data('folder-location');
    });
    $('.selectable').click(function () {
        let isSelected = $(this).hasClass('selected');
        $('.selectable.selected').removeClass('selected');
        if (!isSelected) {
            $(this).addClass('selected');
        }
    });
    $('#addFolder').click(function () {
        let row = '<tr id="new-folder">' +
            '<td class="item-name">' +
            '<svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + urls.plugins + '/ssv-file-management/images/folder.svg#folder"></use></svg>' +
            '<form id="newFolderForm">' +
            '<input type="hidden" name="action" value="mp_ssv_create_folder">' +
            '<input type="hidden" name="path" value="'+$(this).data('path')+'">' +
            '<input type="text" name="newFolderName" style="height: 35px; width: calc(100% - 90px); float: left;">' +
            '<button type="submit" class="inline"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + urls.plugins + '/ssv-file-management/images/sprite_icons.svg#apply"></use></svg></button>' +
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
                success: function(data) {
                    location.reload();
                }
            });
        });
    });
});
