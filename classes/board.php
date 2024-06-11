<?php

require_once __DIR__ .'/post.php';
require_once __DIR__ .'/fileHandler.php';
require_once __DIR__ .'/hook.php';
require_once __DIR__ .'/auth.php';
require_once __DIR__ .'/repos/repoThread.php';
require_once __DIR__ .'/../lib/common.php';


class boardClass{
	private $conf;
	private $confPath;
	private $boardID;
	private $lastPostID;
	private $threads = [];
	private $threadsFullyLoaded = false;
	private $repo;

	public function __construct($confPath, $boardID, $lastPostID = 0){
		$this->confPath = $confPath;
		if (file_exists($confPath)){
			$this->conf = require $confPath;
		}else
		{
			drawErrorPageAndDie("could not load config file for boardID: " . $boardID);
		}
		$this->conf = require $confPath;
        $this->boardID = $boardID;
		$this->lastPostID = $lastPostID;
        $this->repo = ThreadRepoClass::getInstance();
		date_default_timezone_set($this->conf['timeZone']);
	}
	public function getThreads(){
		if($this->threadsFullyLoaded == false){
            $this->threads = $this->repo->loadThreads($this->conf);
            $this->threadsFullyLoaded = true;
        }
        return $this->threads;
	}
	public function getThreadByID($threadID){
        if(!isset($this->threads[$threadID])){
            $this->threads[$threadID] = $this->repo->loadThreadByID($this->conf ,$threadID);
        }
        return $this->threads[$threadID];
	}
	public function getBoardID(){
		return $this->boardID;
	}
    public function prune(){
        $maxActiveThreads = $this->conf['maxActiveThreads'];
        $maxArchivedThreads = $this->conf['maxArchivedThreads'];
        $totalAllowedThreads = $maxActiveThreads + $maxArchivedThreads;

        $THREADREPO = ThreadRepoClass::getInstance();
        $count = $THREADREPO->getThreadCount($this->conf);

        if($count > $totalAllowedThreads){
            $threadIDs = $THREADREPO->fetchThreadIDsForDeletion($this->conf, $totalAllowedThreads);
            $THREADREPO->deleteThreadByID($this->conf, $threadIDs);
            foreach($threadIDs as $threadID){
                deleteFilesInThreadByID($threadID);
            }
        }
        $THREADREPO->archiveOldThreads($this->conf,$maxActiveThreads);
        
    }
	public function setBoardID($boardID){
		$this->boardID = $boardID;
		$this->conf['boardID'] = $boardID;
		$this->updateConfigFile();
	}
	public function setLastPostID($id){
		$this->lastPostID = $id;
	}
	public function getConf(){
		return $this->conf;
	}
	public function setConf($conf){
		$this->conf = $conf;
		$this->updateConfigFile();
	}
	public function getConfPath(){
		return $this->confPath;
	}
	public function getLastPostID(){
		return $this->lastPostID;
	}
	public function deleteThreadByID(){
		
	}
	private function updateConfigFile(){
		$newConf = '<?php return ' . var_export($this->conf, true) . ';';
		if (file_put_contents($this->confPath, $newConf) === false) {
			drawErrorPageAndDie("Failed to write configuration.");
		}
	}
}