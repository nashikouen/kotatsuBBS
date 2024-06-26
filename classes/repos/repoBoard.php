<?php
require_once  __DIR__ .'/DBConnection.php';
require_once  __DIR__ .'/interfaces.php';
require_once __DIR__ .'/../board.php';
require_once __DIR__ .'/../../lib/common.php';

class BoardRepoClass implements BoardRepositoryInterface {
    // this is a singleton.
    // these functions should be disabled. and getInstance should be used insted.
    private function __clone() {}
    public function __wakeup() { throw new Exception("Unserialization of AuthClass instances is not allowed.");}

    private $loadedBoards;
    private $db;
    private static $instance = null;
    private function __construct() {
        $this->db = DatabaseConnection::getInstance(); 
    }
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new BoardRepoClass();
        }
        return self::$instance;
    }
    public function updateBoard($board) {
        $path = $board->getConfPath();
        $lastID = $board->getLastPostID();
        $id = $board->getBoardID();
        $stmt = $this->db->prepare("UPDATE boards SET configPath = ?, lastPostID = ? WHERE boardID = ?");
        $stmt->bind_param("sii", $path, $lastID, $id );
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    public function loadBoards() {
        $boards = [];
        $query = "SELECT * FROM boards";
        $result = $this->db->query($query);
    
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $boards[] = new boardClass($row['configPath'], $row['boardID']);
            }
        }
        foreach ($boards as $board){
            $id = $board->getBoardID();
            $this->loadedBoards[$id] = $board;
        }
        return $boards;
    }
    public function loadBoardByID($boardID) {
        if($this->loadedBoards !== null  && $this->loadedBoards[$boardID] == false){
            return $this->loadedBoards[$boardID];
        }
        $stmt = $this->db->prepare("SELECT * FROM boards WHERE boardID = ?");
        $stmt->bind_param("i", $boardID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($row = $result->fetch_assoc()) {
            $board = new boardClass($row['configPath'], $row['boardID']);
            $stmt->close();
            return $board;
        } else {
            $stmt->close();
            return null;
        }
    }
    public function deleteBoardByID($boardID) {
        $stmt = $this->db->prepare("DELETE FROM boards WHERE boardID = ?");
        $stmt->bind_param("i", $boardID);
        $success = $stmt->execute();
        $stmt->close();
        $this->loadedBoards[$boardID] = null;
        return $success;
    }
    public function createBoard($board) {
        try {
            // Fetch configuration path from the board object
            $conf = $board->getConfPath();
            $lastPostID = 0;  // Initialize lastPostID to 0
    
            // Prepare SQL statement with lastPostID
            $stmt = $this->db->prepare("INSERT INTO boards (configPath, lastPostID) VALUES (?, ?)");
            $stmt->bind_param("si", $conf, $lastPostID);  // 's' for string, 'i' for integer
    
            // Execute the statement
            $success = $stmt->execute();
            if ($success) {
                // Set the board ID on the board object to the newly generated ID
                $board->setBoardID($this->db->insert_id);
                $board->setLastPostID($lastPostID);
            } else {
                throw new Exception("Failed to create new board");
            }
    
            // Close the statement
            $stmt->close();
            $this->loadedBoards[$board->getBoardID()] = $board;
            return $success;
        } catch (Exception $e) {
            // Log the error and execute the error callback
            error_log($e->getMessage());
            return false;
        }
    }  
}
