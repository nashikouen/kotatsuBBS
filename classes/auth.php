<?php
enum roles{
        case Admin;
        case Mod;
        case janitor;
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
    public function getRole() {
        return $_SESSION['role'] ?? roles::noAuth; // Default to noAuth if not set
    }
    public function setRole(roles $role) {
        $_SESSION['role'] = $role;
    }
    public function isAdmin() {
        return $this->getRole() == roles::Admin;
    }
    public function isMod() {
        return $this->getRole() == roles::Mod;
    }
    public function isJanitor() {
        return $this->getRole() == roles::Janitor;
    }
    public function isNotAuth() {
        return $this->getRole() == roles::noAuth;
    }
}