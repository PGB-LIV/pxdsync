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
        if ($ftpStream == false) {
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
            $file['remote_size'] = substr($raw[0], 4);
            $file['local_size'] = 0;
            
            if (file_exists($localPath)) {
                $file['local_size'] = filesize($localPath);
            }
            
            $attempts = 0;
            while ($file['local_size'] != $file['remote_size']) {
                if ($attempts >= 3) {
                    break;
                }
                
                $file['local_size'] = $this->downloadFile($file['location'], 
                    DATA_PATH_PREFIX . '/' . $this->info->getIdString() . '/' . $file['name']);
                
                $attempts ++;
            }
        }
        ftp_close($ftpStream);
    }

    function downloadFile($src, $dest)
    {
        if (file_exists($dest)) {
            return;
        }
        
        $reader = fopen($src, 'r');
        
        $destPart = $dest . '.part';
        $writer = fopen($destPart, 'w');
        
        while (! feof($reader)) {
            fwrite($writer, fread($reader, 4096));
        }
        
        fclose($reader);
        fclose($writer);
        
        rename($destPart, $dest);
        
        return filesize($dest);
    }
}
