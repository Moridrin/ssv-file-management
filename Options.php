<?php

namespace mp_ssv_file_manager\Options;


use mp_general\base\BaseFunctions;
use mp_general\base\SSV_Global;

abstract class Options
{
    const OPTION_GROUP = 'mp_ssv_file_manager';

    const OPTIONS = [
        'view'     => [
            'id'          => 'mp_ssv_file_manager__guests_can_view',
            'title'       => 'Can view Files & Folders',
            'description' => 'Enabling this allows guests to view files and folders',
        ],
        'download' => [
            'id'          => 'mp_ssv_file_manager__guests_can_download',
            'title'       => 'Can download Files',
            'description' => 'Enabling this allows guests to download files',
        ],
        'upload'   => [
            'id'          => 'mp_ssv_file_manager__guests_can_upload',
            'title'       => 'Can upload Files & Folders',
            'description' => 'Enabling this allows guests to upload files and folders',
        ],
        'edit'     => [
            'id'          => 'mp_ssv_file_manager__guests_can_edit',
            'title'       => 'Can edit Files',
            'description' => 'Enabling this allows guests to edit files',
        ],
        'delete'   => [
            'id'          => 'mp_ssv_file_manager__guests_can_delete',
            'title'       => 'Can delete Files & Folders',
            'description' => 'Enabling this allows guests to delete files and folders',
        ],
    ];

    public static function registerSettings()
    {
        add_settings_section('ssv_guest_section', 'Guests', null, 'ssv_settings');

        foreach (self::OPTIONS as $option) {
            add_settings_field($option['id'], $option['title'], [BaseFunctions::class, 'showCheckbox'], 'ssv_settings', 'ssv_guest_section', ['id' => $option['id'], 'description' => $option['description']]);
            register_setting(self::OPTION_GROUP, $option['id'], ['type' => 'boolean']);
        }
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
        ?>
        <div class="wrap">
            <h1>Your Plugin Page Title</h1>
            <!--suppress HtmlUnknownTarget -->
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections('ssv_settings');
                submit_button();
                ?>
        </div>
        <?php
    }
}

add_action('admin_init', [Options::class, 'registerSettings']);
add_action('network_admin_menu', [Options::class, 'setupNetworkMenu']);
add_action('admin_menu', [Options::class, 'setupSiteSpecificMenu']);
