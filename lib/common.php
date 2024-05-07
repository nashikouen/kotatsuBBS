<?php

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