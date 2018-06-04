<?php

use mp_general\base\BaseFunctions;

function mp_ssv_file_manager_shortcode($attributes)
{
    wp_enqueue_style('ssv_dropzone');
    wp_enqueue_style('ssv_context_menu');
    wp_enqueue_style('ssv_frontend_file_manager');

    wp_enqueue_script('ssv_dropzone');
    wp_enqueue_script('ssv_context_menu');
    wp_enqueue_script('ssv_frontend_file_manager');
    wp_enqueue_script('ssv_general_functions');

    ob_start();
    $path = $_REQUEST['path'] ?? DIRECTORY_SEPARATOR;
    ?>
    <div id="fileManager"></div>
    <script>
        jQuery(document).ready(function () {
            fileManager.init('fileManager', '<?= BaseFunctions::escape($path, 'js') ?>', <?= json_encode(current_user_can('manage_files')) ?>, <?= json_encode($attributes) ?>);
        });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('ssv_file_manager', 'mp_ssv_file_manager_shortcode');
