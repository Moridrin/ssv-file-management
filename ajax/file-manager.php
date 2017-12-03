<?php

function mp_ssv_ajax_file_manager()
{
    $settings  = [
        'path'         => SSV_FILE_MANAGER_ROOT_FOLDER,
        'showFolderUp' => true,
        'showFolders'  => true,
        'showFiles'    => true,
    ];
    $settings  = array_merge($settings, array_intersect_key($_POST, $settings));
    $path      = realpath($settings['path']);
    $pathArray = explode(DIRECTORY_SEPARATOR, $path);

    if (!mp_ssv_starts_with($path, SSV_FILE_MANAGER_ROOT_FOLDER) && !current_user_can('administrator')) {
        ?>
        <div class="notification error">You are not allowed to view this folder.</div>
        <?php
    } else {
        ?>
        <h1 id="currentFolderTitle" style="display: inline-block"><?= end($pathArray) ?></h1>
        <button id="addFolder" style="float: right" data-path="<?= $path ?>">Add Folder</button>
        <br/>
        <table id="itemList" class="item-list" cellspacing="0" cellpadding="0" data-path="<?= $path ?>">
            <col width="auto"/>
            <col width="36px"/>
            <?php
            $items = array_diff(scandir($path), ['.', '..']);
            usort(
                $items,
                function ($a, $b) use ($path) {
                    $aIsDir = is_dir($path . DIRECTORY_SEPARATOR . $a);
                    $bIsDir = is_dir($path . DIRECTORY_SEPARATOR . $b);
                    if (($aIsDir && $bIsDir) || (!$aIsDir && !$bIsDir)) {
                        return strcmp($a, $b);
                    } elseif ($aIsDir) {
                        return -1;
                    } elseif ($bIsDir) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            );
            if ($path !== SSV_FILE_MANAGER_ROOT_FOLDER && $settings['showFolderUp']) {
                $parentPathArray = $pathArray;
                array_pop($parentPathArray);
                $folderUp = implode(DIRECTORY_SEPARATOR, $parentPathArray);
                ?>
                <tr data-location="<?= $folderUp ?>" class="dbclick-navigate">
                    <td class="item-name" title="Parent Folder">
                        <span data-location="<?= $folderUp ?>">
                            <svg>
                                <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/folder-up.svg#folder-up"></use>
                            </svg>
                            <span>..</span>
                        </span>
                    </td>
                    <td class="item-actions-unavailable">
                        <svg style="width: 16px; height: 35px;">
                            <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                        </svg>
                    </td>
                </tr>
                <?php
            }
            foreach ($items as $item) {
                if ($settings['showFolders'] && is_dir($path . DIRECTORY_SEPARATOR . $item)) {
                    ?>
                    <tr data-location="<?= $path ?>" data-item="<?= $item ?>" class="selectable dbclick-navigate">
                        <td class="item-name" title="<?= $item ?>">
                            <span data-location="<?= $path ?>" data-item="<?= $item ?>">
                                <svg>
                                    <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/folder.svg#folder"></use>
                                </svg>
                                <span class="title"><?= $item ?></span>
                            </span>
                        </td>
                        <td class="item-actions">
                            <svg style="width: 16px; height: 35px;">
                                <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                            </svg>
                        </td>
                    </tr>
                    <?php
                } elseif ($settings['showFiles']) {
                    ?>
                    <tr class="selectable dbclick-download" data-location="<?= $path ?>" data-item="<?= $item ?>">
                        <td class="item-name" title="<?= $item ?>">
                            <span>
                                <svg>
                                    <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/fileapi-upload-button.svg#fileapi-upload-button"></use>
                                </svg>
                                <span class="title"><?= $item ?></span>
                            </span>
                        </td>
                        <td class="item-actions">
                            <svg style="width: 16px; height: 35px;">
                                <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                            </svg>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
        <?php
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_ajax_file_manager', 'mp_ssv_ajax_file_manager');
add_action('wp_ajax_nopriv_mp_ssv_ajax_file_manager', 'mp_ssv_ajax_file_manager');
