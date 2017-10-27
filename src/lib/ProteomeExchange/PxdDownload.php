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
namespace pgb_liv\pxdsync\ProteomeExchange;

use pgb_liv\php_ms\Reader\PxdInfo;

/**
 *
 * @author Andrew Collins
 */
class PxdDownload
{

    const REMOTE_SIZE = 'remote_size';

    const LOCAL_SIZE = 'local_size';

    private $info;

    public function __construct(PxdInfo $info)
    {
        $this->info = $info;
    }

    public function downloadAll()
    {
        if (! is_dir(DATA_PATH_PREFIX . '/' . $this->info->getIdString())) {
            mkdir(DATA_PATH_PREFIX . '/' . $this->info->getIdString());
        }
        
        $ftpStream = ftp_connect('ftp.pride.ebi.ac.uk', 21);
        if (! $ftpStream) {
            die('Remote FTP Server Down');
        }
        
        $loginResult = ftp_login($ftpStream, 'anonymous', 'example@example.com');
        if (! $loginResult) {
            die('Unable to login');
        }
        
        foreach ($this->info->getDatasetFileList() as $file) {
            $remotePath = str_replace('ftp://ftp.pride.ebi.ac.uk', '', $file['location']);
            $localPath = DATA_PATH_PREFIX . '/' . $this->info->getIdString() . '/' . $file['name'];
            
            $raw = ftp_raw($ftpStream, 'SIZE ' . $remotePath);
            if (is_null($raw)) {
                throw new \UnexpectedValueException(
                    'SIZE returned NULL for "(' . gettype($remotePath) . ')' . $remotePath . '"');
            }
            
            $file[self::REMOTE_SIZE] = substr($raw[0], 4);
            $file[self::LOCAL_SIZE] = 0;
            
            if (file_exists($localPath)) {
                $file[self::LOCAL_SIZE] = filesize($localPath);
            }
            
            if ($file[self::LOCAL_SIZE] == $file[self::REMOTE_SIZE]) {
                echo 'Skipping ' . $file['name'] . ' (' . $file[self::LOCAL_SIZE] . '/' . $file[self::REMOTE_SIZE] . ')' .
                     PHP_EOL;
                continue;
            }
            
            $attempts = 0;
            while ($file[self::LOCAL_SIZE] != $file[self::REMOTE_SIZE]) {
                echo 'Downloading ' . $file['name'] . ' (' . $file[self::LOCAL_SIZE] . '/' . $file[self::REMOTE_SIZE] .
                     ')...';
                if ($attempts >= 3) {
                    break;
                }
                
                $start = microtime(true);
                $file[self::LOCAL_SIZE] = $this->downloadFile($file['location'],
                    DATA_PATH_PREFIX . '/' . $this->info->getIdString() . '/' . $file['name']);
                
                $duration = microtime(true) - $start;
                $speed = (($file[self::LOCAL_SIZE] / 1024) / 1024) / $duration;
                echo ' Done (' . round($speed, 3) . 'MB/s)' . PHP_EOL;
                $attempts ++;
            }
        }
        
        ftp_close($ftpStream);
    }

    public function downloadFile($src, $dest)
    {
        if (file_exists($dest)) {
            return;
        }
        
        $reader = fopen($src, 'r');
        
        $destPart = $dest . '.part';
        $writer = fopen($destPart, 'w');
        
        if ($writer === false)
        {
            return 0;
        }
        
        while (! feof($reader)) {
            $buffer = fread($reader, 1048576);
            if (! $buffer) {
                die('read failed ?');
            }
            
            if (! fwrite($writer, $buffer)) {
                
                die('write failed?');
            }
        }
        
        fclose($reader);
        fclose($writer);
        
        rename($destPart, $dest);
        
        return filesize($dest);
    }
}
