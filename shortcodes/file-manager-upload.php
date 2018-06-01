<?php

function mp_ssv_file_manager_upload_shortcode($atts)
{
    wp_enqueue_style('ssv_dropzone');
    wp_enqueue_style('ssv_context_menu');
    wp_enqueue_style('ssv_frontend_file_manager_css');
    wp_enqueue_script('ssv_dropzone');
    wp_enqueue_script('ssv_context_menu');
    wp_enqueue_script('ssv_frontend_file_manager_js');

    ob_start();
    ?>
    <h1>Add Items</h1>
    <form id="uploadFile" action="<?=admin_url('admin-ajax.php')?>" class="dropzone">
        <input name="action" type="hidden" value="mp_ssv_file_manager_file_upload"/>
        <input id="uploadPath" name="path" type="hidden" value="<?=DIRECTORY_SEPARATOR?>"/>
        <div class="fallback">
            <input name="file" type="file" multiple/>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('ssv_file_manager_upload', 'mp_ssv_file_manager_upload_shortcode');
