<?php

use mp_ssv_file_manager\SSV_FileManager;
use mp_ssv_general\SSV_General;

if (!defined('ABSPATH')) {
    exit;
}

#region Register
function mp_ssv_file_manager_register_plugin()
{
    /** @var wpdb $wpdb */
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();

    #region Permissions Tables
    $table_name = SSV_FileManager::TABLE_FOLDER_RIGHTS;
    $sql
                = "
		CREATE TABLE IF NOT EXISTS $table_name (
			path VARCHAR(255) NOT NULL,
			roles VARCHAR(255) NOT NULL ,
			PRIMARY KEY (path)
		) $charset_collate;";
    $wpdb->query($sql);
    $table_name = SSV_FileManager::TABLE_FOLDER_SITE_RIGHTS;
    $sql
                = "
		CREATE TABLE IF NOT EXISTS $table_name (
			path VARCHAR(255) NOT NULL,
			domains VARCHAR(255) NOT NULL ,
			PRIMARY KEY (path)
		) $charset_collate;";
    $wpdb->query($sql);
    #endregion

    #region Setup Sites and Access
    $items = scandir(SSV_FileManager::ROOT_FOLDER);
    foreach (get_sites() as $site) {
        if (!in_array($site->domain, $items)) {
            mkdir(SSV_FileManager::ROOT_FOLDER . DIRECTORY_SEPARATOR . $site->domain);
        }
        $wpdb->insert(
            SSV_FileManager::TABLE_FOLDER_SITE_RIGHTS,
            [
                'path'    => realpath(SSV_FileManager::ROOT_FOLDER . DIRECTORY_SEPARATOR . $site->domain),
                'domains' => json_encode([$site->domain]),
            ]
        );
    }
    #endregion
}

register_activation_hook(SSV_FILE_MANAGER_PATH . 'ssv-file-manager.php', 'mp_ssv_file_manager_register_plugin');
register_activation_hook(SSV_FILE_MANAGER_PATH . 'ssv-file-manager.php', 'mp_ssv_general_register_plugin');
#endregion

#region Unregister
function mp_ssv_file_manager_unregister()
{
}

register_deactivation_hook(SSV_FILE_MANAGER_PATH . 'ssv-file-manager.php', 'mp_ssv_file_manager_unregister');
#endregion

#region UnInstall
function mp_ssv_file_manager_uninstall()
{
    global $wpdb;
    $wpdb->show_errors();
    $table_name = SSV_FileManager::TABLE_FOLDER_RIGHTS;
    $sql        = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
    $table_name = SSV_FileManager::TABLE_FOLDER_SITE_RIGHTS;
    $sql        = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}

register_uninstall_hook(SSV_FILE_MANAGER_PATH . 'ssv-file-manager.php', 'mp_ssv_file_manager_uninstall');
#endregion

