<?php
/**
 * Plugin Name: SSV File Management
 * Plugin URI: http://moridrin.com/ssv-file-manager
 * Description: This is a plugin to let the members manage files in the frontend of the Sportal.
 * Version: 1.1.6
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */

namespace mp_ssv_file_manager;

use mp_ssv_general\User;

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

require_once 'general/general.php';
require_once 'functions.php';
require_once 'options/options.php';
require_once 'ajax/file-manager.php';

class SSV_FileManager
{
    const PATH = SSV_FILE_MANAGER_PATH;
    const URL = SSV_FILE_MANAGER_URL;

    const TABLE_FOLDER_RIGHTS = SSV_FILE_MANAGER_FOLDER_RIGHTS_TABLE;

    const ROOT_FOLDER = SSV_FILE_MANAGER_ROOT_FOLDER;

    const ADMIN_REFERER_OPTIONS = 'ssv_file_manager__admin_referer_options';

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
        $table = self::TABLE_FOLDER_RIGHTS;
        $wpdb->query("TRUNCATE TABLE $table");
    }

    public static function CLEAN_INSTALL()
    {
        mp_ssv_file_manager_uninstall();
        mp_ssv_file_manager_register_plugin();
    }

    public static function getFolderAccess(string $path = SSV_FILE_MANAGER_ROOT_FOLDER): array
    {
        $path = realpath($path);
        global $wpdb;
        $table_name = SSV_FileManager::TABLE_FOLDER_RIGHTS;
        $sql        = "SELECT roles FROM $table_name WHERE '$path' LIKE CONCAT(path, '%') ORDER BY CHAR_LENGTH(path) DESC";
        $roles      = $wpdb->get_var($sql);
        if ($roles === null) {
            return [];
        } else {
            return json_decode($roles);
        }
    }

    public static function hasFolderAccess(string $path, User $user = null)
    {
        if (!is_dir($path)) {
            return false;
        }
        if (current_user_can('administrator')) {
            return true;
        }
        if ($user === null) {
            $user = User::getCurrent();
        }

        return count(array_intersect($user->roles, self::getFolderAccess($path))) > 0;
    }

    public static function getRootFolders(User $user = null): array
    {
        if (current_user_can('administrator')) {
            return [SSV_FileManager::ROOT_FOLDER];
        }
        if ($user === null) {
            $user = User::getCurrent();
        }
        global $wpdb;
        $table_name       = SSV_FileManager::TABLE_FOLDER_RIGHTS;
        $sqlWithAccess    = "SELECT path FROM $table_name";
        $sqlWithoutAccess = "SELECT path FROM $table_name";
        $roles            = $user->roles;
        if (count($roles)) {
            $role             = array_pop($roles);
            $sqlWithAccess    .= " WHERE JSON_CONTAINS(roles, '\"$role\"')";
            $sqlWithoutAccess .= " WHERE !JSON_CONTAINS(roles, '\"$role\"')";
        }
        foreach ($roles as $role) {
            $sqlWithAccess    .= " OR JSON_CONTAINS(roles, '\"$role\"')";
            $sqlWithoutAccess .= " AND !JSON_CONTAINS(roles, '\"$role\"')";
        }
        $pathsWithAccess    = $wpdb->get_results($sqlWithAccess);
        $pathsWithoutAccess = $wpdb->get_results($sqlWithoutAccess);

        if ($pathsWithAccess === null) {
            $pathsWithAccess = [];
        } else {
            $pathsWithAccess = array_column($pathsWithAccess, 'path');
        }
        if ($pathsWithoutAccess === null) {
            $pathsWithoutAccess = [];
        } else {
            $pathsWithoutAccess = array_column($pathsWithoutAccess, 'path');
        }
        $rootPaths = array_filter(
            $pathsWithAccess,
            function ($path) use ($pathsWithAccess, $pathsWithoutAccess) {
                foreach ($pathsWithAccess as $otherPath) {
                    if ($path !== $otherPath && mp_ssv_starts_with($path, $otherPath)) {
                        foreach ($pathsWithoutAccess as $pathWithoutAccess) {
                            if (mp_ssv_starts_with($path, $pathWithoutAccess) && mp_ssv_starts_with($pathWithoutAccess, $otherPath)) {
                                return true;
                            }
                        }
                        return false;
                    }
                }
                return true;
            }
        );
        return $rootPaths;
    }
}
