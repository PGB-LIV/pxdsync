<?php
/**
 * Copyright 2016 University of Liverpool
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/**
 *
 * @author Andrew Collins
 */
namespace pgb_liv\pxdsync\scripts;

use pgb_liv\php_ms\Reader\PxdInfo;
use pgb_liv\pxdsync\ProteomeExchange\PxdDownload;

error_reporting(E_ALL);
ini_set('display_errors', true);

require_once '../conf/config.php';
require_once '../conf/autoload.php';

$lockFile = DATA_PATH_PREFIX . '/.pxdsync/.lock';
if (! file_exists($lockFile)) {
    touch($lockFile);
}

$lock = fopen($lockFile, 'r+');

if (! flock($lock, LOCK_EX | LOCK_NB)) {
    die('Process Running. Terminating.');
}

$terminate = false;
while (! $terminate) {
    $jobQueue = array_diff(scandir(DATA_PATH_PREFIX . '/.pxdsync/.queue'), array(
        '..',
        '.'
    ));
    
    foreach ($jobQueue as $job) {
        echo 'Downloading ' . $job . PHP_EOL;
        
        $info = new PxdInfo($job);
        $downloader = new PxdDownload($info);
        $downloader->downloadAll();
        
        unlink(DATA_PATH_PREFIX . '/.pxdsync/.queue/' . $job);
    }
    
    sleep(60);
}

fclose($lock);
