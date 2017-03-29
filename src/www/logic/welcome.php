<?php
$currentSync = scandir(DATA_PATH_PREFIX);

foreach ($currentSync as $key => $value) {
    if ($value == '.' || $value == '..' || $value == '.pxdsync') {
        unset($currentSync[$key]);
    }
}

$smarty->assign('currentSync', $currentSync);
