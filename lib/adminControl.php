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

function deleteFilesInThreadByID($id){
    $dir = __DIR__."/../threads/".$id;

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $dir . '/' . $file;

            if (!is_dir($filePath)) {
                unlink($filePath);
            }
        }
    }
    rmdir($dir);
}
function deleteBoardByID($boardID){
    $BOARDREPO = BoardRepoClass::getInstance();
    $board = $BOARDREPO->loadBoardByID($boardID);

    $threads = $board->getThreads();
    foreach($threads as $thread){
        deleteFilesInThreadByID($thread->getThreadID());
    }
    unlink($board->getConfPath());
    $BOARDREPO->deleteBoardByID($boardID);
}
function deleteThread($thread){
    global $globalConf;
    $THREADREPO = ThreadRepoClass::getInstance();
    deleteFilesInThreadByID($thread->getThreadID());
    foreach($thread->getPosts() as $post){
        deletePost($post, true);
    }
    $THREADREPO->deleteThreadByID($thread->getConf(), $thread->getThreadID());
}
function deletePost($post, $isDeletingThread=false){
    $THREADREPO = ThreadRepoClass::getInstance();
    $thread = $THREADREPO->loadThreadByID($post->getConf(), $post->getThreadID());
    if($post->getPostID() == $thread->getOPPostID() && $isDeletingThread == false){
        deleteThread($thread);
        return;
    }else{
        $conf = $post->getConf();
        foreach($post->getFiles() as $file){
            deleteFile($file);
        }
        $POSTREPO = PostRepoClass::getInstance();
        $POSTREPO->deletePostByID($conf, $post->getPostID());
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
function deleteFile($file){
    unlink($file->getThumbnailPath());
    unlink($file->getFilePath());
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