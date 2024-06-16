<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$globalConf = require __DIR__ ."/conf.php";

define("ROOTPATH", $globalConf['webRootPath']);
define("DOMAIN", $globalConf['domain']);

define("MAX_INPUT_LENGTH", 255 - 128); /* you cant make this bigger then 255 with out changing the cap to to the db */
define("MAX_INPUT_LENGTH_PASSWORD", 16 - 8); /* you cant make this bigger then 16 with out changing the cap to to the db */

define("IMAGE_EXTENTIONS", $globalConf['IMAGE_EXTENTIONS']);
define("VIDEO_EXTENTIONS", $globalConf['VIDEO_EXTENTIONS']);
define("AUDIO_EXTENTIONS", $globalConf['AUDIO_EXTENTIONS']);

ini_set('session.cookie_lifetime', $globalConf['sessionLifeTime']);
ini_set("memory_limit", $globalConf['memoryLimit']);
