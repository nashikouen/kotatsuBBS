<?php
enum roles{
    case superAdmin;
    case superModerator;
    case superJanitor;
    case Admin;
    case Moderator;
    case Janitor;
    case noAuth; // user was never logged in.
}

class AuthClass {
    public function __construct() {}
    public function __clone() {}
    public function __wakeup() { throw new Exception("Unserialization of AuthClass instances is not allowed."); }

    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new AuthClass();
        }
        return self::$instance;
    }
    private function isHashInArray($hash, $array) {
        foreach ($array as $item) {
            if ($item[0] === $hash) {
                return $item;
            }
        }
        return null;
    }
    private function getRole() {
        return $_SESSION['authRole'] ?? roles::noAuth; // Default to noAuth if not set
    }
    private function getBaordID(){
        return $_SESSION['authBoardID'] ?? -1; // Default to noAuth if not set
    }
    public function setRoleByHash($hash, $boardID = null) {
        global $globalConf;

        /* serch for hash in super log ins */
        $matchedItem = $this->isHashInArray($hash, $globalConf['adminHashes']);
        if ($matchedItem) {
            $_SESSION['authRole'] = roles::superAdmin;
            $_SESSION['authName'] = $matchedItem[1];
            return;
        }

        $matchedItem = $this->isHashInArray($hash, $globalConf['moderatorHashes']);
        if ($matchedItem) {
            $_SESSION['authRole'] = roles::superModerator;
            $_SESSION['authName'] = $matchedItem[1];
            return;
        }

        $matchedItem = $this->isHashInArray($hash, $globalConf['janitorHashes']);
        if ($matchedItem) {
            $_SESSION['authRole'] = roles::superJanitor;
            $_SESSION['authName'] = $matchedItem[1];
            return;
        }

        /* serch for hash in non super log ins now */
        $boardConf = [];
        if (!is_null($boardID)) {
            $boardConf = getBoardConfByID($boardID);
        }

        $matchedItem = $this->isHashInArray($hash, $boardConf['adminHashes']);
        if ($matchedItem) {
            $_SESSION['authRole'] = roles::Admin;
            $_SESSION['authBoardID'] = $boardID;
            $_SESSION['authName'] = $matchedItem[1];
            return;
        }

        $matchedItem = $this->isHashInArray($hash, $boardConf['moderatorHashes']);
        if ($matchedItem) {
            $_SESSION['authRole'] = roles::Moderator;
            $_SESSION['authBoardID'] = $boardID;
            $_SESSION['authName'] = $matchedItem[1];
            return;
        }

        $matchedItem = $this->isHashInArray($hash, $boardConf['janitorHashes']);
        if ($matchedItem) {
            $_SESSION['authRole'] = roles::Janitor;
            $_SESSION['authBoardID'] = $boardID;
            $_SESSION['authName'] = $matchedItem[1];
            return;
        }

        /* no has = no role */
        $_SESSION['authRole'] = roles::noAuth;
    }
    public function isSuper() {
        $role = $_SESSION['authRole'];
        if($role == roles::superAdmin || $role == roles::superJanitor || $role == roles::superModerator){
            return true;
        }
        return false;
    }
    public function isAdmin($boardID=-1) {
        return  ($this->getRole() == roles::Admin  &&  $_SESSION['authBoardID'] == $boardID) || 
                ($this->getRole() == roles::superAdmin );
        
    }
    public function isModerator($boardID=-1) {
        return  ($this->getRole() == roles::Moderator  &&  $_SESSION['authBoardID'] == $boardID) || 
                ($this->getRole() == roles::superModerator );
    }
    public function isJanitor($boardID=-1) {
        return  ($this->getRole() == roles::Janitor  &&  $_SESSION['authBoardID'] == $boardID) || 
                ($this->getRole() == roles::superJanitor );
    }
    public function isNotAuth() {
        return $this->getRole() == roles::noAuth;
    }
    public function isAuth($boardID=-1){
        return $this->isJanitor($boardID) ||  $this->isModerator($boardID) ||  $this->isAdmin($boardID);
    }
    public function getName(){
        return $_SESSION['authName'];
    }
    public function __toString(){
        return $this->getRole()->name;
    }
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}