#region Enquire Scripts
function mp_ssv_file_manager_scripts($hook)
{
    global $post;
    if (
        (isset($post) && (strpos($post->post_content, '[ssv_file_manager]') !== false))
        || (is_admin() && ($hook === 'ssv-options_page_ssv-file-manager-settings'))
    ) {
        wp_enqueue_style('ssv_dropzone', plugins_url() . '/ssv-file-manager/css/dropzone.css');
        wp_enqueue_style('ssv_context_menu', plugins_url() . '/ssv-file-manager/css/jquery.contextMenu.css');
        wp_enqueue_style('ssv_frontend_file_manager_css', plugins_url() . '/ssv-file-manager/css/ssv-file-manager.css');
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

add_action('wp_enqueue_scripts', 'mp_ssv_file_manager_scripts');
add_action('admin_enqueue_scripts', 'mp_ssv_file_manager_scripts');
#endregion

#region Filter Content
function mp_ssv_frontend_file_manager_filter($content)
{
    if (strpos($content, '[ssv_file_manager]') !== false) {
        ob_start();
        ?>
        <div id="fileManager"></div>
        <script>
            fileManagerInit('fileManager', null);
        </script>
        <?php
        $content = str_replace('[ssv_file_manager]', ob_get_clean(), $content);
    }
    if (strpos($content, '[ssv_file_manager_upload]') !== false) {
        ob_start();
        ?>
        <h1>Add Items</h1>
        <form id="uploadFile" action="<?= admin_url('admin-ajax.php') ?>" class="dropzone">
            <input name="action" type="hidden" value="mp_ssv_file_manager_file_upload"/>
            <input id="uploadPath" name="path" type="hidden" value="<?= SSV_FILE_MANAGER_ROOT_FOLDER ?>"/>
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
#endregion

#region File Upload
function mp_ssv_ajax_file_manager_file_upload()
{
    if (!isset($_POST['path'])) {
        throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
    }
    $uploadDir = realpath($_POST['path']);
    if (mp_ssv_starts_with($uploadDir, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('manage_sites')) {
        if (!is_dir($uploadDir)) {
            echo json_encode(['success' => false, 'message' => $uploadDir . ' is not a directory.']);
        } elseif (!is_writable($uploadDir)) {
            echo json_encode(['success' => false, 'message' => $uploadDir . ' is not writable.']);
        } else {
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $uploadDir . DIRECTORY_SEPARATOR . $_FILES['file']['name'])) {
                echo json_encode(['success' => true, 'message' => 'Uploaded file ' . $_FILES['file']['name'] . ' to ' . $uploadDir]);
            } else {
                echo json_encode(['success' => true, 'message' => 'Unknown error.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to upload files here ' . $uploadDir]);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_file_manager_file_upload', 'mp_ssv_ajax_file_manager_file_upload');
#endregion

#region Create Folder
function mp_ssv_ajax_file_manager_create_folder()
{
    if (!isset($_POST['path']) || !isset($_POST['newFolderName'])) {
        throw new HttpInvalidParamException('The "path" or "newFolderName" parameter isn\'t provided.');
    }
    $createPath = realpath($_POST['path']);
    if (mp_ssv_starts_with($createPath, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('manage_sites')) {
        mkdir($createPath . DIRECTORY_SEPARATOR . $_POST['newFolderName']);
        echo json_encode(['success' => true, 'message' => 'Created new directory ' . $createPath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to create a folder here ' . $createPath]);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_file_manager_create_folder', 'mp_ssv_ajax_file_manager_create_folder');
#endregion

#region Delete Item
function mp_ssv_file_manager_delete_item($dirPath)
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
                mp_ssv_file_manager_delete_item($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
}

function mp_ssv_ajax_file_manager_delete_item()
{
    if (!isset($_POST['path']) || !isset($_POST['item'])) {
        throw new HttpInvalidParamException('The "path" or "item" parameter isn\'t provided.');
    }
    $base       = realpath($_POST['path']);
    $deleteItem = $base . DIRECTORY_SEPARATOR . $_POST['item'];
    if (mp_ssv_starts_with($deleteItem, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('manage_sites')) {
        mp_ssv_file_manager_delete_item($deleteItem);
        echo json_encode(['success' => true, 'message' => 'Deleted ' . $deleteItem]);
    } else {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete ' . $deleteItem]);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_file_manager_delete_item', 'mp_ssv_ajax_file_manager_delete_item');
#endregion

#region Rename Item
function mp_ssv_ajax_file_manager_rename_item()
{
    if (!isset($_POST['path']) || !isset($_POST['oldItemName']) || !isset($_POST['newItemName'])) {
        throw new HttpInvalidParamException('The "path" or "oldItemName" or "newItemName" parameter isn\'t provided.');
    }
    $base        = realpath($_POST['path']);
    $currentItem = $base . DIRECTORY_SEPARATOR . $_POST['oldItemName'];
    $newItem     = $base . DIRECTORY_SEPARATOR . $_POST['newItemName'];
    if (mp_ssv_starts_with($currentItem, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('manage_sites')) {
        if (rename($currentItem, $newItem)) {
            echo json_encode(['success' => true, 'message' => 'Renamed ' . $currentItem . ' to ' . $newItem]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to rename ' . $currentItem]);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_file_manager_rename_item', 'mp_ssv_ajax_file_manager_rename_item');
#endregion

#region Get Shared With
function mp_ssv_ajax_file_manager_get_shared_with()
{
    if (!isset($_POST['path'])) {
        throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
    }
    $path = realpath($_POST['path']);
    if (mp_ssv_starts_with($path, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('manage_sites')) {
        $roles        = array_keys(get_editable_roles());
        $folderAccess = SSV_FileManager::getFolderAccess($path);
        foreach ($roles as $role) {
            if ($role === 'administrator') {
                continue; // Administrator has always access.
            }
            ?>
            <option value="<?= $role ?>" <?= in_array($role, $folderAccess) ? 'selected' : '' ?>>
                <?= $role ?>
            </option>
            <?php
        }
    } else {
        echo json_encode(false);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_file_manager_get_shared_with', 'mp_ssv_ajax_file_manager_get_shared_with');
#endregion

#region Get Shared With Domain
function mp_ssv_ajax_file_manager_get_shared_with_domain()
{
    if (!isset($_POST['path'])) {
        throw new HttpInvalidParamException('The "path" parameter isn\'t provided.');
    }
    $path = realpath($_POST['path']);
    if (mp_ssv_starts_with($path, SSV_FILE_MANAGER_ROOT_FOLDER) || current_user_can('manage_sites')) {
        $domains      = array_column(get_sites(), 'domain');
        $folderAccess = SSV_FileManager::getFolderSiteAccess($path);
        foreach ($domains as $domain) {
            ?>
            <option value="<?= $domain ?>" <?= in_array($domain, $folderAccess) ? 'selected' : '' ?>>
                <?= $domain ?>
            </option>
            <?php
        }
    } else {
        echo json_encode(false);
    }
    wp_die();
}

add_action('wp_ajax_mp_ssv_file_manager_get_shared_with_domain', 'mp_ssv_ajax_file_manager_get_shared_with_domain');
#endregion

#region Add New Site
function mp_ssv_file_manager_new_site_added($blog_id, $user_id, $domain)
{
    /** @var wpdb $wpdb */
    global $wpdb;
    $items = scandir(SSV_FileManager::ROOT_FOLDER);

    if (!in_array($domain, $items)) {
        mkdir(SSV_FileManager::ROOT_FOLDER . DIRECTORY_SEPARATOR . $domain);
    }
    $wpdb->insert(
        SSV_FileManager::TABLE_FOLDER_SITE_RIGHTS,
        [
            'path'    => realpath(SSV_FileManager::ROOT_FOLDER . DIRECTORY_SEPARATOR . $domain),
            'domains' => json_encode([$domain]),
        ]
    );
}

add_action('wpmu_new_blog', 'mp_ssv_file_manager_new_site_added', 3);
#endregion
