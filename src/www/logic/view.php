<?php
use pgb_liv\php_ms\Reader\PxdInfo;
use pgb_liv\pxdsync\ProteomeExchange\PxdDownload;

if (! isset($_GET['pxd'])) {
    die('Bad page access');
}

$info = new PxdInfo($_GET['pxd']);

$fileList = array();

$ftpStream = ftp_connect('ftp.pride.ebi.ac.uk', 21);
if (!$ftpStream) {
    die('Remote FTP Server Down');
}

$loginResult = ftp_login($ftpStream, 'anonymous', 'example@example.com');

foreach ($info->getDatasetFileList() as $file) {
    $fileEntry = array();
    $fileEntry['name'] = $file['name'];
    
    $remotePath = str_replace('ftp://ftp.pride.ebi.ac.uk', '', $file['location']);
    $raw = ftp_raw($ftpStream, "SIZE $remotePath");
    $fileEntry[PxdDownload::REMOTE_SIZE] = substr($raw[0], 4);
    
    $localPath = DATA_PATH_PREFIX . '/' . $info->getIdString() . '/' . $fileEntry['name'];
    if (file_exists($localPath)) {
        $fileEntry[PxdDownload::LOCAL_SIZE] = filesize($localPath);
        
        $fileEntry['isDownloaded'] = $fileEntry[PxdDownload::LOCAL_SIZE] == $fileEntry[PxdDownload::REMOTE_SIZE] ? 'Yes' : 'Failed';
        if ($fileEntry['isDownloaded'] == 'Failed') {
            $fileEntry['error'] = 'Recieved ' . $fileEntry[PxdDownload::LOCAL_SIZE] . '. Expected ' . $fileEntry[PxdDownload::REMOTE_SIZE];
        }
    } else {
        if (file_exists($localPath . '.part')) {
            $fileEntry[PxdDownload::LOCAL_SIZE] = filesize($localPath . '.part');
            $fileEntry['error'] = 'Recieved ' . round(($fileEntry[PxdDownload::LOCAL_SIZE] / $fileEntry[PxdDownload::REMOTE_SIZE]) * 100, 2) .
                 '%';
        }
        
        $fileEntry['isDownloaded'] = 'No';
    }
    
    $fileList[] = $fileEntry;
}

ftp_close($ftpStream);

$smarty->assign('fileList', $fileList);
