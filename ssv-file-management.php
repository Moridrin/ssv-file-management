<?php
/**
 * Plugin Name: SSC File Management
 * Plugin URI: http://moridrin.com/ssv-file-management
 * Description: This is a plugin to let the members manage files in the frontend of the Sportal.
 * Version: 1.0.0
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */

if (!defined('ABSPATH')) {
    exit;
}

function mp_ssv_frontend_file_css()
{
    global $post;
    if (strpos($post->post_content, '[ssv_upload]') !== false) {
        wp_enqueue_style('ssv_dropzone', plugins_url() . '/ssv-file-management/css/dropzone.css');
        wp_enqueue_style('ssv_frontend_file_management_css', plugins_url() . '/ssv-file-management/css/ssv-file-management.css');
        wp_enqueue_style('ssv_context_menu', plugins_url() . '/ssv-file-management/css/jquery.contextMenu.css');
        wp_enqueue_script('ssv_dropzone', plugins_url() . '/ssv-file-management/js/dropzone.js', ['jquery']);
        wp_enqueue_script('ssv_context_menu', plugins_url() . '/ssv-file-management/js/jquery.contextMenu.js', ['jquery']);
        wp_enqueue_script('ssv_frontend_file_management_js', plugins_url() . '/ssv-file-management/js/ssv-file-management.js', ['jquery']);
        wp_localize_script('ssv_frontend_file_management_js', 'urls', ['plugins' => plugins_url(), 'admin' => admin_url('admin-ajax.php')]);
    }
}

add_action('wp_enqueue_scripts', 'mp_ssv_frontend_file_css');

function mp_ssv_frontend_file_manager($content) {
    if (strpos($content, '[ssv_upload]') !== false) {
        ob_start();
        include_once 'file-upload.php';
        $content = str_replace('[ssv_upload]', ob_get_clean(), $content);
    }
    return $content;
}
add_filter('the_content', 'mp_ssv_frontend_file_manager');

function mp_ssv_ajax_file_upload() {
    die('test');
    $fileErrors = [
        0 => "There is no error, the file uploaded with success",
        1 => "The uploaded file exceeds the upload_max_files in server settings",
        2 => "The uploaded file exceeds the MAX_FILE_SIZE from html form",
        3 => "The uploaded file uploaded only partially",
        4 => "No file was uploaded",
        6 => "Missing a temporary folder",
        7 => "Failed to write file to disk",
        8 => "A PHP extension stoped file to upload",
    ];

    $posted_data =  isset($_POST) ? $_POST : array();
    $file_data = isset($_FILES) ? $_FILES : array();

    $data = array_merge($posted_data, $file_data);
    var_dump($data);
    die();
}
add_action('wp_ajax_nopriv_mp_ssv_ajax_file_upload', 'mp_ssv_ajax_file_upload' );

function mp_ssv_ajax_create_folder() {
    mkdir(ABSPATH.'wp-content'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$_POST['path'].DIRECTORY_SEPARATOR.$_POST['newFolderName']);
    wp_die();
}
add_action('wp_ajax_mp_ssv_create_folder', 'mp_ssv_ajax_create_folder' );