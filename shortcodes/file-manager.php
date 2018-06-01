<?php

function mp_ssv_file_manager_shortcode($atts)
{
    wp_enqueue_style('ssv_dropzone');
    wp_enqueue_style('ssv_context_menu');
    wp_enqueue_style('ssv_frontend_file_manager_css');
    wp_enqueue_script('ssv_dropzone');
    wp_enqueue_script('ssv_context_menu');
    wp_enqueue_script('ssv_frontend_file_manager_js');

    ob_start();
    ?>
    <div id="fileManager"></div>
    <script>
        jQuery(document).ready(function () {
            fileManager.init('fileManager', '/', true);
        });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('ssv_file_manager', 'mp_ssv_file_manager_shortcode');
