<?php
/*
 * this lib is a set of tools of things to help control your board.
 */
require_once __DIR__ .'/../classes/board.php';
require_once __DIR__ .'/../classes/repos/repoBoard.php';
require_once __DIR__ .'/../classes/repos/repoThread.php';
require_once __DIR__ .'/../classes/repos/repoPost.php';
require_once __DIR__ .'/common.php';

$globalConf = require __DIR__ ."/../conf.php";

function createBoard($name, $desc, $smallName, $isUnlisted=true){
    $BOARDREPO = BoardRepoClass::getInstance();
    $confDir = realpath(__DIR__ . "/../boardConfigs/") . '/';
    $confName = $smallName . ".php";

    if(file_exists($confDir . $confName)){
        drawErrorPageAndDie("there is already a board using this boardNameID");
    }

    copy($confDir . "baseConf.php", $confDir . $confName);

    $board = new boardClass($confDir . $confName, 0);

    $conf = $board->getConf();
    $conf['boardTitle'] = $name;
    $conf['boardSubTitle'] = $desc;
    $conf["boardNameID"] = $smallName;
    $conf['unlisted'] = $isUnlisted;

    // bc pass my ref.
    $board->setConf($conf);

    if($BOARDREPO->createBoard($board) == false){
        unlink($confName);
        drawErrorPageAndDie("failed to create a new board");
    }
    return $board;
}
function deleteBoardByID($boardID){
    $BOARDREPO = BoardRepoClass::getInstance();
    $board = $BOARDREPO->loadBoardByID($boardID);
    unlink($board->getConfPath());

    $BOARDREPO->deleteBoardByID($boardID);
}
function deleteThread($thread){
    global $globalConf;
    $THREADREPO = ThreadRepoClass::getInstance();
    $posts = $thread->getPosts();
    foreach($posts as $post){
        deletePost($post);
    }
    $THREADREPO->deleteThreadByID($thread->getConf(), $thread->getThreadID());
    $path = __DIR__."/../threads/".$thread->getThreadID();
    if(file_exists($path)){
        rmdir($path);
    }
}
function deletePost($post){
    $POSTREPO = PostRepoClass::getInstance();
    $conf = $post->getConf();
    $POSTREPO->deletePostByID($conf, $post->getPostID());

    $THREADREPO = ThreadRepoClass::getInstance();
    $thread = $THREADREPO->loadThreadByID($post->getConf(), $post->getThreadID());
    if($post->getPostID() == $thread->getOPPostID()){
        deleteThread($thread);
        return;
    }
}

function editPost($boardID, $postID, $newComment){
    $BOARDREPO = BoardRepoClass::getInstance();
    $POSTREPO = PostRepoClass::getInstance();
    $board = $BOARDREPO->loadBoardByID($boardID);
    $conf = $board->getConf();
    $post = $POSTREPO->loadPostByID($conf, $postID);
    $post->setComment($newComment);
    $POSTREPO->updatePost($conf, $post);
}
function deleteFile($boardID, $postID, $fileID){

}

function moveTreadToNewBoard($orginBoardID, $orginThreadID, $newBoardID){
    $BOARDREPO = BoardRepoClass::getInstance();
    $POSTREPO = PostRepoClass::getInstance();
    $THREADREPO = ThreadRepoClass::getInstance();

    $srcBoard = $BOARDREPO->loadBoardByID($orginBoardID);
    $srcThread = $srcBoard->getThreadByID($orginThreadID);
    $destBoard = $BOARDREPO->loadBoardByID($newBoardID);

    $srcPosts = $srcThread->getPosts();
    foreach ($srcPosts as $post){
        // we are using the dest board for configs. this has board id in it.
        $POSTREPO->createPost($destBoard->getConf(), $post);
        //make sure to add files to new posts.
    }
    //new board means new post ids for posts.
    $srcThread->setOPPostID($srcPosts[0]->getPostID);
    //thread's uniqe id is the same just need to update its board id.
    $THREADREPO->updateThread($destBoard->getConf(), $srcThread);

    // files should still be same link :P

    return;
}
function changeBoardConf($boardID, $key, $value){

}