<?php
/**
 * Plugin Name: SSV File Management
 * Plugin URI: http://moridrin.com/ssv-file-manager
 * Description: This is a plugin to let the members manage files in the frontend.
 * Version: 1.0.0
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
require_once 'Ajax.php';
require_once 'SSV_FileManager.php';
require_once 'vendor/autoload.php';
require_once 'templates/FolderView.php';
require_once 'Options.php';
require_once 'shortcodes/file-manager.php';
