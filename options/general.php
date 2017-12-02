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
        <tr>
            <th scope="row">
                <label for="columns">Role</label>
            </th>
            <th scope="row">
                <label for="columns">Customizer Columns</label>
            </th>
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
                <table class="item-list" cellspacing="0" cellpadding="0" style="width: 100%;">
                    <col width="auto"/>
                    <col width="36px"/>
                    <?php
                    $realPath = $relativePath = $base = SSV_FILE_MANAGER_ROOT_FOLDER;
                    $items = array_diff(scandir($realPath), ['.', '..']);
                    array_filter($items, function ($item) use ($realPath) {
                       return is_dir($realPath.DIRECTORY_SEPARATOR.$item);
                    });
                    foreach ($items as $item) {
                        if (is_dir($realPath.DIRECTORY_SEPARATOR.$item)) {
                            ?>
                            <tr data-location="<?= $relativePath ?>" data-item="<?= $item ?>" class="selectable dbclick-navigate">
                                <td class="item-name" title="<?= $item ?>">
                                    <a href="?path=<?= $relativePath.DIRECTORY_SEPARATOR.$item ?>">
                                        <svg><use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/folder.svg#folder"></use></svg>
                                        <?=$item?>
                                    </a>
                                </td>
                                <td class="item-actions">
                                    <svg style="width: 16px; height: 35px;"><use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/sprite_icons.svg#more"></use></svg>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
    <?= SSV_General::getFormSecurityFields(SSV_General::OPTIONS_ADMIN_REFERER, true, 'Reset All'); ?>
</form>
