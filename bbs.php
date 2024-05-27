<?php

include __DIR__ .'/includes.php';

require_once __DIR__ .'/classes/board.php';
require_once __DIR__ .'/classes/thread.php';
require_once __DIR__ .'/classes/post.php';
require_once __DIR__ .'/classes/file.php';

require_once __DIR__ .'/classes/hook.php';
require_once __DIR__ .'/classes/auth.php';
require_once __DIR__ .'/classes/fileHandler.php';
require_once __DIR__ .'/classes/html.php';

require_once __DIR__ .'/classes/repos/repoBoard.php';
require_once __DIR__ .'/classes/repos/repoThread.php';
require_once __DIR__ .'/classes/repos/repoPost.php';
//require_once __DIR__ .'/classes/repos/repoFile.php';

require_once __DIR__ .'/lib/common.php';
require_once __DIR__ .'/lib/adminControl.php';

$AUTH = AuthClass::getInstance();
$HOOK = HookClass::getInstance();
$POSTREPO = PostRepoClass::getInstance();
$THREADREPO = ThreadRepoClass::getInstance();
//$FILEREPO = FileRepoClass::getInstance();
$BOARDREPO = BoardRepoClass::getInstance();

function genUserPostFromRequest($conf, $thread, $isOp=false){
	global $AUTH;
	global $HOOK;
	global $globalConf;

	/*
	 * below is the exact same thing as this
	 * 
	 * $name;
	 * if(!empty($_POST['name']))){
	 *     $name = $_POST['name'];
	 * }elseif (!$conf['requireName']){
	 *     $name = $conf['defaultName'];
	 * }else{
	 *     drawErrorPageAndDie("name is a required feild");
	 * }
	 */
	$name = !empty($_POST['name']) ? $_POST['name'] : (!$conf['requireName'] ? $conf['defaultName'] : drawErrorPageAndDie("your Name is required."));
	$email = !empty($_POST['email']) ? $_POST['email'] : (!$conf['requireEmail'] ? $conf['defaultEmail'] : drawErrorPageAndDie("your Email is required."));
	$subject = !empty($_POST['subject']) ? $_POST['subject'] : (!$conf['requireSubject'] ? $conf['defaultSubject'] : drawErrorPageAndDie("a Subject is required."));
	$comment = !empty($_POST['comment']) ? $_POST['comment'] : (!$conf['requireComment'] ? $conf['defaultComment'] : drawErrorPageAndDie("a comment is required."));
	$password = !empty($_POST['password']) ? $_POST['password'] : (isset($_COOKIE['password']) ? $_COOKIE["password"] : null);
	
	//gen post password if none is provided
	if($password == null){
        // op's first post, he gets cookie, clicks on ip logger. now evil has an ip + time...
		$hasinput = $_SERVER['REMOTE_ADDR'] . time() . $globalConf['tripcodeSalt'];
		$hash = hash('sha256', $hasinput);
		$password = substr($hash, -MAX_INPUT_LENGTH_PASSWORD); 
	}

    //drawErrorPageAndDie("Name: $name, Email: $email, Password: $password");
	//cookies!!!!
	setrawcookie('name', rawurlencode($name), $conf['cookieExpireTime']);
	setrawcookie('email', rawurlencode($email), $conf['cookieExpireTime']);
	setrawcookie('password', rawurlencode($password), $conf['cookieExpireTime']);

	$post = new PostDataClass(	$conf,$name,$email,$subject,
								$comment,$password,time(),$_SERVER['REMOTE_ADDR'],
								$thread->getThreadID());
	// make sure stuff dose not blow over the db limits
	$post->validate();

    // get the uploaded files and put them inside the post object.
    $fileHandler = new fileHandlerClass($conf);
    $uploadFiles = $fileHandler->getFilesFromPostRequest();
    $procssedFiles = $fileHandler->procssesFiles($uploadFiles, $isOp); 

    foreach ($procssedFiles as $file) {
        $post->addFile($file);
    }

    $noFilesUploaded = count($procssedFiles) <= 0;
    if($conf['requireFile'] && $noFilesUploaded){
        drawErrorPageAndDie("a file is required");
    }
    if($conf['opMustHaveFile'] && $isOp && $noFilesUploaded){
        drawErrorPageAndDie("you must have a file as OP");
    }
    if($conf['postMustHaveFileOrComment'] && $noFilesUploaded && ($comment == $conf['defaultComment'] || $comment == "")){
        drawErrorPageAndDie("you must have a file or a comment");
    }
    
    /* 
     *  if we are not admin or mod, remove any html tags.
     *  dont get confused. the first if statment catchs any non admins and mods.
     *  any one getting to the second if statement should be admin or mod.
     */
	if(!$AUTH->isAdmin($conf['boardID']) && !$AUTH->isModerator($conf['boardID'])){ 	
		$post->stripHtml();
	}elseif(isset($_POST['stripHTML'])){
        $post->stripHtml();
    }

	//if the board lets you tripcode, apply tripcode to name.
	if($conf['canTripcode']){
		$post->applyTripcode();	
	}else{
        $post->stripTripcodePass();
    }

	//$HOOK->executeHook("onUserPostToBoard", $post, $fileHandler);// HOOK base post fully loaded with no html

	/* prep post for db and drawing */

	// if the board allows embeding of links. admins and mods are expeced to embed there own things since they have that power
	if($conf['autoEmbedLinks'] && !$AUTH->isAdmin($conf['boardID']) && !$AUTH->isModerator($conf['boardID'])){
		$post->embedLinks();
	}
	// if board allows post to link to other post.
	if($conf['allowQuoteLinking']){
		$post->quoteLinks();
	}

	//new lines get converted to <br>
	$post->addLineBreaks();

	// stuff like bb code, emotes, capcode, ID, should all be handled in moduels.
	$HOOK->executeHook("onPostPrepForDrawing", $post);// HOOK post with html fully loaded

	return $post;
}
function userPostNewPostToThread($board){
	$conf = $board->getConf();
	global $POSTREPO;
	global $THREADREPO;

	// load existing thread
	$thread = $board->getThreadByID($_POST['threadID']);	

	// create post to thread
	$post = genUserPostFromRequest($conf, $thread);

	if($post->isBumpingThread()){
		$thread->bump();
	}

	// save post to data base.
	$POSTREPO->createPost($conf, $post);
	$THREADREPO->updateThread($conf, $thread);

    // fill in missing data we could not have known untill after comiting to repo.
    $post->setThreadID($thread->getThreadID());
    $POSTREPO->updatePost($conf, $post);

    $threadDir = __DIR__ . "/threads/". $thread->getThreadID();
    $post->moveFilesToDir($threadDir);
    $post->addFilesToRepo();

	return $post;
}
function userPostNewThread($board){
	$conf = $board->getConf();
	global $POSTREPO;
	global $THREADREPO;

	// make a new thread
	$thread = new threadClass($conf, time());

	// create post with thread
	$post = genUserPostFromRequest($conf, $thread, true);

	// save post and thread to data base.
	$POSTREPO->createPost($conf, $post);
	$THREADREPO->createThread($conf, $thread, $post);
    
    // fill in missing data we could not have known untill after comiting to repo.
    $post->setThreadID($thread->getThreadID());
    $POSTREPO->updatePost($conf, $post);

    $threadDir = __DIR__ . "/threads/" . $thread->getThreadID();
    mkdir($threadDir);
    $post->moveFilesToDir($threadDir);
    $post->addFilesToRepo();

	return $thread;
}
function userDeletedPost($board, $post, $password){
    global $AUTH;
    if($password == "" && isset($_COOKIE['password'])){
        $password = $_COOKIE['password'];
    }
    // the passwords dont match and user dose not have power.
    if($password != $post->getPassword() && $AUTH->isNotAuth()){
        return;
    }
    deletePost($post);
}

