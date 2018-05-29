<?php

use mp_general\base\SSV_Global;

if (!defined('ABSPATH')) {
    exit;
}

function ssv_add_ssv_file_manager_options()
{
    SSV_Global::addMenuItem('File Manager Options', 'File Manager', 'manage_options', 'ssv-file-manager-settings', 'ssv_file_manager_options_page_content');
}

function ssv_file_manager_options_page_content()
{
    $activeTab = $_GET['tab'] ?? 'general';
    ?>
    <div class="wrap">
        <h1>File Manager Options</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=<?=esc_html($_GET['page'])?>&tab=general" class="nav-tab <?=$activeTab === 'general' ? 'nav-tab-active' : ''?>">General</a>
            <a href="http://bosso.nl/plugins/ssv-file-manager/" target="_blank" class="nav-tab">
                Help <!--suppress HtmlUnknownTarget -->
                <img src="<?=esc_url(SSV_Global::URL)?>/images/link-new-tab-small.png" width="14" style="vertical-align:middle">
            </a>
        </h2>
        <?php
        /** @noinspection PhpIncludeInspection */
        require_once $activeTab.'.php';
        ?>
    </div>
    <?php
}

add_action('admin_menu', 'ssv_add_ssv_file_manager_options');

function ssv_add_ssv_file_manager_super_admin_options()
{
    add_submenu_page('ssv_settings', 'File Manager Options', 'File Manager', 'manage_options', 'ssv-file-manager-settings', 'ssv_file_manager_super_admin_options_page_content');
}

function ssv_file_manager_super_admin_options_page_content()
{
    $activeTab = $_GET['tab'] ?? 'general';
    ?>
    <div class="wrap">
        <h1>File Manager Options</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=<?=esc_html($_GET['page'])?>&tab=general" class="nav-tab <?=$activeTab === 'general' ? 'nav-tab-active' : ''?>">General</a>
            <a href="http://bosso.nl/plugins/ssv-file-manager/" target="_blank" class="nav-tab">
                Help <!--suppress HtmlUnknownTarget -->
                <img src="<?=esc_url(SSV_Global::URL)?>/images/link-new-tab-small.png" width="14" style="vertical-align:middle">
            </a>
        </h2>
        <?php
        /** @noinspection PhpIncludeInspection */
        require_once 'network-admin'.DIRECTORY_SEPARATOR.$activeTab.'.php';
        ?>
    </div>
    <?php
}

add_action('network_admin_menu', 'ssv_add_ssv_file_manager_super_admin_options', 9);
