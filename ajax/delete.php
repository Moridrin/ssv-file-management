<?php


use League\Flysystem\FileNotFoundException;

/**
 * @throws HttpInvalidParamException
 */
function mp_ssv_ajax_file_manager_delete()
{
    if (!isset($_POST['path'])) {
        throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
    }
    $fileManager = \mp_ssv_file_manager\SSV_FileManager::connect();
    try {
        $fileManager->delete($_POST['path']);
    } catch (FileNotFoundException $e) {
        wp_die(json_encode(['success' => false]));
    }
    wp_die(json_encode(['success' => true]));
}

add_action('wp_ajax_mp_ssv_file_manager_delete', 'mp_ssv_ajax_file_manager_delete');