/*-------------------------------------------------------MAIN ENTRY-------------------------------------------------------*/

/*
 * this file is the main request handler of the board after it is already installed.
 * below handels the routing.
 * 
 * you should not try and hack anything into this file unless you know what you are doing.
 * ./moduels/ is where your hacks should be put. and then enable them through the admin pannel.
 */

$board = getBoardFromRequest();
$boardHtml = new htmlclass($board->getConf(), $board);


/*
 *	since the webserver will be using rewrites. this next section will look a little funky and redundent.
 *  but this is wat i want to achive with rewrites. 
 * 
 */

/*----------get action recived----------*/
if (isset($_GET['thread'])){

	$threadID = $_GET['thread'];
	if(!is_numeric($threadID)){
		drawErrorPageAndDie("thread must be a number");
	}

	$thread = $board->getThreadByID($threadID);
	if(is_null($thread)){
		drawErrorPageAndDie("thread dose not exist");
	}

	$boardHtml->drawThreadPage($thread);
}
/*----------post action recived----------*/
elseif(isset($_POST['action'])){
	$action = $_POST['action'];
	
	switch ($action) {
		case 'postToThread':
			$post = userPostNewPostToThread($board);
			$thread = $board->getThreadByID($_POST['threadID']);
			//$boardHtml->drawThreadPage($thread);
            redirectToPost($post);
			break;
		case 'postNewThread':
			$thread = userPostNewThread($board);
			//$boardHtml->drawThreadPage($thread);
            redirectToThread($thread);
			break;
        case 'deletePosts':
            if (empty($_POST['postIDs'])) {
                drawErrorPageAndDie("no posts where selected.");
            }
            $postIDs = $_POST['postIDs'];
            foreach ($postIDs as $postId) {
                if(!is_numeric($postId)){
                    continue;
                }
                $post = $POSTREPO->loadPostByID($board->getConf(), $postId);
                userDeletedPost($board, $post, $_POST['password']);
            }
            redirectToBoard($board);
            break;
		default:
			$stripedInput = htmlspecialchars($_POST['action'], ENT_QUOTES, 'UTF-8');
			drawErrorPageAndDie("invalid action: " . $stripedInput);
			break;
	}
}
/*----------no action recived----------*/
else{
	$page = $_GET['page'] ?? $_POST['page'] ?? 0;
	if (!is_numeric($page)) {
		drawErrorPageAndDie("invalid page");
	}
	$page = abs(intval($page));
	$boardHtml->drawThreadListingPage($page);
}