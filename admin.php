<?php

include __DIR__ .'/includes.php';

require_once __DIR__ .'/classes/html.php';
require_once __DIR__ .'/classes/auth.php';

require_once __DIR__ .'/lib/common.php';
require_once __DIR__ .'/lib/adminControl.php';

$AUTH = AuthClass::getInstance();
$board = getBoardFromRequest();
$boardHtml = new htmlclass($board->getConf(), $board);

function userLoggingIn(){
    global $globalConf;
    global $AUTH;
    global $board;

    if(!isset($_POST['password'])){
        drawErrorPageAndDie("no password provided");
    }
    
    $passHash = genTripcode($_POST['password'], $globalConf['tripcodeSalt']);

    /*
     *  try loggin in as what ever user it is.
     */
    $AUTH->setRoleByHash($passHash, $board->getBoardID());
}

function userLoggingOut(){

}

if($AUTH->isNotAuth()){
    if(isset($_POST['action']) && $_POST['action'] == "login"){
        userLoggingIn();
        redirectToAdmin($board);
    }else{
        $boardHtml->drawLoginPage();
    }
    exit;
}

if(isset($_POST['action'])){
	$action = $_POST['action'];
	switch ($action) {
        case 'logout':
            userLoggingOut();
            redirectToBoard($board);
			break;
		case 'login':
            userLoggingIn();
            redirectToAdmin($board);
			break;
		default:
			$stripedInput = htmlspecialchars($_POST['action'], ENT_QUOTES, 'UTF-8');
			drawErrorPageAndDie("invalid action: " . $stripedInput);
			break;
	}
}

$boardHtml->drawAdminPage();