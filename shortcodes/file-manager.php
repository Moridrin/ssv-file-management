<?php

use mp_general\base\BaseFunctions;

function mp_ssv_file_manager_shortcode($attributes)
{
    wp_enqueue_style('ssv_context_menu');
    wp_enqueue_style('ssv_frontend_file_manager');

    wp_enqueue_script('ssv_context_menu');
    wp_enqueue_script('ssv_frontend_file_manager');
    wp_enqueue_script('ssv_general_functions');

    if (!is_array($attributes)) {
        $attributes = [];
    }
    $attributes    += [
        'path'          => DIRECTORY_SEPARATOR,
        'maxUploadSize' => BaseFunctions::getMaxUploadSize(),
        'allowEdit'     => current_user_can('manage_files'),
    ];
    $currentPath   = $_REQUEST['path'] ?? $attributes['path'];
    ob_start();
    ?>
    <div id="messagesContainer"></div>
    <div id="fileManager">
        <div id="itemListContainer" class="loading" style="min-height: 100px;">
            <div id="itemListLoader" class="cssLoader"></div>
        </div>
    </div>
    <?php
    ?>
    <script>
        jQuery(document).ready(function () {
            FileManager.init(
                'fileManager',
                '<?= BaseFunctions::escape($currentPath, 'js') ?>',
                '<?= BaseFunctions::escape($attributes['allowEdit'], 'js') ?>',
                '<?= BaseFunctions::escape($attributes['maxUploadSize'], 'js') ?>'
            );
        });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('ssv_file_manager', 'mp_ssv_file_manager_shortcode');
