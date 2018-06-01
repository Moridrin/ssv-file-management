<?php


use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use mp_general\base\SSV_Global;

/**
 * @throws HttpInvalidParamException
 */
function mp_ssv_ajax_file_manager_create_folder()
{
    if (!isset($_POST['path']) || !isset($_POST['newFolderName'])) {
        throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
    }
    $fileManager = \mp_ssv_file_manager\SSV_FileManager::connect();
    $fileManager->createDir($_POST['path'].DIRECTORY_SEPARATOR.$_POST['newFolderName']);
    wp_die(json_encode(['success' => true]));
}

add_action('wp_ajax_mp_ssv_file_manager_create_folder', 'mp_ssv_ajax_file_manager_create_folder');
