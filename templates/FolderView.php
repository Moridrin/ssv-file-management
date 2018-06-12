<?php

namespace mp_ssv_file_manager\templates;

use mp_general\base\BaseFunctions;
use mp_general\base\SSV_Global;
use mp_ssv_file_manager\Options\Options;
use mp_ssv_file_manager\SSV_FileManager;

define('SSV_FILE_MANAGER_ROOT_FOLDER', get_option('ssv_file_manager__root_folder', DIRECTORY_SEPARATOR));

class FolderView
{

    public static function show(string $currentPath, array $items)
    {
        if (!(current_user_can(SSV_FileManager::RIGHTS['view']) || (!is_user_logged_in() && get_option(Options::OPTIONS['guests']['view']['id'])))) {
            SSV_Global::addError('You do not have enough rights to view these files');
            SSV_Global::showErrors();
            return;
        }
        $breadcrumbs   = array_filter(explode(DIRECTORY_SEPARATOR, $currentPath));
        $currentFolder = array_pop($breadcrumbs);
        if (empty($currentFolder)) {
            $currentFolder = "HOME";
        }
        ?>
        <h1 id="currentFolderTitle" style="display: inline-block; width: 100%;" data-path="<?= BaseFunctions::escape($currentPath, 'attr') ?>">
            <?= BaseFunctions::escape($currentFolder, 'html') ?>
            <?php
            if (current_user_can(SSV_FileManager::RIGHTS['upload'])) {
                ?>
                <button id="addFolder" class="button btn button-primary" style="float: right">Add Folder</button>
                <?php
            }
            ?>
        </h1>
        <br/>
        <div id="itemListContainer">
            <table id="itemList" class="item-list" cellspacing="0" cellpadding="0" style="width: 100%; margin: 10px 0;">
                <colgroup>
                    <col width="auto"/>
                    <col width="36px"/>
                </colgroup>
                <?php
                if ($currentPath !== SSV_FileManager::ROOT_FOLDER) {
                    if (empty($breadcrumbs)) {
                        $folderUp = SSV_FileManager::ROOT_FOLDER;
                    } else {
                        $folderUp = implode(DIRECTORY_SEPARATOR, $breadcrumbs);
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
            <div id="itemListLoader" class="cssLoader"></div>
        </div>
        <?php
        if (current_user_can(SSV_FileManager::RIGHTS['upload']) || (!is_user_logged_in() && get_option(Options::OPTIONS['guests']['upload']['id']))) {
            ?>
            <input type="file" id="fileUploadInput" style="display: none;" multiple>
            <div id="dropTarget" style="cursor: pointer; border: 5px dashed #bbb; text-align: center; line-height: 150px;">
                <div id="dropFilesLabel">Drop Files to Upload</div>
                <table id="uploadingFilesList" style="line-height: initial; margin: 0; padding: 0;"></table>
            </div>
            <script>
                (function () {
                    let fileUploadInput = document.getElementById('fileUploadInput');
                    let dropTarget = document.getElementById('dropTarget');
                    if (window.File && window.FileList && window.FileReader) {
                        let xhr = new XMLHttpRequest();
                        if (xhr.upload) {
                            dropTarget.addEventListener('click', function () {
                                fileUploadInput.click();
                            }, false);
                            fileUploadInput.addEventListener('change', FileManager.uploader.FileSelectHandler, false);
                            dropTarget.addEventListener('drop', FileManager.uploader.FileSelectHandler, false);
                            dropTarget.addEventListener('dragover', function (event) {
                                event.preventDefault();
                            });
                            dropTarget.addEventListener('dragleave', function (event) {
                                event.preventDefault();
                            });
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
        <tr
                class="click-navigate no-menu"
                title="Parent Folder"
                data-path="<?= BaseFunctions::escape($path, 'attr') ?>"
        >
            <td class="item-name" title="Parent Folder">
                <span data-path="<?= BaseFunctions::escape($path, 'attr') ?>">
                    <svg style="fill: <?= BaseFunctions::escape(get_option(Options::OPTIONS['appearance']['folder_color']['id']), 'attr') ?>">
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

    private static function showFolder(array $item)
    {
        ?>
        <tr class="folder">
            <td
                    class="click-navigate item-name"
                    title="<?= BaseFunctions::escape($item['name'], 'attr') ?>"
                    data-path="<?= BaseFunctions::escape($item['path'], 'attr') ?>"
            >
                <span>
                    <svg style="fill: <?= BaseFunctions::escape(get_option(Options::OPTIONS['appearance']['folder_color']['id']), 'attr') ?>">
                        <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/folder.svg#folder"></use>
                    </svg>
                    <span class="title"><?= BaseFunctions::escape($item['name'], 'html') ?></span>
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

    private static function showFile(array $item)
    {
        ?>
        <tr class="file">
            <td
                    class="click-download item-name"
                    title="<?= BaseFunctions::escape($item['name'], 'attr') ?>"
                    data-path="<?= BaseFunctions::escape($item['path'], 'attr') ?>"
                    data-filename="<?= BaseFunctions::escape($item['name'], 'attr') ?>"
            >
                <span>
                    <svg style="fill: <?= BaseFunctions::escape(get_option(Options::OPTIONS['appearance']['file_color']['id']), 'attr') ?>">
                        <use xlink:href="<?= plugins_url() ?>/ssv-file-manager/images/sprite_icons.svg#article"></use>
                    </svg>
                    <span class="title"><?= BaseFunctions::escape($item['name'], 'html') ?></span>
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
