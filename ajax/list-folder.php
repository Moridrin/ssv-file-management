<?php

use mp_general\base\BaseFunctions;
use mp_ssv_file_manager\SSV_FileManager;

function mp_ssv_ajax_list_folder()
{
    $filesystem = SSV_FileManager::connect();
    $path       = $_POST['path'] ?? DIRECTORY_SEPARATOR;
    $pathArray  = explode(DIRECTORY_SEPARATOR, $path);
    $folderName = end($pathArray);
    if (empty($folderName)) {
        $folderName = 'HOME';
    }
    ?>
    <h1 id="currentFolderTitle" style="display: inline-block"><?=BaseFunctions::escape($folderName, 'html')?></h1>
    <?php if (current_user_can('administrator')): // TODO Fix correct right ?>
    <button id="addFolder" class="button button-primary" style="float: right" data-path="<?=$path?>">Add Folder</button>
<?php endif; ?>
    <br/>
    <table id="itemList" class="item-list" cellspacing="0" cellpadding="0" data-path="<?=$path?>" style="width: 100%;">
        <colgroup>
            <col width="auto"/>
            <col width="36px"/>
        </colgroup>
        <?php
        $items = $filesystem->listContents($path);
        usort(
            $items,
            function ($a, $b) use ($path) {
                $aIsDir = $a['type'] === 'dir';
                $bIsDir = $b['type'] === 'dir';
                if (($aIsDir && $bIsDir) || (!$aIsDir && !$bIsDir)) {
                    return strcmp($a['filename'], $b['filename']);
                } elseif ($aIsDir) {
                    return -1;
                } elseif ($bIsDir) {
                    return 1;
                } else {
                    return 0;
                }
            }
        );
        if ($path !== DIRECTORY_SEPARATOR) {
            $parentPathArray = $pathArray;
            array_pop($parentPathArray);
            $folderUp = empty($parentPathArray) ? DIRECTORY_SEPARATOR : implode(DIRECTORY_SEPARATOR, $parentPathArray);
            ?>
            <tr data-path="<?=BaseFunctions::escape($folderUp, 'attr')?>" class="dbclick-navigate no-menu">
                <td class="item-name" title="Parent Folder">
                    <span data-path="<?=BaseFunctions::escape($folderUp, 'attr')?>">
                        <svg>
                            <use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/folder-up.svg#folder-up"></use>
                        </svg>
                        <span>..</span>
                    </span>
                </td>
                <td class="item-actions-unavailable">
                    <svg>
                        <use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                    </svg>
                </td>
            </tr>
            <?php
        }
        foreach ($items as $item) {
            if ($item['type'] === 'dir') {
                ?>
                <tr class="dbclick-navigate folder" data-path="<?=BaseFunctions::escape($item['path'], 'attr')?>">
                    <td class="item-name" title="<?=BaseFunctions::escape($item['filename'], 'attr')?>">
                        <span data-path="<?=BaseFunctions::escape($item['path'], 'attr')?>">
                            <svg>
                                <use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/folder.svg#folder"></use>
                            </svg>
                            <span class="title"><?=BaseFunctions::escape($item['filename'], 'html')?></span>
                        </span>
                    </td>
                    <td class="folder-actions">
                        <svg>
                            <use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                        </svg>
                    </td>
                </tr>
                <?php
            } else {
                ?>
                <tr class="dbclick-open file" data-path="<?=BaseFunctions::escape($item['path'], 'attr')?>" data-filename="<?=BaseFunctions::escape($item['basename'], 'attr')?>">
                    <td class="item-name" title="<?=BaseFunctions::escape($item['filename'], 'attr')?>">
                        <span>
                            <svg>
                                <use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/fileapi-upload-button.svg#fileapi-upload-button"></use>
                            </svg>
                            <span class="title"><?=BaseFunctions::escape($item['filename'], 'html')?></span>
                        </span>
                    </td>
                    <td class="item-actions">
                        <svg>
                            <use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                        </svg>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
    </table>
    <?php
    wp_die();
}

add_action('wp_ajax_mp_ssv_ajax_list_folder', 'mp_ssv_ajax_list_folder');
add_action('wp_ajax_nopriv_mp_ssv_ajax_list_folder', 'mp_ssv_ajax_list_folder');
