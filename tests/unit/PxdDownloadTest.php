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
namespace pgb_liv\php_ms\Test\Unit;

use pgb_liv\php_ms\Reader\PxdInfo;
use pgb_liv\pxdsync\ProteomeExchange\PxdDownload;

class PxdDownloadTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers pgb_liv\pxdsync\ProteomeExchange\PxdDownload::__construct
     *
     * @uses pgb_liv\pxdsync\ProteomeExchange\PxdDownload
     */
    public function testObjectCanBeConstructedForValidConstructorArguments()
    {
        $id = 100;
        $info = new PxdInfo($id);
        $download = new PxdDownload($info);
        $this->assertInstanceOf('pgb_liv\pxdsync\ProteomeExchange\PxdDownload', $download);
        
        return $download;
    }
}
