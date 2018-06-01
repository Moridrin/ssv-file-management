<?php

namespace mp_ssv_file_manager\templates;

use mp_general\base\BaseFunctions;

define('SSV_FILE_MANAGER_ROOT_FOLDER', get_option('ssv_file_manager__root_folder', DIRECTORY_SEPARATOR));

class FolderView
{

    public const ROOT_FOLDER = SSV_FILE_MANAGER_ROOT_FOLDER;

    public static function show(string $folderName, string $path, array $items)
    {
        ?>
        <h1 id="currentFolderTitle" style="display: inline-block"><?=BaseFunctions::escape($folderName, 'html')?></h1>
        <?php
        if (current_user_can('administrator')) {
            ?>
            <button id="addFolder" class="button button-primary" style="float: right" data-path="<?=BaseFunctions::escape($path, 'attr')?>">Add Folder</button>
            <?php
        }
        ?>
        <br/>
        <table id="itemList" class="item-list" cellspacing="0" cellpadding="0" data-path="<?=BaseFunctions::escape($path, 'attr')?>" style="width: 100%;">
            <colgroup>
                <col width="auto"/>
                <col width="36px"/>
            </colgroup>
            <?php
            if ($path !== self::ROOT_FOLDER) {
                if ($path === $folderName) {
                    $folderUp = self::ROOT_FOLDER;
                } else {
                    $folderUp = str_replace(DIRECTORY_SEPARATOR.$folderName, '', $path);
                }
                self::showFolderUp($folderUp);
            }
            foreach ($items as $item) {
                if ($item['type'] === 'dir') {
                    self::showFolder($item);
                } else {
                    self::showFile($item);
                }
            }
            ?>
        </table>
        <form id="uploadForm" enctype="multipart/form-data" data-path="<?=BaseFunctions::escape($path, 'attr')?>">
            <input type="hidden" name="action" value="mp_ssv_file_manager_upload_file">
            <input type="file" id="uploadFile" name="upload">
            <button id="uploadSubmit" type="submit" class="button button-primary" style="float: right">Upload</button>
        </form>
        <?php
    }

    private static function showFolderUp(string $path)
    {
        ?>
        <tr data-path="<?=BaseFunctions::escape($path, 'attr')?>" class="dbclick-navigate no-menu">
            <td class="item-name" title="Parent Folder">
                <span data-path="<?=BaseFunctions::escape($path, 'attr')?>">
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

    private static function showFolder(array $item)
    {
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
    }

    private static function showFile(array $item)
    {
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
