<?php
use mp_ssv_general\SSV_General;

if (!defined('ABSPATH')) {
    exit;
}

function ssv_add_ssv_file_manager_options()
{
    add_submenu_page('ssv_settings', 'File Manager Options', 'File Manager', 'manage_options', 'ssv-file-manager-settings', 'ssv_file_manager_options_page_content');
    add_submenu_page('ssc_settings', 'File Manager Options', 'File Manager', 'manage_options', 'ssv-file-manager-settings', 'ssv_file_manager_options_page_content');
}

function ssv_file_manager_options_page_content()
{
    $active_tab = "general";
    if (isset($_GET['tab'])) {
        $active_tab = $_GET['tab'];
    }
    ?>
    <div class="wrap">
        <h1>File Manager Options</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=<?= esc_html($_GET['page']) ?>&tab=general" class="nav-tab <?= SSV_General::currentNavTab($active_tab, 'general') ?>">General</a>
            <a href="http://bosso.nl/plugins/ssv-file-manager/" target="_blank" class="nav-tab">
                Help <!--suppress HtmlUnknownTarget -->
                <img src="<?= esc_url(SSV_General::URL) ?>/images/link-new-tab-small.png" width="14" style="vertical-align:middle">
            </a>
        </h2>
        <?php
        /** @noinspection PhpIncludeInspection */
        require_once $active_tab . '.php';
        ?>
    </div>
    <?php
}

add_action('admin_menu', 'ssv_add_ssv_file_manager_options');

function ssv_file_manager_general_options_page_content()
{
    ?><h2><a href="?page=<?= __FILE__ ?>">File Manager Options</a></h2><?php
}

add_action(SSV_General::HOOK_GENERAL_OPTIONS_PAGE_CONTENT, 'ssv_file_manager_general_options_page_content');
