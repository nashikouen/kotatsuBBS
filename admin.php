<?php

include __DIR__ .'/includes.php';

require_once __DIR__ .'/classes/html.php';
require_once __DIR__ .'/classes/auth.php';
require_once __DIR__ .'/classes/repos/repoPost.php';
require_once __DIR__ .'/classes/repos/repoBan.php';


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
    logAudit($board, $AUTH->getName() . " has logged in as a " . $AUTH);

}

function userLoggingOut(){
    global $AUTH;
    $AUTH->clearRole();
}

function userCreatingBoard(){
    global $AUTH;
    if($AUTH->isAdmin() && $AUTH->isSuper()){
        $name = htmlspecialchars($_POST['boardTitle']);
        $desc = htmlspecialchars($_POST['boardDescription']);
        $smallName = htmlspecialchars($_POST['boardURLName']);

        $smallName = str_replace(' ', '', $smallName);
        $smallName = preg_replace('/\s+/', '', $smallName);

        if (empty($name)) {
            drawErrorPageAndDie("Board title is required.");
        }
        if (empty($desc)) {
            drawErrorPageAndDie("Board description is required.");
        }
        if (empty($smallName)) {
            drawErrorPageAndDie("Board URL name is required.");
        }

        $isUnlisted = isset($_POST['boardUnlisted']);
        $board = createBoard($name, $desc, $smallName, $isUnlisted);

        logAudit($board, $AUTH->getName() . " has created a board");

        return $board;
    }
    drawErrorPageAndDie("you are not authorized.");
    return null;
}

function userDeletingBoard(){
    global $AUTH;
    global $board;
    if($AUTH->isAdmin() && $AUTH->isSuper()){
        $boardID = $_POST['boardList']; 
        if(!is_numeric($boardID)){
            drawErrorPageAndDie("invalid board id? how?");
        }
        logAudit($board, $AUTH->getName() . " has deleted a board");
        deleteBoardByID($boardID);
        return;
    }
    logAudit($board, $AUTH->getName() . " tried to delete a board but is not authorized...");
    drawErrorPageAndDie("you are not authorized.");
    return;
}

function banPost(){
    global $board;
    $POSTREPO = PostRepoClass::getInstance();
    $BANREPO = BanRepoClass::getInstance();

    /*
     *  this looks so ugly...
     */
    $post = $POSTREPO->loadPostByID($board->getConf(), $_POST['postID']);
    $isBanForerver = isset($_POST['banForever']) ? true : false;
    $isBanFile = isset($_POST['banFile']) ? true : false;
    $isBanDomain = isset($_POST['banDomain']) ? true : false;
    $domain = $_POST['domainString'];
    $isBanIP = isset($_POST['banIP']) ? true : false;
    $ip = $post->getIP();
    $isDeletePost = isset($_POST['deletePost']) ? true : false;
    $banTime = $_POST['banTime'];
    $banReason = $_POST['banReason'];
    $publicMessage = $_POST['publicMessage'];
    $isAddTosSpamDB = isset($_POST['addSpamdb']) ? true : false;

    if($isBanForerver){
        $expireTime = PHP_INT_MAX;
    }elseif(empty($banTime)){
        $expireTime = durationToUnixTime($board->getConf()['defaultBanTime']);
    }else{
        $expireTime = durationToUnixTime($banTime);
    }


    //ban ip
    if($isBanIP){
        $BANREPO->banIP($board->getBoardID(),$ip, $banReason, $expireTime, false, false, $isAddTosSpamDB);
    }
    //ban domain
    if($isBanDomain){
        $BANREPO->banDomain($board->getBoardID(), $domain, $banReason, false, $isAddTosSpamDB);
    }
    //ban file
    if($isBanFile){
        foreach($post->getFiles() as $file){
            $BANREPO->banFile($board->getBoardID(), $file->getMD5(), $banReason, false, $isAddTosSpamDB);
        }
    }
    //delete post
    if($isDeletePost){
        deletePost($post);
    }else{
        $post->appendText($publicMessage);
        updatePost($post);
    }
}



/*-------------------------------------------------------MAIN ENTRY-------------------------------------------------------*/
/*
 *  exit if we are not authenticated.
 */
if($AUTH->isNotAuth()){
    if(isset($_POST['action']) && $_POST['action'] == "login"){
        userLoggingIn();
        redirectToAdmin($board);
    }else{
        $boardHtml->drawLoginPage();
    }
    exit;
}
/*
 *  this workes exactly like bbs.php you will have a list of actions and they will map to functions.
 */
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
        case 'createBoard':
            $board = userCreatingBoard();
            redirectToAdmin($board);
            break;
        case 'deleteBoard':
            userDeletingBoard();
            redirectToAdmin($board);
            break;
        case 'banPost':
            banPost();
            redirectToBoard($board);
            break;
		default:
			$stripedInput = htmlspecialchars($_POST['action'], ENT_QUOTES, 'UTF-8');
			drawErrorPageAndDie("invalid action: " . $stripedInput);
			break;
	}
}elseif (isset($_GET['action'])){
	$action = $_GET['action'];
	switch ($action) {
        case 'listByIP':
            //userLoggingOut();
            //redirectToBoard($board);
			break;
        case 'editPost':
            //userLoggingIn();
            //redirectToAdmin($board);
            break;
        case 'banPost':
            $postID = $_GET['postID'];
            if(is_numeric($postID) == false){
                drawErrorPageAndDie("not a valid post id");
            }
            $POSTREPO = PostRepoClass::getInstance();
            $post = $POSTREPO->loadPostByID($board->getConf(),$postID);
            if(is_null($post)){
                drawErrorPageAndDie("can not find post");
            }
            $boardHtml->drawBanUserPage($post);
            return;
		default:
			$stripedInput = htmlspecialchars($_GET['action'], ENT_QUOTES, 'UTF-8');
			drawErrorPageAndDie("invalid action: " . $stripedInput);
			break;
	}
    return;
}


$boardHtml->drawAdminPage();