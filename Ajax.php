<?php

namespace mp_ssv_file_manager;

use Exception;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use mp_general\base\BaseFunctions;
use mp_general\base\SSV_Global;
use mp_ssv_file_manager\Options\Options;
use mp_ssv_file_manager\templates\FolderView;

class Ajax
{

    public static $callables = [];

    public static function createFolder()
    {
        if (current_user_can('mp_ssv_file_manager__can_upload') || (!is_user_logged_in() && get_option('ssv_file_manager__guests_can_upload'))) {
            BaseFunctions::checkParameters('path', 'newFolderName');
            $path        = BaseFunctions::sanitize($_REQUEST['path'], 'text') . DIRECTORY_SEPARATOR . BaseFunctions::sanitize($_REQUEST['newFolderName'], 'text');
            $encodedPath = BaseFunctions::encodeUnderscoreBase64($path);
            SSV_FileManager::connect()->createDir($encodedPath);
            wp_die(json_encode(['success' => true, 'path' => $path, 'encodedPath' => $encodedPath]));
        } else {
            SSV_Global::addError('You are not allowed to create a folder');
            wp_die(json_encode(['success' => false]));
        }
    }

    public static function deleteFile()
    {
        if (current_user_can('mp_ssv_file_manager__can_delete') || (!is_user_logged_in() && get_option('ssv_file_manager__guests_can_delete'))) {
            BaseFunctions::checkParameters('path');
            try {
                $fileManager = SSV_FileManager::connect();
                $path        = BaseFunctions::sanitize($_REQUEST['path'], 'text');
                $encodedPath = BaseFunctions::encodeUnderscoreBase64($path);
                $fileManager->delete($encodedPath);
                wp_die(json_encode(['success' => true, 'path' => $path, 'encodedPath' => $encodedPath]));
            } catch (FileNotFoundException $e) {
                SSV_Global::addError($e->getMessage());
                wp_die(json_encode(['success' => false]));
            }
        } else {
            SSV_Global::addError('You are not allowed to delete files');
            wp_die(json_encode(['success' => false]));
        }
    }

    public static function deleteFolder()
    {
        if (current_user_can('mp_ssv_file_manager__can_delete') || (!is_user_logged_in() && get_option('ssv_file_manager__guests_can_delete'))) {
            BaseFunctions::checkParameters('path');
            $path        = BaseFunctions::sanitize($_REQUEST['path'], 'text');
            $encodedPath = BaseFunctions::encodeUnderscoreBase64($path);
            $fileManager = SSV_FileManager::connect();
            $fileManager->deleteDir($encodedPath);
            wp_die(json_encode(['success' => true, 'path' => $path, 'encodedPath' => $encodedPath]));
        } else {
            SSV_Global::addError('You are not allowed to delete folders');
            wp_die(json_encode(['success' => false]));
        }
    }

    /**
     * @throws Exception
     */
    public static function editFile()
    {
        if (current_user_can('mp_ssv_file_manager__can_edit') || (!is_user_logged_in() && get_option('ssv_file_manager__guests_can_edit'))) {
            BaseFunctions::checkParameters('oldPath', 'newPath');
            try {
                $fileManager    = SSV_FileManager::connect();
                $oldPath        = BaseFunctions::sanitize($_REQUEST['oldPath'], 'text');
                $encodedOldPath = BaseFunctions::encodeUnderscoreBase64($oldPath);
                $newPath        = BaseFunctions::sanitize($_REQUEST['newPath'], 'text');
                $encodedNewPath = BaseFunctions::encodeUnderscoreBase64($newPath);
                $fileManager->rename($encodedOldPath, $encodedNewPath);
                wp_die(json_encode(['success' => true, 'oldPath' => $oldPath, 'encodedOldPath' => $encodedOldPath, 'newPath' => $newPath, 'encodedNewPath' => $encodedNewPath]));
            } catch (FileExistsException | FileNotFoundException $e) {
                SSV_Global::addError($e->getMessage());
                wp_die(json_encode(['success' => false]));
            }
        } else {
            SSV_Global::addError('You are not allowed to edit files');
            wp_die(json_encode(['success' => false]));
        }
    }

