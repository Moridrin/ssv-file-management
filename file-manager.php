<?php

use mp_ssv_general\SSV_General;

$base = SSV_FILE_MANAGER_ROOT_FOLDER;
$baseArray = explode(DIRECTORY_SEPARATOR, $base);

$current = realpath($base.DIRECTORY_SEPARATOR.(isset($_GET['path']) ? $_GET['path'] : ''));
$currentArray = explode(DIRECTORY_SEPARATOR, $current);

$foldersUp = count(array_diff_assoc($baseArray, $currentArray));

$relativePath = implode(DIRECTORY_SEPARATOR, array_fill(0, $foldersUp, ('..')));
$relativePath = $relativePath === '' ? '' : $relativePath.DIRECTORY_SEPARATOR;
$relativePath .= implode(DIRECTORY_SEPARATOR, array_diff_assoc($currentArray, $baseArray));
$relativePathArray = explode(DIRECTORY_SEPARATOR, $relativePath);

$realPath = realpath($base.DIRECTORY_SEPARATOR.$relativePath);
$realPathArray = explode(DIRECTORY_SEPARATOR, $realPath);

if (!mp_ssv_starts_with($realPath, $base) && !current_user_can('administrator')) {
    ?><div class="notification error">You are not allowed to view this folder.</div><?php
} else {
    $currentFolder = end($realPathArray);
    ?>
    <div class="row">
        <div class="element column-2">
            <h1 style="display: inline-block"><?=$currentFolder?></h1>
            <button id="addFolder" style="float: right" data-path="<?=$relativePath?>">Add Folder</button><br/>
            <table class="item-list" cellspacing="0" cellpadding="0">
                <col width="auto"/>
                <col width="36px"/>
                <?php
                $items = array_diff(scandir($realPath), ['.', '..']);
                usort($items, function ($a, $b) use ($realPath) {
                    $aIsDir = is_dir($realPath.DIRECTORY_SEPARATOR.$a);
                    $bIsDir = is_dir($realPath.DIRECTORY_SEPARATOR.$b);
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
                if ($realPath !== realpath($base)) {
                    $parentPathArray = $relativePathArray;
                    if ($foldersUp > 0) {
                        $folderUp = $relativePath.DIRECTORY_SEPARATOR.'..';
                    } else {
                        array_pop($parentPathArray);
                        $folderUp = implode(DIRECTORY_SEPARATOR, $parentPathArray);
                    }
                    ?>
                    <tr data-location="<?= $folderUp ?>" class="dbclick-navigate">
                        <td class="item-name" title="Parent Folder">
                            <a href="?path=<?= $folderUp ?>">
                                <svg><use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/folder-up.svg#folder-up"></use></svg>
                                ..
                            </a>
                        </td>
                        <td class="item-actions-unavailable">
                            <svg style="width: 16px; height: 35px;"><use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/sprite_icons.svg#more"></use></svg>
                        </td>
                    </tr>
                    <?php
                }
                foreach ($items as $item) {
                    if (is_dir($realPath.DIRECTORY_SEPARATOR.$item)) {
                        ?>
                        <tr data-location="<?= $relativePath ?>" data-item="<?= $item ?>" class="selectable dbclick-navigate">
                            <td class="item-name" title="<?= $item ?>">
                                <a href="?path=<?= $relativePath.DIRECTORY_SEPARATOR.$item ?>">
                                    <svg><use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/folder.svg#folder"></use></svg>
                                    <?=$item?>
                                </a>
                            </td>
                            <td class="item-actions">
                                <svg style="width: 16px; height: 35px;"><use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/sprite_icons.svg#more"></use></svg>
                            </td>
                        </tr>
                        <?php
                    } else {
                        ?>
                        <tr class="selectable" data-location="<?= $relativePath ?>" data-item="<?= $item ?>">
                            <td class="item-name" title="<?= $item ?>">
                                <a href="#">
                                    <svg><use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/fileapi-upload-button.svg#fileapi-upload-button"></use></svg>
                                    <?=$item?>
                                </a>
                            </td>
                            <td class="item-actions">
                                <svg style="width: 16px; height: 35px;"><use xlink:href="<?=plugins_url()?>/ssv-file-manager/images/sprite_icons.svg#more"></use></svg>
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
                <input name="action" type="hidden" value="mp_ssv_ajax_file_upload" />
                <input name="path" type="hidden" value="<?= implode('/', $path) ?>" />
                <div class="fallback">
                      <input name="file" type="file" multiple />
                  </div>
            </form>
        </div>
    </div>
    <?php
}