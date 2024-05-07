<?php

$rootDir = __DIR__ ."/../";
$globalConf = require $rootDir ."/conf.php";
$loggingDir = $rootDir . $globalConf['logDir'];

$auditLogPath = $loggingDir . "audit.log";
$errorLogPath = $loggingDir . "error.log";
//$banLogPath = $loggingDir . "ban.log";
//$globalLogPath = $loggingDir . "globalBan.log";


if (file_exists(!$loggingDir)){
    mkdir($loggingDir);
}

foreach([$auditLogPath, $errorLogPath, $banLogPath, $globalLogPath] as $file){
    if (file_exists(!$dir)){
        touch($file);
    }
}

function logError($errorMessage){
    global $errorLogPath;
    $logs = fopen($errorLogPath, 'a');
    fwrite($logs, "[" . date('Y-m-d H:i:s', time()) . "]" . $errorMessage);
    fclose($logs);
}
function logAudit($auditMessage){
    global $auditLogPath;
    $logs = fopen($auditLogPath, 'a');
    fwrite($logs, "[" . date('Y-m-d H:i:s', time()) . "]" . $auditMessage);
    fclose($logs);
}
