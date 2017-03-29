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
chdir('src/public_html');

$envTestConf = getenv('PHPUNIT_CONF_PATH');

if (file_exists('../conf/config.test.php')) {
    require_once '../conf/config.test.php';
} elseif ($envTestConf !== false && file_exists($envTestConf)) {
    require_once $envTestConf;
} else {
    var_dump($envTestConf);
    die('ERROR: Database config missing');
}

require_once '../conf/autoload.php';
