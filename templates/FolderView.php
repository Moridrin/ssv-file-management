<?php

namespace mp_ssv_file_manager\templates;

use mp_general\base\BaseFunctions;

define('SSV_FILE_MANAGER_ROOT_FOLDER', get_option('ssv_file_manager__root_folder', DIRECTORY_SEPARATOR));

class FolderView
{

    public const ROOT_FOLDER = SSV_FILE_MANAGER_ROOT_FOLDER;

    public static function show(string $encodedFolderName, string $encodedPath, array $encodedItems)
    {
        ?>
        <h1 id="currentFolderTitle" style="display: inline-block" data-path="<?= BaseFunctions::escape($encodedPath, 'attr') ?>"><?= BaseFunctions::escape(BaseFunctions::decodeUnderscoreBase64($encodedFolderName), 'html') ?></h1>
        <?php
        if (current_user_can('manage_files')) {
            ?>
            <button id="addFolder" class="button button-primary" style="float: right" data-path="<?= BaseFunctions::escape($encodedPath, 'attr') ?>">Add Folder</button>
            <?php
        }
        ?>
        <br/>
        <table id="itemList" class="item-list" cellspacing="0" cellpadding="0" data-path="<?= BaseFunctions::escape($encodedPath, 'attr') ?>" style="width: 100%;">
            <colgroup>
                <col width="auto"/>
                <col width="36px"/>
            </colgroup>
            <?php
            if ($encodedPath !== self::ROOT_FOLDER) {
                if ($encodedPath === $encodedFolderName) { // If the path equals the folder the parent folder is the root folder
                    $folderUp = self::ROOT_FOLDER;
                } else {
                    $folderUp = BaseFunctions::decodeUnderscoreBase64(str_replace(DIRECTORY_SEPARATOR . $encodedFolderName, '', $encodedPath));
                }
                self::showFolderUp($folderUp);
            }
            foreach ($encodedItems as $encodedItem) {
                $itemName = BaseFunctions::decodeUnderscoreBase64($encodedItem['filename']);
                if (!$itemName || !mb_check_encoding($itemName)) {
                    continue; // Don't show files and folders that haven't been uploaded with this plugin.
                }
                if ($encodedItem['type'] === 'dir') {
                    self::showFolder($encodedItem, $itemName);
                } else {
                    self::showFile($encodedItem, $itemName);
                }
            }
            ?>
        </table>
        <?php
        if (current_user_can('manage_files')) {
            ?>
            <input type="file" id="fileUploadInput" style="display: none;" multiple>
            <div id="dropTarget" style="cursor: pointer; border: 5px dashed #bbb; text-align: center; line-height: 150px;">Drop Files to Upload</div>
            <script>
                (function () {
                    let fileUploadInput = document.getElementById('fileUploadInput');
                    let dropTarget = document.getElementById('dropTarget');
                    if (window.File && window.FileList && window.FileReader) {
                        let xhr = new XMLHttpRequest();
                        if (xhr.upload) {
                            dropTarget.addEventListener('click', function (event) {
                                fileUploadInput.click();
                            }, false);
                            fileUploadInput.addEventListener('change', fileManager.uploader.FileSelectHandler, false);
                            dropTarget.addEventListener('drop', fileManager.uploader.FileSelectHandler, false);
                            dropTarget.addEventListener('dragover', function (event) { event.preventDefault(); });
                            dropTarget.addEventListener('dragleave', function (event) { event.preventDefault(); });
                        }
                    }
                })();
            </script>
            <?php
        }
    }

    private static function showFolderUp(string $path)
    {
        ?>
        <tr data-path="<?= BaseFunctions::escape($path, 'attr') ?>" class="dbclick-navigate no-menu">
            <td class="item-name" title="Parent Folder">
                <span data-path="<?= BaseFunctions::escape($path, 'attr') ?>">
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

    private static function showFolder(array $encodedItem, string $itemName)
    {
        ?>
        <tr class="dbclick-navigate folder" data-path="<?= BaseFunctions::escape(BaseFunctions::decodeUnderscoreBase64($encodedItem['path']), 'attr') ?>">
            <td class="item-name" title="<?= BaseFunctions::escape($itemName, 'attr') ?>">
                <span data-path="<?= BaseFunctions::escape(BaseFunctions::decodeUnderscoreBase64($encodedItem['path']), 'attr') ?>">
                    <svg>
                        <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/folder.svg#folder"></use>
                    </svg>
                    <span class="title"><?= BaseFunctions::escape($itemName, 'html') ?></span>
                </span>
            </td>
            <td class="folder-actions">
                <svg>
                    <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/sprite_icons.svg#more"></use>
                </svg>
            </td>
        </tr>
        <?php
    }

    private static function showFile(array $encodedItem, string $itemName)
    {
        ?>
        <tr class="dbclick-open file" data-path="<?= BaseFunctions::escape(BaseFunctions::decodeUnderscoreBase64($encodedItem['path']), 'attr') ?>" data-filename="<?= BaseFunctions::escape($itemName, 'attr') ?>">
            <td class="item-name" title="<?= BaseFunctions::escape($itemName, 'attr') ?>">
                <span>
                    <svg>
                        <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/fileapi-upload-button.svg#fileapi-upload-button"></use>
                    </svg>
                    <span class="title"><?= BaseFunctions::escape($itemName, 'html') ?></span>
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
