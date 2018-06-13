<?php

namespace mp_ssv_file_manager\Options;


use mp_general\base\BaseFunctions;
use mp_general\base\SSV_Global;

abstract class Options
{
    const OPTION_GROUP = 'mp_ssv_file_manager';

    const SECTIONS = [
        'connection',
        'guests',
        'appearance',
    ];

    const OPTIONS = [
        'connection' => [
            'key'       => [
                'id'    => 'mp_ssv_file_manager__connection_key',
                'title' => 'Key',
            ],
            'secret'    => [
                'id'    => 'mp_ssv_file_manager__connection_secret',
                'title' => 'Secret',
            ],
            'endpoint'  => [
                'id'          => 'mp_ssv_file_manager__connection_endpoint',
                'title'       => 'Endpoint',
                'description' => 'By default: https://ams3.digitaloceanspaces.com',
                'default'     => 'https://ams3.digitaloceanspaces.com',
            ],
            'container' => [
                'id'    => 'mp_ssv_file_manager__connection_container',
                'title' => 'Container',
            ],
        ],
        'guests'     => [
            'view'     => [
                'id'          => 'mp_ssv_file_manager__guests_can_view',
                'title'       => 'Can view Files & Folders',
                'description' => 'Enabling this allows guests to view files and folders',
                'type'        => 'boolean',
                'callback'    => 'showCheckbox',
            ],
            'download' => [
                'id'          => 'mp_ssv_file_manager__guests_can_download',
                'title'       => 'Can download Files',
                'description' => 'Enabling this allows guests to download files',
                'type'        => 'boolean',
                'callback'    => 'showCheckbox',
            ],
            'upload'   => [
                'id'          => 'mp_ssv_file_manager__guests_can_upload',
                'title'       => 'Can upload Files & Folders',
                'description' => 'Enabling this allows guests to upload files and folders',
                'type'        => 'boolean',
                'callback'    => 'showCheckbox',
            ],
            'edit'     => [
                'id'          => 'mp_ssv_file_manager__guests_can_edit',
                'title'       => 'Can edit Files',
                'description' => 'Enabling this allows guests to edit files',
                'type'        => 'boolean',
                'callback'    => 'showCheckbox',
            ],
            'delete'   => [
                'id'          => 'mp_ssv_file_manager__guests_can_delete',
                'title'       => 'Can delete Files & Folders',
                'description' => 'Enabling this allows guests to delete files and folders',
                'type'        => 'boolean',
                'callback'    => 'showCheckbox',
            ],
        ],
        'appearance' => [
            'error_classes' => [
                'id'          => 'mp_ssv_file_manager__error_classes',
                'callback'    => 'showTextArea',
                'title'       => 'Error Classes',
                'description' => 'These classes will be added to the error message div',
            ],
            'folder_color'  => [
                'id'       => 'mp_ssv_file_manager__folder_color',
                'callback' => 'showColorPicker',
                'title'    => 'Folder Color',
            ],
            'file_color'    => [
                'id'       => 'mp_ssv_file_manager__file_color',
                'callback' => 'showColorPicker',
                'title'    => 'File Color',
            ],
        ],
    ];

    public static function registerSettings()
    {
        foreach (self::SECTIONS as $section) {
            add_settings_section('mp_ssv_file_manager_' . $section . '_section', ucfirst($section), null, 'ssv_' . $section);

            foreach (self::OPTIONS[$section] as $option) {
                $option += [
                    'description' => '',
                    'type'        => 'string',
                    'callback'    => 'showTextField',
                ];
                add_settings_field($option['id'], $option['title'], [BaseFunctions::class, $option['callback']], 'ssv_' . $section, 'mp_ssv_file_manager_' . $section . '_section', $option);
                register_setting(self::OPTION_GROUP . '_ssv_' . $section, $option['id'], ['type' => $option['type']]);
            }
        }

        // foreach (self::OPTIONS['connection'] as $option) {
        //     add_settings_field($option['id'], $option['title'], [BaseFunctions::class, 'showTextField'], 'ssv_connection', 'ssv_connection_section', $option);
        //     register_setting(self::OPTION_GROUP.'_connection', $option['id'], ['type' => 'string']);
        // }
        //
        // add_settings_section('ssv_guest_section', 'Guests', null, 'ssv_guest');
        //
        // foreach (self::OPTIONS['guests'] as $option) {
        //     add_settings_field($option['id'], $option['title'], [BaseFunctions::class, 'showCheckbox'], 'ssv_guest', 'ssv_guest_section', $option);
        //     register_setting(self::OPTION_GROUP.'_guests', $option['id'], ['type' => 'boolean']);
        // }
        //
        // add_settings_section('ssv_appearance_section', 'Appearance', null, 'ssv_appearance');
        //
        // foreach (self::OPTIONS['appearance'] as $option) {
        //     add_settings_field($option['id'], $option['title'], [BaseFunctions::class, $option['callback']], 'ssv_appearance', 'ssv_appearance_section', $option);
        //     register_setting(self::OPTION_GROUP.'_appearance', $option['id'], ['type' => 'string']);
        // }
    }

    public static function setupNetworkMenu()
    {
        SSV_Global::addMenuItem('File Manager Options', 'File Manager', 'ssv_file_manager', [Options::class, 'testFunction']);
    }

    public static function setupSiteSpecificMenu()
    {
        SSV_Global::addMenuItem('File Manager Options', 'File Manager', 'ssv_file_manager', [Options::class, 'testFunction']);
    }

    public static function testFunction()
    {
        $activeTab = $_REQUEST['tab'] ?? 'ssv_connection';
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="?<?= BaseFunctions::escape(BaseFunctions::getCurrentUrlWithArguments(['tab' => 'ssv_connection']), 'attr') ?>" class="nav-tab <?= $activeTab == 'ssv_connection' ? 'nav-tab-active' : ''; ?>">Connection</a>
            <a href="?<?= BaseFunctions::escape(BaseFunctions::getCurrentUrlWithArguments(['tab' => 'ssv_guests']), 'attr') ?>" class="nav-tab <?= $activeTab == 'ssv_guests' ? 'nav-tab-active' : ''; ?>">Guest</a>
            <a href="?<?= BaseFunctions::escape(BaseFunctions::getCurrentUrlWithArguments(['tab' => 'ssv_appearance']), 'attr') ?>" class="nav-tab <?= $activeTab == 'ssv_appearance' ? 'nav-tab-active' : ''; ?>">Appearance</a>
            <a href="https://moridrin.com/plugins/ssv-file-manager" class="nav-tab">Help</a>
        </h2>
        <div class="wrap">
            <h1>Your Plugin Page Title</h1>
            <!--suppress HtmlUnknownTarget -->
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_GROUP . '_' . $activeTab);
                do_settings_sections($activeTab);
                submit_button();
                ?>
        </div>
        <?php
    }
}

add_action('admin_init', [Options::class, 'registerSettings']);
add_action('network_admin_menu', [Options::class, 'setupNetworkMenu']);
add_action('admin_menu', [Options::class, 'setupSiteSpecificMenu']);
