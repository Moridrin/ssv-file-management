<?php

namespace mp_ssv_file_manager;

use Exception;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use mp_general\base\BaseFunctions;
use mp_general\base\SSV_Global;
use mp_ssv_file_manager\templates\FolderView;

class Ajax
{

    public static $callables = [];

    public static function createFolder()
    {
        BaseFunctions::checkParameters('path', 'newFolderName');
        $path        = BaseFunctions::sanitize($_REQUEST['path'], 'text') . DIRECTORY_SEPARATOR . BaseFunctions::sanitize($_REQUEST['newFolderName'], 'text');
        $encodedPath = BaseFunctions::encodeUnderscoreBase64($path);
        SSV_FileManager::connect()->createDir($encodedPath);
        wp_die(json_encode(['success' => true, 'path' => $path, 'encodedPath' => $encodedPath]));
    }

    public static function deleteFile()
    {
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
    }

    public static function deleteFolder()
    {
        BaseFunctions::checkParameters('path');
        $path        = BaseFunctions::sanitize($_REQUEST['path'], 'text');
        $encodedPath = BaseFunctions::encodeUnderscoreBase64($path);
        SSV_FileManager::connect()->deleteDir($encodedPath);
        wp_die(json_encode(['success' => true, 'path' => $path, 'encodedPath' => $encodedPath]));
    }

    /**
     * @throws Exception
     */
    public static function editFile()
    {
        BaseFunctions::checkParameters('oldPath', 'newPath');
        try {
            $fileManager    = SSV_FileManager::connect();
            $oldPath        = BaseFunctions::sanitize($_REQUEST['oldPath'], 'text');
            $encodedOldPath = BaseFunctions::encodeUnderscoreBase64($oldPath);
            $newPath        = BaseFunctions::sanitize($_REQUEST['oldPath'], 'text');
            $encodedNewPath = BaseFunctions::encodeUnderscoreBase64($newPath);
            $fileManager->rename($encodedOldPath, $encodedNewPath);
            wp_die(json_encode(['success' => true, 'oldPath' => $oldPath, 'encodedOldPath' => $encodedOldPath, 'newPath' => $newPath, 'encodedNewPath' => $encodedNewPath]));
        } catch (FileExistsException | FileNotFoundException $e) {
            SSV_Global::addError($e->getMessage());
            wp_die(json_encode(['success' => false]));
        }
    }

    /**
     * @throws Exception
     */
    public static function uploadFile()
    {
        BaseFunctions::checkParameters('path', 'fileName');
        $fileManager     = SSV_FileManager::connect();
        $path            = BaseFunctions::sanitize($_REQUEST['path'], 'text');
        $encodedPath     = BaseFunctions::encodeUnderscoreBase64($path);
        $fileName        = BaseFunctions::sanitize($_REQUEST['fileName'], 'text');
        $encodedFileName = BaseFunctions::encodeUnderscoreBase64($fileName);
        $fileData        = file_get_contents($_FILES['file']['tmp_name']);
        $fileManager->put($encodedPath . DIRECTORY_SEPARATOR . $encodedFileName, $fileData, ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);
        wp_die(json_encode(['success' => true, 'path' => $path, 'encodedPath' => $encodedPath, 'fileName' => $fileName, 'encodedFileName' => $encodedFileName]));
    }

    public static function downloadFile()
    {
        BaseFunctions::checkParameters('path');
        $fileManager = SSV_FileManager::connect();
        $path        = BaseFunctions::sanitize($_REQUEST['path'], 'text');
        $encodedPath = BaseFunctions::encodeUnderscoreBase64($path);
        $file        = $fileManager->get($encodedPath);
        $fileName    = BaseFunctions::decodeUnderscoreBase64($file->getMetadata()['filename']);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $file->read();
        die();
    }

    public static function listFolder()
    {
        $filesystem  = SSV_FileManager::connect();
        $path        = BaseFunctions::sanitize($_REQUEST['path'] ?? DIRECTORY_SEPARATOR, 'text');
        $encodedPath = BaseFunctions::encodeUnderscoreBase64($path);
        $pathArray   = explode(DIRECTORY_SEPARATOR, $encodedPath);
        $folderName  = end($pathArray);
        if (empty($folderName)) {
            $folderName = 'HOME';
        }
        $items = $filesystem->listContents($encodedPath);
        usort(
            $items,
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
        FolderView::show($folderName, $encodedPath, $items);
        wp_die();
    }
}

foreach (get_class_methods(Ajax::class) as $method) {
    $callable                 = __NAMESPACE__ . '__' . BaseFunctions::toSnakeCase($method);
    Ajax::$callables[$method] = $callable;
    add_action('wp_ajax_' . $callable, [Ajax::class, $method]);
    if (get_option($callable . '_without_login', ($method === 'listFolder'))) {
        add_action('wp_ajax_nopriv_' . $callable, [Ajax::class, $method]);
    }
}

// \mp_general\base\BaseFunctions::var_export(Ajax::$callables, true);