<?php
/**
 * Plugin Name: SSC File Management
 * Plugin URI: http://moridrin.com/ssv-file-manager
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
define('SSV_FILE_MANAGER_ROOT_FOLDER', ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'SSV File Manager');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'general/general.php';

function mp_ssv_frontend_file_css()
{
    global $post;
    if (strpos($post->post_content, '[ssv_upload]') !== false) {
        wp_enqueue_style('ssv_dropzone', plugins_url() . '/ssv-file-manager/css/dropzone.css');
        wp_enqueue_style('ssv_frontend_file_manager_css', plugins_url() . '/ssv-file-manager/css/ssv-file-manager.css');
        wp_enqueue_style('ssv_context_menu', plugins_url() . '/ssv-file-manager/css/jquery.contextMenu.css');
        wp_enqueue_script('ssv_dropzone', plugins_url() . '/ssv-file-manager/js/dropzone.js', ['jquery']);
        wp_enqueue_script('ssv_context_menu', plugins_url() . '/ssv-file-manager/js/jquery.contextMenu.js', ['jquery']);
        wp_enqueue_script('ssv_frontend_file_manager_js', plugins_url() . '/ssv-file-manager/js/ssv-file-manager.js', ['jquery']);
        wp_localize_script('ssv_frontend_file_manager_js', 'urls', ['plugins' => plugins_url(), 'admin' => admin_url('admin-ajax.php')]);
    }
}

add_action('wp_enqueue_scripts', 'mp_ssv_frontend_file_css');

function mp_ssv_frontend_file_manager($content)
{
    if (strpos($content, '[ssv_upload]') !== false) {
        ob_start();
        include_once 'file-manager.php';
        $content = str_replace('[ssv_upload]', ob_get_clean(), $content);
    }
    return $content;
}

add_filter('the_content', 'mp_ssv_frontend_file_manager');

function mp_ssv_ajax_file_upload()
{
    $uploadDir = realpath(SSV_FILE_MANAGER_ROOT_FOLDER . DIRECTORY_SEPARATOR . $_POST['path'] . DIRECTORY_SEPARATOR);
    if (mp_ssv_starts_with($uploadDir, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('administrator')) {
        if (!is_dir($uploadDir)) {
            echo json_encode(['error' => 'The location to upload is not a directory.']);
        } elseif (!is_writable($uploadDir)) {
            echo json_encode(['error' => 'The directory is not writable.']);
        } else {
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadDir . $_FILES['file']['name'])) {
                echo json_encode(['success' => 'success']);
            } else {
                echo json_encode(['error' => 'Unknown error.']);
            }
        }
        wp_die();
    }
}

add_action('wp_ajax_mp_ssv_ajax_file_upload', 'mp_ssv_ajax_file_upload');

function mp_ssv_ajax_create_folder()
{
    $createPath = realpath(SSV_FILE_MANAGER_ROOT_FOLDER . DIRECTORY_SEPARATOR . $_POST['path'] . DIRECTORY_SEPARATOR);
    if (mp_ssv_starts_with($createPath, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('administrator')) {
        mkdir($createPath . $_POST['newFolderName']);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_create_folder', 'mp_ssv_ajax_create_folder');

function deleteItem($dirPath)
{
    if (!is_dir($dirPath)) {
        unlink($dirPath);
    } else {
        if (substr($dirPath, strlen($dirPath) - 1, 1) != DIRECTORY_SEPARATOR) {
            $dirPath .= DIRECTORY_SEPARATOR;
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                deleteItem($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}

function mp_ssv_ajax_delete_item()
{
    $deleteItem = realpath(SSV_FILE_MANAGER_ROOT_FOLDER . DIRECTORY_SEPARATOR . $_POST['path'] . DIRECTORY_SEPARATOR . $_POST['item']);
    if (mp_ssv_starts_with($deleteItem, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('administrator')) {
        deleteItem($deleteItem);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_delete_item', 'mp_ssv_ajax_delete_item');

function mp_ssv_ajax_rename_item()
{
    $base       = realpath(SSV_FILE_MANAGER_ROOT_FOLDER . DIRECTORY_SEPARATOR . $_POST['path'] . DIRECTORY_SEPARATOR);
    $renameItem = realpath($base . $_POST['oldItemName']);
    if (mp_ssv_starts_with($renameItem, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('administrator')) {
        rename($renameItem, realpath($base . $_POST['newItemName']));
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_rename_item', 'mp_ssv_ajax_rename_item');
