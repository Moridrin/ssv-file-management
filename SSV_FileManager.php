<?php

namespace mp_ssv_file_manager;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use mp_general\base\SSV_Global;
use mp_ssv_file_manager\Options\Options;

if (!defined('ABSPATH')) {
    exit;
}

define('SSV_FILE_MANAGER_PATH', plugin_dir_path(__FILE__));
define('SSV_FILE_MANAGER_URL', plugins_url() . '/ssv-file-manager/');

class SSV_FileManager
{
    public const PATH        = SSV_FILE_MANAGER_PATH;
    public const URL         = SSV_FILE_MANAGER_URL;
    public const ROOT_FOLDER = SSV_FILE_MANAGER_ROOT_FOLDER;

    const ADMIN_REFERER_OPTIONS = 'ssv_file_manager__admin_referer_options';

    const RIGHTS = [
        'view'     => 'mp_ssv_file_manager__can_view',
        'download' => 'mp_ssv_file_manager__can_download',
        'upload'   => 'mp_ssv_file_manager__can_upload',
        'edit'     => 'mp_ssv_file_manager__can_edit',
        'delete'   => 'mp_ssv_file_manager__can_delete',
    ];

    public static function setup()
    {
        $role = get_role('administrator');
        foreach (self::RIGHTS as $right) {
            $role->add_cap($right);
        }
        update_option(Options::OPTIONS['appearance']['error_classes']['id'], 'notice notice-error error');
        update_option(Options::OPTIONS['appearance']['folder_color']['id'], '#FFB300');
        update_option(Options::OPTIONS['appearance']['file_color']['id'], '#057D9F');
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
        $client = S3Client::factory(
            [
                'credentials' => [
                    'key'    => get_option(Options::OPTIONS['connection']['key']['id']),
                    'secret' => get_option(Options::OPTIONS['connection']['secret']['id']),
                ],
                'endpoint'    => get_option(Options::OPTIONS['connection']['endpoint']['id']),
                'region'      => '',
                'version'     => 'latest',
            ]
        );

        $connection = new AwsS3Adapter($client, get_option(Options::OPTIONS['connection']['container']['id']));
        $filesystem = new Filesystem($connection);

        return $filesystem;
    }

    public static function registerStyles()
    {
        wp_register_style('ssv_context_menu', plugins_url() . '/ssv-file-manager/css/jquery.contextMenu.css');
        wp_register_style('ssv_frontend_file_manager', plugins_url() . '/ssv-file-manager/css/ssv-file-manager.css');

        wp_enqueue_style('fa_icons', plugins_url() . '/ssv-file-manager/css/fontawesome-all.css');
    }

    public static function registerScripts()
    {
        $rights = [];
        foreach (self::RIGHTS as $key => $right) {
            $rights[$key] = current_user_can($right) || (!is_user_logged_in() && get_option(Options::OPTIONS['guests'][$key]['id']));
        }
        wp_register_script('ssv_context_menu', plugins_url() . '/ssv-file-manager/js/jquery.contextMenu.js', ['jquery']);
        wp_register_script('ssv_frontend_file_manager', plugins_url() . '/ssv-file-manager/js/ssv-file-manager.js', ['jquery']);
        wp_localize_script(
            'ssv_frontend_file_manager',
            'mp_ssv_file_manager_params',
            [
                'urls'    => [
                    'plugins'  => plugins_url(),
                    'ajax'     => admin_url('admin-ajax.php'),
                    'base'     => 'https://essf-social.ams3.digitaloceanspaces.com/',
                    'basePath' => ABSPATH,
                ],
                'rights'  => $rights,
                'actions' => Ajax::$callables,
            ]
        );

        wp_enqueue_script('mp-ssv-general-functions', SSV_Global::URL . '/js/general-functions.js', ['jquery']);
    }
}

register_activation_hook(SSV_FILE_MANAGER_PATH . 'ssv-file-manager.php', [SSV_FileManager::class, 'setup']);
register_deactivation_hook(SSV_FILE_MANAGER_PATH . 'ssv-file-manager.php', [SSV_FileManager::class, 'deactivate']);
add_action('wp_enqueue_scripts', [SSV_FileManager::class, 'registerStyles']);
add_action('wp_enqueue_scripts', [SSV_FileManager::class, 'registerScripts']);
