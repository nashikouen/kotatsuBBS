<?php

interface BoardRepositoryInterface {
    public function updateBoard($board);
    public function loadBoards();
    public function loadBoardByID($boardID);
    public function deleteBoardByID($boardID);
    public function createBoard($board);
}

interface ThreadRepositoryInterface {
    public function createThread($boardConf, $thread, $post);
    public function loadThreadByID($boardConf, $threadID);
    public function loadThreads($boardConf);
    public function loadThreadsByPage($boardConf, $page);
    public function updateThread($boardConf, $thread);
    public function deleteThreadByID($boardCon, $threadID);
    public function archiveOldThreads($boardConf, $maxActiveThreads);
    public function fetchThreadIDsForDeletion($boardConf, $offset);
}

interface PostDataRepositoryInterface {
    public function createPost($boardConf, $post);
    public function createPostImport($boardConf, $post);
    public function loadPostByID($boardConf, $postID);
    public function loadPosts($boardConf);
    public function loadPostsByThreadID($boardConf, $threadID);
    public function loadNPostByThreadID($boardConf, $threadID, $num);
    public function setPostID($boardConf, $post, $newPostID);
    public function updatePost($boardConf, $post);
    public function deletePostByID($boardConf, $postID);
}

interface FileRepositoryInterface {
    //public function saveBoard($board);
    //public function loadBoards();
    //public function loadBoardByID($boardID);
    //public function deleteBoardByID($boardID);
    //public function createBoard($board);
}