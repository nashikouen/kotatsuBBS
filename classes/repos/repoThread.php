<?php
require_once __DIR__ .'/DBConnection.php';
require_once __DIR__ .'/interfaces.php';
require_once __DIR__ .'/../thread.php';
require_once __DIR__ .'/../../lib/common.php';
require_once __DIR__ .'/repoPost.php';



class ThreadRepoClass implements ThreadRepositoryInterface {
    private $db;
    private static $instance = null;

    private function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }

    private function __clone() {}
    public function __wakeup() { throw new Exception("Unserialization of AuthClass instances is not allowed.");}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new ThreadRepoClass();
        }
        return self::$instance;
    }
    public function createThread($boardConf, $thread, $post) {
        try{
            if ($post->getPostID() == -1) {
                error_log("post must be registered before the thread.");
                return false;
            }
            // get vlaues for querry
            $bump = $thread->getLastBumpTime();
            $postID = $post->getPostID();
            $postCount = 1;

            //construct querry
            $stmt = $this->db->prepare("INSERT INTO threads (boardID, lastTimePosted, opPostID) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $boardConf['boardID'], $bump, $postID);

            // run qerrry
            $success = $stmt->execute();
            if (!$success) {
                throw new Exception("Failed to create new thread");
            }
            // update objects and repo with new data
            $thread->setThreadID($this->db->insert_id);
            $thread->setPostCount($postCount);
            $thread->setOPPostID($post->getPostID());

            $POSTREPO = PostRepoClass::getInstance();
            $post->setThreadID($this->db->insert_id);
            $POSTREPO->updatePost($boardConf, $post);


            $stmt->close();
            return $success;
        } catch (Exception $e) {
            error_log($e->getMessage());
            drawErrorPageAndDie($e->getMessage());
            return false;
        }
    }
    public function loadThreadByID($boardConf, $threadID) {
        $stmt = $this->db->prepare("SELECT * FROM threads WHERE boardID = ? AND threadID = ?");
        $stmt->bind_param("ii", $boardConf['boardID'], $threadID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $thread = new threadClass($boardConf, $row['lastTimePosted'], $row['threadID'], $row['opPostID']);
            $stmt->close();
            return $thread;
        } else {
            $stmt->close();
            return null;
        }
    }
    public function loadThreads($boardConf) {
        $threads = [];
        $stmt = $this->db->prepare("SELECT * FROM threads WHERE boardID = ?");
        $stmt->bind_param("i", $boardConf['boardID']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $threads[] = new threadClass($boardConf, $row['lastTimePosted'], $row['threadID'], $row['opPostID']);
        }
        $stmt->close();
        return $threads;
    }
    public function loadThreadsByPage($boardConf, $page=0){
        $threads = [];
        $offset = $page * $boardConf['threadsPerPage'];

        $stmt = $this->db->prepare("SELECT * FROM threads WHERE boardID = ? ORDER BY lastTimePosted DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $boardConf['boardID'], $boardConf['threadsPerPage'], $offset );
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $threads[] = new threadClass($boardConf, $row['lastTimePosted'], $row['threadID'], $row['opPostID']);
        }

        $stmt->close();
        return $threads;
    }
    public function getThreadCount($boardConf){
        $count = 0;
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM threads WHERE boardID = ?");
        $stmt->bind_param("i", $boardConf['boardID']);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count;
    }
    public function updateThread($boardConf, $thread) {
        
        $bump = $thread->getLastBumpTime();
        $postID = $thread->getOPPostID();
        $id = $thread->getThreadID();
        $postCount = $thread->getPostCount();
        $stmt = $this->db->prepare("UPDATE threads SET lastTimePosted = ?, opPostID = ? WHERE boardID = ? AND threadID = ?");
        $stmt->bind_param("iiii", $bump, $postID, $boardConf['boardID'], $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }  
    public function deleteThreadByID($boardConf, $threadID) {
        $stmt = $this->db->prepare("DELETE FROM threads WHERE boardID = ? AND threadID = ?");
        $stmt->bind_param("ii", $boardConf['boardID'], $threadID);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    
}
