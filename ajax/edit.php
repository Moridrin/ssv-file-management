<?php


use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use mp_general\base\SSV_Global;

/**
 * @throws HttpInvalidParamException
 */
function mp_ssv_ajax_file_manager_edit()
{
    if (!isset($_POST['oldPath']) || !isset($_POST['newPath'])) {
        throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
    }
    // TODO Check if user is allowed to upload
    $fileManager = \mp_ssv_file_manager\SSV_FileManager::connect();
    try {
        $fileManager->rename($_POST['oldPath'], $_POST['newPath']);
    } catch (FileExistsException | FileNotFoundException $e) {
        SSV_Global::addError($e->getMessage());
        wp_die(json_encode(['success' => false]));
    }
    wp_die(json_encode(['success' => true]));
}

add_action('wp_ajax_mp_ssv_file_manager_edit', 'mp_ssv_ajax_file_manager_edit');
