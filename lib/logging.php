<?php
/*
 *  name as implies. this logs things to a text file.
 */

$globalConf = require __DIR__ ."/../conf.php";
$loggingDir = $globalConf['logDir'] . 'kotatsuLog/';

$auditLogPath = $loggingDir . "audit.log";
$errorLogPath = $loggingDir . "error.log";

if (!file_exists($loggingDir)){
    mkdir($loggingDir);
}

foreach([$auditLogPath, $errorLogPath] as $file){
    if (!file_exists($file)){
        touch($file);
    }
}

function logError($board, $errorMessage){
    global $errorLogPath;
    $logs = fopen($errorLogPath, 'a');
    fwrite($logs, "[" . date('Y-m-d H:i:s', time()) . "] board: " . boardIDToName($board->getBoardID()) . " boardID: " . $board->getBoardID() . " >> " . $errorMessage."\n");
    fclose($logs);
}
function logAudit( $board, $auditMessage){
    global $auditLogPath;
    $logs = fopen($auditLogPath, 'a');
    fwrite($logs, "[" . date('Y-m-d H:i:s', time()) . "] board: " . boardIDToName($board->getBoardID()) . " boardID: " . $board->getBoardID() . " >> " . $auditMessage."\n");
    fclose($logs);
}
