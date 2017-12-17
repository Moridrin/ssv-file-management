<?php

use mp_ssv_file_manager\SSV_FileManager;
use mp_ssv_general\SSV_General;

function mp_ssv_ajax_file_manager()
{
    $options  = $_POST['options'] + [
        'showFolderUp'      => true,
        'showFolders'       => true,
        'showFiles'         => true,
        'selectableFiles'   => true,
        'selectableFolders' => true,
        'allowCreateFolder' => true,
    ];
    foreach ($options as &$option) {
        $option = filter_var($option, FILTER_VALIDATE_BOOLEAN);
    }
    if (empty($_POST['path']) && !current_user_can('manage_sites')) {
        $folders = SSV_FileManager::getRootFoldersForSite();
        ?>
        <h1 id="currentFolderTitle" style="display: inline-block">SSV Folder Manager</h1>
        <br/>
        <?php if (count($folders) === 0) {
            ?><div class="notification">There are no folders you have access to.</div><?php
        }
        ?>
        <table id="itemList" class="item-list" cellspacing="0" cellpadding="0" data-path="null" style="width: 100%;">
            <colgroup>
                <col width="auto"/>
                <col width="36px"/>
            </colgroup>
            <?php
            foreach ($folders as $path) {
                $pathArray = explode(DIRECTORY_SEPARATOR, $path);
                $item = array_pop($pathArray);
                $path = implode(DIRECTORY_SEPARATOR, $pathArray);
                ?>
                <tr class="dbclick-navigate folder no-menu" data-location="<?= $path ?>" data-item="<?= $item ?>">
                    <td class="item-name" title="<?= $item ?>">
                        <span data-location="<?= $path ?>" data-item="<?= $item ?>">
                            <svg>
                                <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/folder.svg#folder"></use>
                            </svg>
                            <span class="title"><?= $item ?></span>
                        </span>
                    </td>
                    <td class="item-actions-unavailable">
                        <svg>
                            <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                        </svg>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    } else {
        $path      = realpath(!empty($_POST['path']) ? $_POST['path'] : SSV_FILE_MANAGER_ROOT_FOLDER);
        $pathArray = explode(DIRECTORY_SEPARATOR, $path);

        if (!mp_ssv_starts_with($path, SSV_FILE_MANAGER_ROOT_FOLDER) || !SSV_FileManager::hasFolderAccess($path)) {
            ?>
            <div class="notification error">You are not allowed to view this folder.</div>
            <?php
        } else {
            ?>
            <h1 id="currentFolderTitle" style="display: inline-block"><?= end($pathArray) ?></h1>
            <?php if ($options['allowCreateFolder']): ?>
                <button id="addFolder" class="button button-primary" style="float: right" data-path="<?= $path ?>">Add Folder</button>
            <?php endif; ?>
            <br/>
            <table id="itemList" class="item-list" cellspacing="0" cellpadding="0" data-path="<?= $path ?>" style="width: 100%;">
                <colgroup>
                    <col width="auto"/>
                    <col width="36px"/>
                </colgroup>
                <?php
                $items = array_diff(scandir($path), ['.', '..', 'lost+found']);
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
                if ($options['showFolderUp'] && $path !== SSV_FILE_MANAGER_ROOT_FOLDER) {
                    $parentPathArray = $pathArray;
                    array_pop($parentPathArray);
                    $folderUp = implode(DIRECTORY_SEPARATOR, $parentPathArray);
                    if (!SSV_FileManager::hasFolderAccess($folderUp)) {
                        $folderUp = null;
                    }
                    ?>
                    <tr data-location="<?= $folderUp ?>" class="dbclick-navigate no-menu">
                        <td class="item-name" title="Parent Folder">
                            <span data-location="<?= $folderUp ?>">
                                <svg>
                                    <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/folder-up.svg#folder-up"></use>
                                </svg>
                                <span>..</span>
                            </span>
                        </td>
                        <td class="item-actions-unavailable">
                            <svg>
                                <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                            </svg>
                        </td>
                    </tr>
                    <?php
                }
                foreach ($items as $item) {
                    if ($options['showFolders'] && SSV_FileManager::hasFolderAccess($path . DIRECTORY_SEPARATOR . $item)) {
                        ?>
                        <tr class="<?= $options['selectableFolders'] ? 'selectable ' : '' ?>dbclick-navigate folder" data-location="<?= $path ?>" data-item="<?= $item ?>">
                            <td class="item-name" title="<?= $item ?>">
                                <span data-location="<?= $path ?>" data-item="<?= $item ?>">
                                    <svg>
                                        <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/folder.svg#folder"></use>
                                    </svg>
                                    <span class="title"><?= $item ?></span>
                                </span>
                            </td>
                            <td class="item-actions">
                                <svg>
                                    <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                                </svg>
                            </td>
                        </tr>
                        <?php
                    } elseif ($options['showFiles'] && is_file($path . DIRECTORY_SEPARATOR . $item)) {
                        ?>
                        <tr class="<?= $options['selectableFiles'] ? 'selectable ' : '' ?>dbclick-download file" data-location="<?= $path ?>" data-item="<?= $item ?>">
                            <td class="item-name" title="<?= $item ?>">
                                <span>
                                    <svg>
                                        <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/fileapi-upload-button.svg#fileapi-upload-button"></use>
                                    </svg>
                                    <span class="title"><?= $item ?></span>
                                </span>
                            </td>
                            <td class="item-actions">
                                <svg>
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
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_ajax_file_manager', 'mp_ssv_ajax_file_manager');
add_action('wp_ajax_nopriv_mp_ssv_ajax_file_manager', 'mp_ssv_ajax_file_manager');
