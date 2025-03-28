<?php

class boardClass
{
	public int $boardID = 0;
	public int $lastPostID = 0;
	public string $boardNameID = '';
	public array $conf = [];
	private array $threads = [];
	private bool $threadsFullyLoaded = false;
	private $repo;

	public function __construct(int $boardID = 0, int $lastPostID = 0, array $conf = [])
	{
		$this->boardID = $boardID;
		$this->lastPostID = $lastPostID;

		// Load base defaults if nothing passed
		$default = require __DIR__ . '/../baseBoardConfig.php';
		$this->conf = array_replace_recursive($default, $conf);

		if (isset($this->conf['boardNameID'])) {
			$this->boardNameID = $this->conf['boardNameID'];
		}

		$this->repo = ThreadRepoClass::getInstance();

		// Optional: set timezone if config provides one
		if (isset($this->conf['timeZone'])) {
			date_default_timezone_set($this->conf['timeZone']);
		}
	}
	public function getThreads()
	{
		if ($this->threadsFullyLoaded === false) {
			$this->threads = $this->repo->loadThreads($this->conf);
			$this->threadsFullyLoaded = true;
		}
		return $this->threads;
	}

	public function getThreadByID($threadID)
	{
		if (!isset($this->threads[$threadID])) {
			$this->threads[$threadID] = $this->repo->loadThreadByID($this->conf, $threadID);
		}
		return $this->threads[$threadID];
	}


	public function getBoardID(): int
	{
		return $this->boardID;
	}

	public function setBoardID(int $id): void
	{
		$this->boardID = $id;
	}

	public function getLastPostID(): int
	{
		return $this->lastPostID;
	}

	public function setLastPostID(int $id): void
	{
		$this->lastPostID = $id;
	}

	public function getBoardNameID(): string
	{
		return $this->boardNameID;
	}

	public function getConf(): array
	{
		return $this->conf;
	}

	public function setConf(array $conf): void
	{
		$this->conf = $conf;
		$this->conf['boardID'] = $this->boardID;
		$this->boardNameID = $conf['boardNameID'];
	}

	public function prune()
	{
		$maxActiveThreads = $this->conf['maxActiveThreads'] ?? 150;
		$maxArchivedThreads = $this->conf['maxArchivedThreads'] ?? 150;
		$totalAllowedThreads = $maxActiveThreads + $maxArchivedThreads;

		$THREADREPO = ThreadRepoClass::getInstance();
		$count = $THREADREPO->getThreadCount($this->conf);

		if ($count > $totalAllowedThreads) {
			$threadIDs = $THREADREPO->fetchThreadIDsForDeletion($this->conf, $totalAllowedThreads);
			$THREADREPO->deleteThreadByID($this->conf, $threadIDs);

			foreach ($threadIDs as $threadID) {
				deleteFilesInThreadByID($threadID);
			}
		}

		$THREADREPO->archiveOldThreads($this->conf, $maxActiveThreads);
	}

}
