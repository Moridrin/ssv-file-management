<?php
namespace mp_ssv_file_manager\options;

use mp_ssv_file_manager\SSV_FileManager;
use mp_ssv_general\BaseFunctions;

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
if (BaseFunctions::isValidPOST(BaseFunctions::OPTIONS_ADMIN_REFERER)) {
    if (isset($_POST['reset'])) {
        SSV_FileManager::resetOptions();
    } else {
        $domains = isset($_POST['domains']) ? $_POST['domains'] : [];
        $wpdb->replace(
            SSV_FileManager::TABLE_FOLDER_SITE_RIGHTS,
            [
                'path'    => realpath($_POST['path']),
                'domains' => json_encode($domains),
            ]
        );
    }
}
$domains = array_column(get_sites(), 'domain');
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
                            let $domains = $('#domains');
                            $.ajax({
                                method: 'POST',
                                url: urls.admin,
                                data: {
                                    action: 'mp_ssv_file_manager_get_shared_with_domain',
                                    path: path,
                                },
                                success: function (data) {
                                    // $('#testData').html(data);
                                    $domains.html(data);
                                    $domains.removeAttr('disabled');
                                }
                            });
                        });
                    }
                );
            </script>
        </td>
        <td>
            <h1><label for="domains">Shared With</label></h1>
            <select id="domains" size="<?= count($domains) ?>" name="domains[]" multiple style="width: 100%;"></select>
        </td>
    </tr>
</table>
<div id="testData"></div>
<form id="generalOptionsForm" method="post" action="#">
    <input type="hidden" id="path" name="path">
    <?= BaseFunctions::getFormSecurityFields(BaseFunctions::OPTIONS_ADMIN_REFERER, true, 'Reset All'); ?>
</form>
<script>
    jQuery(function ($) {
        $('#generalOptionsForm').submit(function () {
            $(this).prepend($('#domains'));
        });
    });
</script>
