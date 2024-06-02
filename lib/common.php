<?php
require_once __DIR__ .'/../classes/repos/repoBoard.php';

function nameIDToBoardID($nameID){
	$files = glob(__DIR__ . '/../boardConfigs/*.php');

	foreach($files as $file){
		$conf = require($file);
		if($conf['boardNameID'] == $nameID){
			return $conf['boardID'];
		}
	}
	return '';
}
function boardIDToName($boardID){
    $files = glob(__DIR__ . '/../boardConfigs/*.php');

    foreach($files as $file){
        $conf = require($file);
        if($conf['boardID'] == $boardID){
            return $conf['boardNameID'];
        }
    }
    return '';
}
function getBoardListing(){
	$files = glob(__DIR__ . '/../boardConfigs/*.php');
	$listing = [];

	foreach($files as $file){
		$conf = include($file);
		if($conf['boardID'] == -1 || $conf['unlisted']){
			continue;
		}
		$listing[$conf['boardNameID']] = '/' . $conf['boardNameID'] . '/';
	}
	return $listing;
}
function getAllBoardConfs(){
	$files = glob(__DIR__ . '/../boardConfigs/*.php');
	$listing = [];

	foreach($files as $file){
		$conf = include($file);
		if($conf['boardID'] == -1 ){
			continue;
		}
		$listing[] =  $conf;
	}
	return $listing;
}
function getBoardConfByID($id){
    $files = glob(__DIR__ . '/../boardConfigs/*.php');

	foreach($files as $file){
		$conf = require($file);
		if($conf['boardID'] == $id){
			return $conf;
		}
	}
	return '';
}
function redirectToPost($post){
    $name = boardIDToName($post->getBoardID());
    $threadID = $post->getThreadID();
    $postID = $post->getPostID();

    $url = ROOTPATH. "$name/thread/$threadID/#p$postID";

    header("Location: $url");
    exit;
}
function redirectToThread($thread){
    $name = boardIDToName($thread->getBoardID());
    $threadID = $thread->getThreadID();

    $url = ROOTPATH. "$name/thread/$threadID/";

    header("Location: $url");
    exit;
}
function redirectToBoard($board){
    $name = boardIDToName($board->getBoardID());
    $url = ROOTPATH. "$name";

    header("Location: $url");
    exit;
}
function redirectToAdmin($board){
    $name = boardIDToName($board->getBoardID());
    $url = ROOTPATH. "$name/admin";

    header("Location: $url");
    exit;
}
function drawErrorPageAndDie($txt){
	$html ='
	<!DOCTYPE html>
	<html lang="en">
	<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Error recived</title>
	<style>
		body {
			background-color: #d0f0c0;
			font-family: Arial, sans-serif;
		}
		.postblock {
			padding: 20px;
			background-color: #ffcccc;
			border: 2px solid #ff0000;
			margin: 10px 0;
			text-align: center;
		}
	</style>
	</head>
	<body>

	<div class="postblock">
		<p>'. $txt .'</p>
	</div>

	</body>
	</html>';
	echo $html;
	die();
}
function getBoardFromRequest(){
    $BOARDREPO = BoardRepoClass::getInstance();
    $boardID = $_POST['boardID'] ?? @nameIDToBoardID($_GET['boardNameID']) ?? '';

    if (!is_numeric($boardID)) {
        drawErrorPageAndDie("you must have a boardID");
    }
    $board = $BOARDREPO->loadBoardByID($boardID);
    if(is_null($board)) {
        drawErrorPageAndDie("board with the boardID of \"".$boardID."\"dose not exist");
    }
    return $board;
}