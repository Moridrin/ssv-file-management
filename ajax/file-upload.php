<?php


function mp_ssv_ajax_file_manager_file_upload()
{
    if (!isset($_POST['path'])) {
        throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
    }
    // TODO Check if user is allowed to upload
    $fileManager = \mp_ssv_file_manager\SSV_FileManager::connect();
    $fileManager->put($_POST['path'].DIRECTORY_SEPARATOR.$_FILES['file']['name'], file_get_contents($_FILES["file"]["tmp_name"]));
    // TODO Remove Uploaded File
    wp_die();
}

add_action('wp_ajax_mp_ssv_file_manager_file_upload', 'mp_ssv_ajax_file_manager_file_upload');
