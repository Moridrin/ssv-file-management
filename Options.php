<?php

namespace mp_ssv_file_manager\Options;


use mp_general\base\BaseFunctions;
use mp_general\base\SSV_Global;

abstract class Options
{
    const OPTION_GROUP = 'ssv_test_options';

    public static function registerSettings()
    {
        add_settings_field('guests_can_view', 'Can view', [BaseFunctions::class, 'showCheckbox'], 'ssv_settings', 'ssv_guest_section', ['id' => 'guests_can_view']);
        add_settings_field('guests_can_download', 'Can download', [BaseFunctions::class, 'showCheckbox'], 'ssv_settings', 'ssv_guest_section', ['id' => 'guests_can_download']);
        add_settings_field('guests_can_upload', 'Can upload', [BaseFunctions::class, 'showCheckbox'], 'ssv_settings', 'ssv_guest_section', ['id' => 'guests_can_upload']);
        add_settings_field('guests_can_edit', 'Can edit', [BaseFunctions::class, 'showCheckbox'], 'ssv_settings', 'ssv_guest_section', ['id' => 'guests_can_edit']);
        add_settings_field('guests_can_delete', 'Can delete', [BaseFunctions::class, 'showCheckbox'], 'ssv_settings', 'ssv_guest_section', ['id' => 'guests_can_delete']);

        add_settings_section('ssv_guest_section', 'Guests', null, 'ssv_settings');

        register_setting(self::OPTION_GROUP, 'guests_can_view');
        register_setting(self::OPTION_GROUP, 'guests_can_download');
        register_setting(self::OPTION_GROUP, 'guests_can_upload');
        register_setting(self::OPTION_GROUP, 'guests_can_edit');
        register_setting(self::OPTION_GROUP, 'guests_can_delete');
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
