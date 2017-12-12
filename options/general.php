<?php
namespace mp_ssv_file_manager\options;

use mp_ssv_file_manager\SSV_FileManager;
use mp_ssv_general\SSV_General;

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
if (SSV_General::isValidPOST(SSV_General::OPTIONS_ADMIN_REFERER)) {
    if (isset($_POST['reset'])) {
        SSV_FileManager::resetOptions();
    } else {
        $roles = isset($_POST['roles']) ? $_POST['roles'] : [];
        $wpdb->delete(SSV_FileManager::TABLE_FOLDER_RIGHTS, ['path' => $_POST['path']]);
        $wpdb->replace(
            SSV_FileManager::TABLE_FOLDER_RIGHTS,
            [
                'path' => realpath($_POST['path']),
                'roles' => json_encode($roles),
            ]
        );
    }
}
//SSV_FileManager::CLEAN_INSTALL();
$roles = array_keys(get_editable_roles());
?>
<table class="form-table">
    <tr>
        <td>
            <div id="fileManager"></div>
            <script>
                fileManagerInit(
                    'fileManager',
                    '<?= SSV_FILE_MANAGER_ROOT_FOLDER ?>',
                    {
                        showFiles: false,
                        showFolders: true,
                        allowCreateFolder: true,
                        allowDownload: false,
                        allowRename: true,
                        allowDelete: true,
                        selectableFolders: false,
                        selectableFiles: false,
                        multiSelect: false,
                    },
                    function (path) {
                        // alert(path);
                        jQuery(function ($) {
                            $('#path').val(path);
                            let $roles = $('#roles');
                            $.ajax({
                                method: 'POST',
                                url: urls.admin,
                                data: {
                                    action: 'mp_ssv_file_manager_get_shared_with',
                                    path: path,
                                },
                                success: function (data) {
                                    // $('#testData').html(data);
                                    $roles.html(data);
                                    $roles.removeAttr('disabled');
                                }
                            });
                        });
                    }
                );
            </script>
        </td>
        <td>
            <h1><label for="roles">Shared With</label></h1>
            <select id="roles" size="<?= count($roles) - 1 ?>" name="roles[]" multiple style="width: 100%;"></select>
        </td>
    </tr>
</table>
<div id="testData"></div>
<form id="generalOptionsForm" method="post" action="#">
    <input type="hidden" id="path" name="path">
    <?= SSV_General::getFormSecurityFields(SSV_General::OPTIONS_ADMIN_REFERER, true, 'Reset All'); ?>
</form>
<script>
    jQuery(function ($) {
        $('#generalOptionsForm').submit(function () {
            $(this).prepend($('#roles'));
        });
    });
</script>
