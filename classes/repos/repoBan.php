<?php
require_once  __DIR__ .'/DBConnection.php';
require_once  __DIR__ .'/interfaces.php';
require_once __DIR__ .'/../../lib/common.php';

class BanRepoClass{
    private function __clone() {}
    public function __wakeup() { throw new Exception("Unserialization of AuthClass instances is not allowed.");}
    private $db;
    private static $instance = null;

    private function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new BanRepoClass();
        }
        return self::$instance;
    }


    public function banIP($boardID, $ip, $reason, $expireTime, $isRangeBaned=false, $isGlobal=false, $isPublic=false, $category="none"){
        global $globalConf;

        $time = time();
        $range = "0";
        if($isGlobal){
            $boardID = 0;
        }
        if($isRangeBaned){
            $ipParts = explode('.', $ip);
            $range = $ipParts[0] . '.' . $ipParts[1] . '.' . $ipParts[2];
        }

        $insertQuery = "INSERT INTO ipBans (boardID, ipAddress, ipRange, reason, category, createdAt, expiresAt, isPublic) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($insertQuery);
        $stmt->bind_param("issssiii", $boardID, $ip, $range, $reason, $category, $time, $expireTime, $isPublic);
        $stmt->execute();

        return true;
    }
    public function banFile($boardID, $fileHash, $reason, $isPreceptual=false, $isGlobal=false, $isPublic=false, $category="none"){
        global $globalConf;

        $time = time();
        if($isGlobal){
            $boardID = 0;
        }

        $insertQuery = "INSERT INTO fileBans (boardID, fileHash, isPerceptual, reason, category, isPublic, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($insertQuery);
        $stmt->bind_param("isissii", $boardID, $fileHash, $isPreceptual, $reason, $category, $isPublic, $time);
        $stmt->execute();

        return true;
    }
    public function banDomain($boardID, $domain, $reason, $isGlobal=false, $isPublic=false, $category="none"){
        global $globalConf;

        $time = time();
        if($isGlobal){
            $boardID = 0;
        }

        $insertQuery = "INSERT INTO stringBans (boardID, bannedString, reason, category, isPublic, createdAt) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($insertQuery);
        $stmt->bind_param("isssii", $boardID, $domain, $reason, $category, $isPublic, $time);
        $stmt->execute();

        return true;
    }

    public function isIpBanned($boardID, $ip, $isPreceptual=false) {
        $ipParts = explode('.', $ip);
        $range = $ipParts[0] . '.' . $ipParts[1] . '.' . $ipParts[2];

        $query = "SELECT * FROM ipBans WHERE (boardID = ? OR boardID = 0) AND (ipAddress = ? OR ipRange = ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iss", $boardID, $ip, $range);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if ($this->checkExpiration($row['expiresAt'])) {
                return true;
            }
        }

        return false;
    }
    public function isFileBanned($boardID, $fileHash, $isPreceptual=false) {
        $query = "SELECT * FROM fileBans WHERE (boardID = ? OR boardID = 0) AND fileHash = ? AND isPerceptual = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("isi", $boardID, $fileHash, $isPreceptual);
        $stmt->execute();
        $result = $stmt->get_result();


        if ($result->num_rows > 0) {
            $stmt->close();
            return true;
        }

        $stmt->close();

        return false;
    }
    public function isDomainBanned($boardID, $domain) {
        $query = "SELECT * FROM stringBans WHERE (boardID = ? OR boardID = 0) AND bannedString = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("is", $boardID, $domain);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt->close();
            return true;
        }

        $stmt->close();

        return false;
    }

    private function checkExpiration($expiresAt) {
        if ($expiresAt === null) {
            return true;
        }
        return time() < $expiresAt;
    }
}