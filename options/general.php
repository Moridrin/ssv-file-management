<?php
namespace mp_ssv_users\options;
use mp_ssv_general\SSV_General;
use mp_ssv_general\User;

if (!defined('ABSPATH')) {
    exit;
}

if (SSV_General::isValidPOST(SSV_General::OPTIONS_ADMIN_REFERER)) {
    if (isset($_POST['reset'])) {
        SSV_General::resetOptions();
    } else {
        $customFieldFields = isset($_POST['columns']) ? SSV_General::sanitize($_POST['columns'], $columns) : array();
        User::getCurrent()->updateMeta(SSV_General::USER_OPTION_CUSTOM_FIELD_FIELDS, json_encode($customFieldFields), false);
    }
}
$roles = SSV_General::getRoles();
?>
<form method="post" action="#">
    <table class="form-table">
        <colgroup>
            <col width="250px"/>
            <col width="auto"/>
            <col width="50px"/>
        </colgroup>
        <tr>
            <td scope="row">
                <label for="columns">Role</label>
            </td>
            <td scope="row">
                <label for="columns">Customizer Columns</label>
            </td>
            <td scope="row">
                <label for="columns">Save</label>
            </td>
        </tr>
        <tr>
            <td>
                <select id="columns" size="<?= count($roles) ?>" name="columns[]" multiple style="width: 100%;">
                    <?php
                    foreach ($roles as $role) {
                        ?>
                        <option value="<?= $role ?>">
                            <?= $role ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </td>
            <td>
                <div id="fileManager"></div>
                <script>
                    fileManagerInit('fileManager', '<?= SSV_FILE_MANAGER_ROOT_FOLDER ?>', {
                        showFiles: false,
                        showFolders: true,
                        allowCreateFolder: true,
                        allowDownload: false,
                        allowRename: true,
                        allowDelete: true,
                        selectableFolders: true,
                        selectableFiles: false,
                        multiSelect: false,
                    });
                </script>
            </td>
            <td>
                <button class="button button-primary">Save</button>
            </td>
        </tr>
    </table>
    <?= SSV_General::getFormSecurityFields(SSV_General::OPTIONS_ADMIN_REFERER, true, 'Reset All'); ?>
</form>
