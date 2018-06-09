<?php
/**
 * Plugin Name: SSV File Management
 * Plugin URI: http://moridrin.com/ssv-file-manager
 * Description: This is a plugin to let the members manage files in the frontend of the Sportal.
 * Version: 1.1.12
 * Author: Jeroen Berkvens
 * Author URI: http://nl.linkedin.com/in/jberkvens/
 * License: WTFPL
 * License URI: http://www.wtfpl.net/txt/copying/
 */

namespace mp_ssv_file_manager;

if (!defined('ABSPATH')) {
    exit;
}

require_once 'general/general.php';
if (is_admin()) {
    require_once 'Options.php';
} else {
    require_once 'vendor/autoload.php';
    require_once 'SSV_FileManager.php';
    require_once 'shortcodes/file-manager.php';
    require_once 'Ajax.php';
    require_once 'templates/FolderView.php';
}