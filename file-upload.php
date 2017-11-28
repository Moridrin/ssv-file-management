<?php
$path = isset($_GET['path']) ? $_GET['path'] : 'SSC File Manager';
if ($_POST) {
    echo 'post';
} else {
    $path = explode(';', $path);
    $currentFolder = end($path);
    ?>
    <h1>/* UNDER CONSTRUCTION *\</h1>
    <div class="row">
        <div class="element column-2">
            <h1 style="display: inline-block"><?=$currentFolder?></h1>
            <button id="addFolder" style="float: right" data-path="<?=implode(DIRECTORY_SEPARATOR, $path)?>">Add Folder</button><br/>
            <table class="item-list" cellspacing="0" cellpadding="0">
                <col width="auto"/>
                <col width="36px"/>
                <?php
                $dir = ABSPATH.'wp-content/uploads/'.implode('/', $path);
                $items = array_diff(scandir($dir), ['.', '..']);
                usort($items, function ($a, $b) use ($dir) {
                    $aIsDir = is_dir($dir.DIRECTORY_SEPARATOR.$a);
                    $bIsDir = is_dir($dir.DIRECTORY_SEPARATOR.$b);
                    if (($aIsDir && $bIsDir) || (!$aIsDir && !$bIsDir)) {
                        return strcmp($a, $b);
                    } elseif ($aIsDir) {
                        return -1;
                    } elseif ($bIsDir) {
                        return 1;
                    } else {
                        return 0;
                    }
                });
                if ($currentFolder !== 'SSC File Manager') {
                    $parentPath = $path;
                    array_pop($parentPath);
                    ?>
                    <tr data-folder-location="?path=<?= implode(';', $parentPath) ?>" class="selectable">
                        <td class="item-name" title="<?= $item ?>">
                            <a href="?path=<?= implode(';', $parentPath) ?>">
                                <svg><use xlink:href="<?=plugins_url()?>/ssv-file-management/images/folder-up.svg#folder-up"></use></svg>
                                ..
                            </a>
                        </td>
                        <td class="item-actions">
                            <svg style="width: 16px; height: 35px;"><use xlink:href="<?=plugins_url()?>/ssv-file-management/images/sprite_icons.svg#more"></use></svg>
                        </td>
                    </tr>
                    <?php
                }
                foreach ($items as $item) {
                    if (is_dir($dir.DIRECTORY_SEPARATOR.$item)) {
                        ?>
                        <tr data-folder-location="?path=<?= implode(';', $path) . ';' . $item ?>" class="selectable">
                            <td class="item-name" title="<?= $item ?>">
                                <a href="?path=<?= implode(';', $path) . ';' . $item ?>">
                                    <svg><use xlink:href="<?=plugins_url()?>/ssv-file-management/images/folder.svg#folder"></use></svg>
                                    <?=$item?>
                                </a>
                            </td>
                            <td class="item-actions">
                                <svg style="width: 16px; height: 35px;"><use xlink:href="<?=plugins_url()?>/ssv-file-management/images/sprite_icons.svg#more"></use></svg>
                            </td>
                        </tr>
                        <?php
                    } else {
                        ?>
                        <tr class="selectable">
                            <td class="item-name" title="<?= $item ?>">
                                <a href="#">
                                    <svg><use xlink:href="<?=plugins_url()?>/ssv-file-management/images/fileapi-upload-button.svg#fileapi-upload-button"></use></svg>
                                    <?=$item?>
                                </a>
                            </td>
                            <td class="item-actions">
                                <svg style="width: 16px; height: 35px;"><use xlink:href="<?=plugins_url()?>/ssv-file-management/images/sprite_icons.svg#more"></use></svg>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>
        <div class="element column-3">
            <h1>Add Items</h1>
            <form action="<?= admin_url('admin-ajax.php') ?>" class="dropzone">
                  <div class="fallback">
                      <input name="action" type="hidden" value="mp_ssv_ajax_file_upload" />
                      <input name="file" type="file" multiple />
                  </div>
            </form>
        </div>
    </div>
    <?php
}