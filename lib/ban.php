<?php
require_once  __DIR__ .'/../classes/repos/DBConnection.php';

$conn = DatabaseConnection::getInstance();

function banIP($boardID, $ip, $isRangeBaned=false, $isGlobal=false, $isPublic=false, $catagory="none"){

}
function banFile($boardID, $fileHash, $isPreceptual=false, $isGlobal=false, $isPublic=false, $catagory="none"){

}
function banDomain($boardID, $domain,$isGlobal=false, $isPublic=false, $catagory="none"){

}



function isIpBanned($boardID, $ip){

}
function isFileBanned($boardID, $fileHash, $isPreceptual=false){

}
function isDomainBanned($boardID, $domain){

}



function isGlobalIpBanned($boardID, $ip){

}
function isGlobalFileBanned($boardID, $fileHash, $isPreceptual=false){

}
function isGlobalDomainBanned($boardID, $domain){

}