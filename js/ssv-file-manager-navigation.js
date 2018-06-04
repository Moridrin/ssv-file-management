// noinspection JSUnresolvedVariable
let FileManagerNavigation = {
    params: mp_ssv_file_manager_navigation_params,
    $fileManager: undefined,

    init: function (fileManagerId, path, allowEdit) {
        FileManagerNavigation.$fileManager = document.getElementById(fileManagerId);
        FileManagerNavigation.update(path);
        FileManagerNavigation.allowEdit = allowEdit;
    },

    update: function (path) {
        jQuery.ajax({
            method: 'POST',
            url: FileManagerNavigation.params.urls.ajax,
            data: {
                action: FileManagerNavigation.params.actions['listFolder'],
                path: path,
                options: FileManagerNavigation.options,
            },
            success: function (data) {
                FileManagerNavigation.$fileManager.innerHTML = data;
                FileManagerNavigation.loaded();
                window.history.pushState('', '', '?path='+path);
            }
        });
    },

    loaded: function () {
        let $itemList = FileManagerNavigation.$fileManager.find('.item-list');

        if (FileManagerNavigation.allowEdit) {
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
        if (FileManagerNavigation.allowEdit) {
            FileManagerNavigation.$fileManager.contextMenu({
                selector: '.folder-actions',
                trigger: 'left',
                callback: contextMenu,
                items: folderItems,
            });
        }
        FileManagerNavigation.$fileManager.contextMenu({
            selector: '.item-actions',
            trigger: 'left',
            callback: contextMenu,
            items: fileItems,
        });

        jQuery('.click-navigate').click(function () {
            let path = jQuery(this).data('path');
            FileManagerNavigation.update(path);
        });
        jQuery('.click-open').click(function () {
            let path = jQuery(this).data('path');
            let filename = jQuery(this).data('filename');
            let a = jQuery("<a>")
                .attr('href', FileManagerNavigation.params.urls.ajax + '?action=' + FileManagerNavigation.params.actions['downloadFile'] + '&path=' + path)
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
                '       <svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + FileManagerNavigation.params.urls.plugins + '/ssv-file-manager/images/folder.svg#folder"></use></svg>' +
                '       <form id="newFolderForm">' +
                '           <input type="hidden" name="action" value="' + FileManagerNavigation.params.actions['createFolder'] + '">' +
                '           <input type="hidden" name="path" value="' + path + '">' +
                '           <input type="text" name="newFolderName" style="height: 35px; width: calc(100% - 90px); float: left; margin: 0;">' +
                '           <button type="submit" class="inline"><svg style="margin: 0; height: 15px; width: 15px;"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="' + FileManagerNavigation.params.urls.plugins + '/ssv-file-manager/images/sprite_icons.svg#apply"></use></svg></button>' +
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
                    url: FileManagerNavigation.params.urls.ajax,
                    data: jQuery("#newFolderForm").serialize(),
                    success: function (data) {
                        console.log(JSON.parse(data));
                        FileManagerNavigation.update(path);
                    }
                });
            });
        });
        jQuery('#uploadForm').submit(function (event) {
            event.preventDefault();
            let path = jQuery(this).data('path');
            let formData = new FormData();
            formData.append('action', FileManagerNavigation.params.actions['uploadFile']);
            formData.append('path', jQuery(this).data('path'));
            formData.append('file', jQuery('#uploadFile').prop('files')[0]);
            jQuery.ajax({
                type: "POST",
                url: FileManagerNavigation.params.urls.ajax,
                contentType: false,
                processData: false,
                data: formData,
                success: function (data) {
                    console.log(JSON.parse(data));
                    FileManagerNavigation.update(path);
                }
            });
        });
        // jQuery('#uploadPath').val(this.$fileManager.children('.item-list').data('path'));
    },
};
