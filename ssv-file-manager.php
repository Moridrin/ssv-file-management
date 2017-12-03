<?php
/**
 * Plugin Name: SSV File Management
 * Plugin URI: http://moridrin.com/ssv-file-manager
 * Description: This is a plugin to let the members manage files in the frontend of the Sportal.
 * Version: 1.0.0
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */

use mp_ssv_general\SSV_General;

if (!defined('ABSPATH')) {
    exit;
}
define('SSV_FILE_MANAGER_ROOT_FOLDER', realpath(ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'SSV File Manager'));

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'general/general.php';
require_once 'options/options.php';
require_once 'ajax/file-manager.php';

function mp_ssv_frontend_file_manager_scripts()
{
    global $post;
    if (strpos($post->post_content, '[ssv_file_manager]') !== false) {
        wp_enqueue_style('ssv_dropzone', plugins_url() . '/ssv-file-manager/css/dropzone.css');
        wp_enqueue_style('ssv_frontend_file_manager_css', plugins_url() . '/ssv-file-manager/css/ssv-file-manager.css');
        wp_enqueue_style('ssv_context_menu', plugins_url() . '/ssv-file-manager/css/jquery.contextMenu.css');
        wp_enqueue_script('ssv_dropzone', plugins_url() . '/ssv-file-manager/js/dropzone.js', ['jquery']);
        wp_enqueue_script('ssv_context_menu', plugins_url() . '/ssv-file-manager/js/jquery.contextMenu.js', ['jquery']);
        wp_enqueue_script('ssv_frontend_file_manager_js', plugins_url() . '/ssv-file-manager/js/ssv-file-manager.js', ['jquery']);
        wp_localize_script(
            'ssv_frontend_file_manager_js',
            'urls',
            [
                'plugins'  => plugins_url(),
                'admin'    => admin_url('admin-ajax.php'),
                'base'     => get_home_url(),
                'basePath' => ABSPATH,
            ]
        );
    }
}

add_action('wp_enqueue_scripts', 'mp_ssv_frontend_file_manager_scripts');

function mp_ssv_backend_file_manager_scripts()
{
    wp_enqueue_style('ssv_dropzone', plugins_url() . '/ssv-file-manager/css/dropzone.css');
    wp_enqueue_style('ssv_frontend_file_manager_css', plugins_url() . '/ssv-file-manager/css/ssv-file-manager.css');
    wp_enqueue_style('ssv_context_menu', plugins_url() . '/ssv-file-manager/css/jquery.contextMenu.css');
    wp_enqueue_script('ssv_dropzone', plugins_url() . '/ssv-file-manager/js/dropzone.js', ['jquery']);
    wp_enqueue_script('ssv_context_menu', plugins_url() . '/ssv-file-manager/js/jquery.contextMenu.js', ['jquery']);
    wp_enqueue_script('ssv_frontend_file_manager_js', plugins_url() . '/ssv-file-manager/js/ssv-file-manager.js', ['jquery']);
    wp_localize_script('ssv_frontend_file_manager_js', 'urls', ['plugins' => plugins_url(), 'admin' => admin_url('admin-ajax.php')]);
}

add_action('admin_enqueue_scripts', 'mp_ssv_backend_file_manager_scripts');

function mp_ssv_frontend_file_manager_filter($content)
{
    if (strpos($content, '[ssv_file_manager]') !== false) {
        ob_start();
        ?>
        <div id="fileManager"></div>
        <script>
            fileManagerInit('fileManager', '<?= SSV_FILE_MANAGER_ROOT_FOLDER ?>');
        </script>
        <?php
        $content = str_replace('[ssv_file_manager]', ob_get_clean(), $content);
    }
    if (strpos($content, '[ssv_file_manager_upload]') !== false) {
        ob_start();
        ?>
        <h1>Add Items</h1>
        <form action="<?= admin_url('admin-ajax.php') ?>" class="dropzone">
            <input name="action" type="hidden" value="mp_ssv_ajax_file_upload"/>
            <input name="path" type="hidden" value="<?= SSV_FILE_MANAGER_ROOT_FOLDER ?>"/>
            <div class="fallback">
                <input name="file" type="file" multiple/>
            </div>
        </form>
        <?php
        $content = str_replace('[ssv_file_manager_upload]', ob_get_clean(), $content);
    }
    return $content;
}

add_filter('the_content', 'mp_ssv_frontend_file_manager_filter');

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

add_action('wp_ajax_mp_ssv_file_upload', 'mp_ssv_ajax_file_upload');

function mp_ssv_ajax_create_folder()
{
    $createPath = realpath($_POST['path']);
    var_export($createPath);
    var_export($createPath . DIRECTORY_SEPARATOR . $_POST['newFolderName']);
    if (mp_ssv_starts_with($createPath, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('administrator')) {
        mkdir($createPath . DIRECTORY_SEPARATOR . $_POST['newFolderName']);
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
    $base       = realpath($_POST['path']);
    $deleteItem = $base . DIRECTORY_SEPARATOR . $_POST['item'];
    if (mp_ssv_starts_with($deleteItem, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('administrator')) {
        deleteItem($deleteItem);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_delete_item', 'mp_ssv_ajax_delete_item');

function mp_ssv_ajax_rename_item()
{
    $base        = realpath($_POST['path']);
    $currentItem = $base . DIRECTORY_SEPARATOR . $_POST['oldItemName'];
    $newItem     = $base . DIRECTORY_SEPARATOR . $_POST['newItemName'];
    if (mp_ssv_starts_with($currentItem, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('administrator')) {
        echo json_encode(rename($currentItem, $newItem));
    } else {
        echo json_encode(false);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_rename_item', 'mp_ssv_ajax_rename_item');
