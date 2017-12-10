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

namespace mp_ssv_file_manager;

use mp_ssv_general\SSV_General;

if (!defined('ABSPATH')) {
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

global $wpdb;
define('SSV_FILE_MANAGER_PATH', plugin_dir_path(__FILE__));
define('SSV_FILE_MANAGER_URL', plugins_url() . '/ssv-file-manager/');
define('SSV_FILE_MANAGER_FOLDER_RIGHTS_TABLE', $wpdb->prefix . "ssv_file_manager_folder_rights");
define('SSV_FILE_MANAGER_ROOT_FOLDER', realpath(ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'SSV File Manager'));

#region Require Once
require_once 'general/general.php';
require_once 'functions.php';
require_once 'options/options.php';
require_once 'ajax/file-manager.php';
#endregion

#region SSV_Events class
class SSV_FileManager
{
    #region Constants
    const PATH = SSV_FILE_MANAGER_PATH;
    const URL = SSV_FILE_MANAGER_URL;

    const TABLE_FOLDER_RIGHTS = SSV_FILE_MANAGER_FOLDER_RIGHTS_TABLE;

    const ROOT_FOLDER = SSV_FILE_MANAGER_ROOT_FOLDER;

    const ADMIN_REFERER_OPTIONS = 'ssv_file_manager__admin_referer_options';
    #endregion

    #region resetOptions()
    /**
     * This function sets all the options for this plugin back to their default value
     */
    public static function resetOptions()
    {
        self::resetGeneralOptions();
    }

    /**
     * This function sets all the options on the General Tab back to their default value
     */
    public static function resetGeneralOptions()
    {
        global $wpdb;
        $wpdb->delete(
            self::TABLE_FOLDER_RIGHTS,
            array(
                '1' => 1,
            )
        );
    }

    public static function CLEAN_INSTALL()
    {
        mp_ssv_file_manager_uninstall();
        mp_ssv_file_manager_register_plugin();
    }
    #endregion

    #region
    public static function getFolderAccess(string $path): array
    {
        $path = realpath($path);
        global $wpdb;
        $table_name = SSV_FileManager::TABLE_FOLDER_RIGHTS;
        $sql = "SELECT roles FROM $table_name WHERE '$path' LIKE CONCAT(path, '%') ORDER BY CHAR_LENGTH(path) DESC";
        $roles = $wpdb->get_var($sql);
        if ($roles === null) {
            return [];
        } else {
            return json_decode($roles);
        }
    }
    #endregion
}
#endregion
