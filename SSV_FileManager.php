<?php

namespace mp_ssv_file_manager;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

if (!defined('ABSPATH')) {
    exit;
}

define('SSV_FILE_MANAGER_PATH', plugin_dir_path(__FILE__));
define('SSV_FILE_MANAGER_URL', plugins_url().'/ssv-file-manager/');

class SSV_FileManager
{
    const PATH = SSV_FILE_MANAGER_PATH;
    const URL  = SSV_FILE_MANAGER_URL;

    const ADMIN_REFERER_OPTIONS = 'ssv_file_manager__admin_referer_options';

    public static function setup()
    {

    }

    public static function deactivate()
    {

    }

    public static function CLEAN_INSTALL()
    {
        self::deactivate();
        self::setup();
    }

    public static function connect(): Filesystem
    {
        $dos_key       = 'YDLOAAYXNUNET3DTV6D2';
        $dos_secret    = 'a3tpq+qh1n2Noo9tGJ0yXpphrCDAaatkQ/uj71g85m4';
        $dos_endpoint  = 'https://ams3.digitaloceanspaces.com';
        $dos_container = 'essf-social';

        $client = S3Client::factory(
            [
                'credentials' => [
                    'key'    => $dos_key,
                    'secret' => $dos_secret,
                ],
                'endpoint'    => $dos_endpoint,
                'region'      => '',
                'version'     => 'latest',
            ]
        );

        $connection = new AwsS3Adapter($client, $dos_container);
        $filesystem = new Filesystem($connection);

        return $filesystem;
    }

    public static function registerStyles()
    {
        wp_register_style('ssv_dropzone', plugins_url().'/ssv-file-manager/css/dropzone.css');
        wp_register_style('ssv_context_menu', plugins_url().'/ssv-file-manager/css/jquery.contextMenu.css');
        wp_register_style('ssv_frontend_file_manager_css', plugins_url().'/ssv-file-manager/css/ssv-file-manager.css');

        wp_enqueue_style('fa_icons', plugins_url().'/ssv-file-manager/css/fontawesome-all.css');
    }

    public static function registerScripts()
    {
        wp_register_script('ssv_dropzone', plugins_url().'/ssv-file-manager/js/dropzone.js', ['jquery']);
        wp_register_script('ssv_context_menu', plugins_url().'/ssv-file-manager/js/jquery.contextMenu.js', ['jquery']);
        wp_register_script('ssv_frontend_file_manager_js', plugins_url().'/ssv-file-manager/js/ssv-file-manager.js', ['jquery']);
        wp_localize_script(
            'ssv_frontend_file_manager_js',
            'mp_ssv_file_manager_params',
            [
                'urls'    => [
                    'plugins'  => plugins_url(),
                    'ajax'     => admin_url('admin-ajax.php'),
                    'base'     => 'https://essf-social.ams3.digitaloceanspaces.com/',
                    'basePath' => ABSPATH,
                ],
                'actions' => [
                    'add_folder' => 'ssv_file_manager_add_folder',
                    'delete'     => 'ssv_file_manager_delete',
                ],
            ]
        );
    }
}

register_activation_hook(SSV_FILE_MANAGER_PATH.'ssv-file-manager.php', [SSV_FileManager::class, 'setup']);
register_deactivation_hook(SSV_FILE_MANAGER_PATH.'ssv-file-manager.php', [SSV_FileManager::class, 'deactivate']);
add_action('wp_enqueue_scripts', [SSV_FileManager::class, 'registerStyles']);
add_action('wp_enqueue_scripts', [SSV_FileManager::class, 'registerScripts']);