    /**
     * @throws Exception
     */
    public static function uploadFile()
    {
        if (current_user_can(SSV_FileManager::RIGHTS['upload']) || (!is_user_logged_in() && get_option(Options::OPTIONS['upload']['id']))) {
            BaseFunctions::checkParameters('path', 'fileName');
            $fileManager     = SSV_FileManager::connect();
            $path            = BaseFunctions::sanitize($_REQUEST['path'], 'text');
            $encodedPath     = BaseFunctions::encodeUnderscoreBase64($path);
            $fileName        = BaseFunctions::sanitize($_REQUEST['fileName'], 'text');
            $encodedFileName = BaseFunctions::encodeUnderscoreBase64($fileName);
            $fileManager->put($encodedPath . DIRECTORY_SEPARATOR . $encodedFileName, file_get_contents($_FILES['file']['tmp_name']), ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);
            wp_die(json_encode(['success' => true, 'path' => $path, 'encodedPath' => $encodedPath, 'fileName' => $fileName, 'encodedFileName' => $encodedFileName]));
        } else {
            SSV_Global::addError('You are not allowed to upload files');
            wp_die(json_encode(['success' => false]));
        }
    }

    /**
     * @throws Exception
     */
    public static function downloadFile()
    {
        if (current_user_can(SSV_FileManager::RIGHTS['download']) || (!is_user_logged_in() && get_option(Options::OPTIONS['download']['id']))) {
            BaseFunctions::checkParameters('path');
            $fileManager = SSV_FileManager::connect();
            $path        = BaseFunctions::sanitize($_REQUEST['path'], 'text');
            $encodedPath = BaseFunctions::encodeUnderscoreBase64($path);
            $file        = $fileManager->get($encodedPath);
            $fileName    = BaseFunctions::decodeUnderscoreBase64($file->getMetadata()['filename']);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo $file->read();
            die();
        } else {
            throw new Exception('You are not allowed to download files');
            // SSV_Global::addError('You are not allowed to download files');
            // wp_die(json_encode(['success' => false]));
        }
    }

    public static function listFolder()
    {
        if (current_user_can(SSV_FileManager::RIGHTS['view']) || (!is_user_logged_in() && get_option(Options::OPTIONS['view']['id']))) {
            $filesystem  = SSV_FileManager::connect();
            $path        = htmlspecialchars_decode(BaseFunctions::sanitize($_REQUEST['path'] ?? SSV_FileManager::ROOT_FOLDER, 'text'));
            $encodedPath = BaseFunctions::encodeUnderscoreBase64($path);
            try {
                $encodedItems = $filesystem->listContents($encodedPath);
                usort(
                    $encodedItems,
                    function ($a, $b) {
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
                $items = [];
                foreach ($encodedItems as $item) {
                    $itemName = BaseFunctions::decodeUnderscoreBase64($item['filename']);
                    if (!$itemName || !mb_check_encoding($itemName)) {
                        continue; // Don't show files and folders that haven't been uploaded with this plugin.content/plugins/ssv-file-manager/Ajax.php on line 152
                    }
                    $items[] = [
                        'type' => $item['type'],
                        'path' => BaseFunctions::decodeUnderscoreBase64($item['path']),
                        'name' => $itemName,
                    ];
                }
                FolderView::show($path, $items);
                wp_die();
            } catch (Exception $exception) {
                ?>
                <div class="notice notice-error error">Could not connect</div>
                <?php
                wp_die();
            }
        } else {
            SSV_Global::addError('You are not allowed view this content');
            wp_die(json_encode(['success' => false]));
        }
    }
}

foreach (get_class_methods(Ajax::class) as $method) {
    $callable                 = __NAMESPACE__ . '__' . BaseFunctions::toSnakeCase($method);
    Ajax::$callables[$method] = $callable;
    add_action('wp_ajax_' . $callable, [Ajax::class, $method]);
    add_action('wp_ajax_nopriv_' . $callable, [Ajax::class, $method]); // Permissions will be checked in the function (and handled with a clear response).
}
