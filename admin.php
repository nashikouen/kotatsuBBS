<?php

include __DIR__ .'/includes.php';

require_once __DIR__ .'/classes/html.php';
require_once __DIR__ .'/classes/auth.php';
require_once __DIR__ .'/classes/repos/repoPost.php';
require_once __DIR__ .'/classes/repos/repoBan.php';


require_once __DIR__ .'/lib/common.php';
require_once __DIR__ .'/lib/adminControl.php';

$AUTH = AuthClass::getInstance();
$board = getBoardFromRequest(true);
$POSTREPO = PostRepoClass::getInstance();

/* 
 *  x_x i really dont know, i want to have a admin log in when there is no board 
 *  i think i have a core problom with requireing a board.. but its a imageboard, it must all have a board?
 * 
 *  this bad code, it is so super users can still login, they will be placed in any real board or kicked to home once they have auth.
 *  it dose checking for get requests. and userLoggingIn()
 */

$noBoard = false;
if(is_null($board)){
    $noBoard = true;
    $board = new boardClass(__DIR__ . '/boardConfigs/baseConf.php', -1, -1);
}

$boardHtml = new htmlclass($board->getConf(), $board);

function goToRealboard(){
    global $AUTH;
    global $noBoard;

    if($AUTH->isSuper() && $noBoard){
        $board = getFirstValidBoard();
        if(is_null($board)){
            drawErrorPageAndDie("something is seriously wrong. contact kotatsuBBS and report this");
        }
        redirectToAdmin($board);
    }else{
        redirectToHome();
    }
}
function userLoggingIn(){
    global $globalConf;
    global $AUTH;
    global $board;
    global $noBoard;

    if(!isset($_POST['password'])){
        drawErrorPageAndDie("no password provided");
    }
    
    $passHash = genTripcode($_POST['password'], $globalConf['tripcodeSalt']);

    /*
     *  try loggin in as what ever user it is.
     */
    $AUTH->setRoleByHash($passHash, $board->getBoardID());
    logAudit($board, $AUTH->getName() . " has logged in as a " . $AUTH);
    if($noBoard){
        goToRealboard();
    }
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
        return $boardID;
    }
    logAudit($board, $AUTH->getName() . " tried to delete a board but is not authorized...");
    drawErrorPageAndDie("you are not authorized.");
    return -1;
}

function banPost(){
    global $board;
    global $AUTH;
    $POSTREPO = PostRepoClass::getInstance();
    $BANREPO = BanRepoClass::getInstance();
    $banText = "";
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
    $rangeBan = $_POST['rangeBan'];
    $category = $_POST['category'] ?? "none";
    $isDeletePost = isset($_POST['deletePost']) ? true : false;
    $banTime = $_POST['banTime'];
    $banReason = $_POST['banReason'];
    $publicMessage = $_POST['publicMessage'];
    $isPublic = isset($_POST['isPublic']) ? true : false;

    if($isBanForerver){
        $expireTime = PHP_INT_MAX;
    }elseif(empty($banTime)){
        $expireTime = durationToUnixTime($board->getConf()['defaultBanTime']);
    }else{
        $expireTime = durationToUnixTime($banTime);
    }

    //ban ip
    if($isBanIP){
        $BANREPO->banIP($board->getBoardID(),$ip, $banReason, $expireTime, $rangeBan, false, $isPublic, $category);
        $banText .= " is IP banned untill ". $expireTime. ".";
    }
    //ban domain
    if($isBanDomain){
        $BANREPO->banDomain($board->getBoardID(), $domain, $banReason, false, $isPublic, $category);
        $banText .= " domain has been banned.";
    }
    //ban file
    if($isBanFile){
        foreach($post->getFiles() as $file){
            $BANREPO->banFile($board->getBoardID(), $file->getMD5(), $banReason, false,false, $isPublic, $category);
        }
        $banText .= " files has been banned.";
    }
    
    logAudit($board, $AUTH->getName() . ' a banned post. '. $banText);

    //delete post
    if($isDeletePost){
        logAudit($board, $AUTH->getName() . " has deleted post " . $post->getPostID());
        deletePost($post);
    }else{
        $post->appendText($publicMessage);
        updatePost($post);
    }
}

function userPostListing(){
    global $POSTREPO;
    global $board;
    global $boardHtml;
    $page = 0;
    $posts = $POSTREPO->loadPostsByPage($board->getConf(), $page);

    $boardHtml->drawAdminPostListingPage($posts);
    exit();
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
            $id = userDeletingBoard();
            if($id == $board->getBoardID()){
                redirectToHome();
            }else{
                redirectToAdmin($board);
            }
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
    if($noBoard){
        goToRealboard();
    }
	$action = $_GET['action'];
	switch ($action) {
        case 'postListing':
            userPostListing();
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

if($noBoard){
    goToRealboard();
}else{
    $boardHtml->drawAdminPage();
}
