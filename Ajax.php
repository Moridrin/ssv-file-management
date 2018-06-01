<?
declare(strict_types=1);


namespace mp_ssv_file_manager;

use HttpInvalidParamException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use mp_general\base\SSV_Global;
use mp_ssv_file_manager\templates\FolderView;

class Ajax
{
    /**
     * @throws HttpInvalidParamException
     */
    public static function createFolder()
    {
        if (!isset($_POST['path']) || !isset($_POST['newFolderName'])) {
            throw new HttpInvalidParamException('The "path" or "newFolderName" parameter isn\'t provided.');
        }
        $fileManager = SSV_FileManager::connect();
        $fileManager->createDir($_POST['path'].DIRECTORY_SEPARATOR.$_POST['newFolderName']);
        wp_die(json_encode(['success' => true]));
    }

    /**
     * @throws HttpInvalidParamException
     */
    public static function deleteFile()
    {
        if (!isset($_POST['path'])) {
            throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
        }
        $fileManager = SSV_FileManager::connect();
        try {
            $fileManager->delete($_POST['path']);
        } catch (FileNotFoundException $e) {
            SSV_Global::addError($e->getMessage());
            wp_die(json_encode(['success' => false]));
        }
        wp_die(json_encode(['success' => true]));
    }

    /**
     * @throws HttpInvalidParamException
     */
    public static function deleteFolder()
    {
        if (!isset($_POST['path'])) {
            throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
        }
        $fileManager = SSV_FileManager::connect();
        $fileManager->deleteDir($_POST['path']);
        wp_die(json_encode(['success' => true]));
    }

    /**
     * @throws HttpInvalidParamException
     */
    public static function editFile()
    {
        if (!isset($_POST['oldPath']) || !isset($_POST['newPath'])) {
            throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
        }
        $fileManager = SSV_FileManager::connect();
        try {
            $fileManager->rename($_POST['oldPath'], $_POST['newPath']);
        } catch (FileExistsException | FileNotFoundException $e) {
            SSV_Global::addError($e->getMessage());
            wp_die(json_encode(['success' => false]));
        }
        wp_die(json_encode(['success' => true]));
    }

    /**
     * @throws HttpInvalidParamException
     */
    public static function uploadFile()
    {
        if (!isset($_POST['path'])) {
            throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
        }
        $fileManager = SSV_FileManager::connect();
        $fileManager->put($_POST['path'].DIRECTORY_SEPARATOR.$_FILES['file']['name'], file_get_contents($_FILES["file"]["tmp_name"]), ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);
        wp_die();
    }

    public static function listFolder()
    {
        $filesystem = SSV_FileManager::connect();
        $path       = $_POST['path'] ?? DIRECTORY_SEPARATOR;
        $pathArray  = explode(DIRECTORY_SEPARATOR, $path);
        $folderName = end($pathArray);
        if (empty($folderName)) {
            $folderName = 'HOME';
        }
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
        FolderView::show($folderName, $path, $items);
        wp_die();
    }
}

add_action('wp_ajax_mp_ssv_file_manager_create_folder', [Ajax::class, 'createFolder']);
add_action('wp_ajax_mp_ssv_file_manager_upload_file', [Ajax::class, 'uploadFile']);
add_action('wp_ajax_mp_ssv_file_manager_edit_file', [Ajax::class, 'editFile']);
add_action('wp_ajax_mp_ssv_file_manager_delete_file', [Ajax::class, 'deleteFile']);
add_action('wp_ajax_mp_ssv_file_manager_delete_folder', [Ajax::class, 'deleteFolder']);

add_action('wp_ajax_mp_ssv_ajax_list_folder', [Ajax::class, 'listFolder']);
add_action('wp_ajax_nopriv_mp_ssv_ajax_list_folder', [Ajax::class, 'listFolder']);
