<?php

include __DIR__ .'/includes.php';

require_once __DIR__ .'/classes/html.php';
require_once __DIR__ .'/classes/auth.php';

require_once __DIR__ .'/lib/common.php';
require_once __DIR__ .'/lib/adminControl.php';

$AUTH = AuthClass::getInstance();

$board = getBoardFromRequest();
$boardHtml = new htmlclass($board->getConf(), $board);

if(isset($_POST['action'])){
	$action = $_POST['action'];
	
	switch ($action) {
		case 'login':
            redirectToPost($post);
			break;
		default:
			$stripedInput = htmlspecialchars($_POST['action'], ENT_QUOTES, 'UTF-8');
			drawErrorPageAndDie("invalid action: " . $stripedInput);
			break;
	}
}

if($AUTH->isNotAuth()){
    $boardHtml->drawLoginPage();
}
