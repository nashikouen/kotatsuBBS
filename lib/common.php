<?php
require_once __DIR__ .'/../classes/repos/repoBoard.php';

// idk how to get this working for openbsd...
function postWebHook($boardID, $threadID, $postID=""){
    global $globalConf;

    $url = 'https://'.DOMAIN.ROOTPATH.boardIDToName($boardID).'/thread/'.$threadID.'/#p'.$postID;
    
    $stream = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'content' => "new post <$url>", 
            ]),
        ]
    ]);
    
    file_get_contents($globalConf['webhook'], false, $stream);
}
function bytesToHumanReadable($size){
    if($size == 0){
        $format = "";
    }
    elseif($size <= 1024){
        $format = $size." B";
    }
    elseif($size <= (1024*1024)){
        $format = sprintf ("%d KB",($size/1024));
    }
    elseif($size <= (1000*1024*1024)){
        $format = sprintf ("%.2f MB",($size/(1024*1024)));
    }
    elseif($size <= (1000*1024*1024*1024)){
        $format = sprintf ("%.2f GB",($size/(1024*1024*1024)));
    }
    elseif($size <= (1000*1024*1024*1024*1024)  || $size >= (1000*1024*1024*1024*1024)){
        $format = sprintf ("%.2f TB",($size/(1024*1024*1024*1024)));
    }
    else{ 
        $format = $size."B";
    }

    return $format;
}
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
function getBoardListing($getUnlited=false){
	$files = glob(__DIR__ . '/../boardConfigs/*.php');
	$listing = [];

	foreach($files as $file){
		$conf = include($file);
		if($conf['boardID'] == -1){
            continue;
        }elseif($conf['unlisted'] == true && $getUnlited == true){
			$listing[$conf['boardNameID']] = '/' . $conf['boardNameID'] . '/';
            continue;
		}elseif($conf['unlisted'] == false && $getUnlited == false){
            $listing[$conf['boardNameID']] = '/' . $conf['boardNameID'] . '/';
            continue;
        }
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
function getBoardCount(){
    $files = glob(__DIR__ . '/../boardConfigs/*.php');
	$count = 0;

	foreach($files as $file){
		$conf = include($file);
		if($conf['boardID'] == -1 ){
			continue;
		}
		$count = $count + 1;
	}
	return $count;
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
function getBoardByID($boardID){
    $BOARDREPO = BoardRepoClass::getInstance();
    return $BOARDREPO->loadBoardByID($boardID);
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
function redirectToHome(){
    $url = ROOTPATH;

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
		<p>'; 
            if(is_array($txt)){
                print_r($txt);
            }else{
                echo($txt);
            }
            $html ='</p>
	</div>

	</body>
	</html>';
	echo $html;
	die();
}
function getBoardFromRequest($allowNull=false){
    $BOARDREPO = BoardRepoClass::getInstance();
    $boardID = $_POST['boardID'] ?? @nameIDToBoardID($_GET['boardNameID']) ?? '';

    if (!is_numeric($boardID)) {
        if($allowNull == false){
            drawErrorPageAndDie("you must have a boardID");
        }
    }
    $board = $BOARDREPO->loadBoardByID($boardID);
    if(is_null($board)) {
        if($allowNull == false){
            drawErrorPageAndDie("board with the boardID of \"".$boardID."\"dose not exist");
        }
    }
    return $board;
}
function durationToUnixTime($duration){
    $starttime = $_SERVER['REQUEST_TIME'];
    
    $durationWeeks = preg_match("/(\d+)w/", $duration, $matchWeeks) ? (int)$matchWeeks[1] : 0;
    $durationDays = preg_match("/(\d+)d/", $duration, $matchDays) ? (int)$matchDays[1] : 0;
    $durationHours = preg_match("/(\d+)h/", $duration, $matchHours) ? (int)$matchHours[1] : 0;
    $durationMinutes = preg_match("/(\d+)min/", $duration, $matchMinutes) ? (int)$matchMinutes[1] : 0;

    return $starttime + ($durationWeeks * 604800) + ($durationDays * 86400) + ($durationHours * 3600) + ($durationMinutes * 60);
}