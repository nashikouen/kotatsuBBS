<?php
if(file_exists(__DIR__ . "/conf.php")){
    echo "it seems this has already been installed. if this is a issue, go to a already created board, or delete conf.php and run this php file again";
    die();
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>KotatsuBBS Installer</title>
        <link class="linkstyle" rel="stylesheet" type="text/css" href="static/css/default.css" title="defaultcss">
	</head>
	<body>
<?php 
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	$conf = require __DIR__ .'/templet.conf.php'; 
	
	function createDB($conn){
		// SQL to create tables
		$sqlCommands = [
			"CREATE TABLE IF NOT EXISTS boards (
				boardID INT AUTO_INCREMENT PRIMARY KEY,
				configPath VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
				lastPostID INT
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
		
			"CREATE TABLE IF NOT EXISTS posts (
				UID INT AUTO_INCREMENT PRIMARY KEY,
				postID INT NOT NULL,
				boardID INT NOT NULL,
				threadID INT NULL,
				password VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
				name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
				email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
				subject VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
				comment TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
				ip VARCHAR(45) NOT NULL,
				postTime BIGINT NOT NULL,
				special TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
				FOREIGN KEY (boardID) REFERENCES boards(boardID) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

			"CREATE TABLE IF NOT EXISTS threads (
				threadID INT AUTO_INCREMENT PRIMARY KEY,
				boardID INT NOT NULL,
				lastTimePosted BIGINT NOT NULL,
				opPostID INT,
                status VARCHAR(10) DEFAULT 'active',
				FOREIGN KEY (boardID) REFERENCES boards(boardID) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

			"CREATE TABLE IF NOT EXISTS files (
                fileID INT AUTO_INCREMENT PRIMARY KEY,
                postID INT NOT NULL,
                threadID INT NOT NULL,
                boardID INT NOT NULL,
                fileName VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                filePath VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                md5 CHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                FOREIGN KEY (threadID) REFERENCES threads(threadID) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (boardID) REFERENCES boards(boardID) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS ipBans (
                banID INT AUTO_INCREMENT PRIMARY KEY,
                ipAddress VARCHAR(45),
                ipRange VARCHAR(45),
                boardID INT NULL,
                reason TEXT,
                category VARCHAR(20),
                isPublic BOOLEAN DEFAULT 0,
                createdAt BIGINT NOT NULL,
                expiresAt BIGINT NULL,
                INDEX idx_ip (ipAddress, ipRange),
                INDEX idx_board (boardID)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS fileBans (
                banID INT AUTO_INCREMENT PRIMARY KEY,
                fileHash VARCHAR(64),
                isPerceptual BOOLEAN,
                reason TEXT,
                category VARCHAR(20),
                boardID INT NULL,
                isPublic BOOLEAN DEFAULT 0,
                createdAt BIGINT NOT NULL,
                INDEX idx_file_hash (fileHash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

            "CREATE TABLE IF NOT EXISTS stringBans (
                banID INT AUTO_INCREMENT PRIMARY KEY,
                bannedString TEXT,
                reason TEXT,
                boardID INT NULL,
                category VARCHAR(20),
                isPublic BOOLEAN DEFAULT 0,
                createdAt BIGINT NOT NULL,
                INDEX idx_banned_string (bannedString(255))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
		];
		// Execute each SQL command
		foreach ($sqlCommands as $sql) {
			if ($conn->query($sql) === TRUE) {
				echo "Table created successfully<br>";
			} else {
				throw new Exception("Error creating table: ". $conn->error);
			}
		}
	}
	
	function updateConf(){	
		global $conf;

		//set configs from postData
		$conf['mysqlDB']['host']			= $_POST['host'];
		$conf['mysqlDB']['port']			= $_POST['port'];
		$conf['mysqlDB']['username'] 		= $_POST['username'];
		$conf['mysqlDB']['password'] 		= $_POST['password'];
		$conf['mysqlDB']['databaseName']	= $_POST['databaseName'];

		//formate and write new config to file
		//$newConfig = '<?php' . PHP_EOL . '// conf.php' . PHP_EOL . 'return ' . var_export($config, true) . ';' . PHP_EOL;
		//file_put_contents('conf.php', $newConfig);

		$newConf = '<?php return ' . var_export($conf, true) . ';';
		if (file_put_contents('conf.php', $newConf) === false) {
			echo "Failed to write configuration.";
		}
	}
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		echo "<div class=\"postblock\">";
		updateConf();
		try {
			$connection = new mysqli($_POST['host'], $_POST['username'], $_POST['password'], $_POST['databaseName']);
			createDB($connection);
			echo "database Successfully set up!<br>";
			$connection->close();

			// these creds should be correct as creating the db sets them.
			// as board repo and stuff needs the creds w
			require_once __DIR__ .'/lib/adminControl.php';
			require_once __DIR__ .'/classes/repos/repoPost.php'; 
			require_once __DIR__ .'/classes/repos/repoThread.php';

			$POSTREPO = PostRepoClass::getInstance();
			$THREADREPO = ThreadRepoClass::getInstance();
			$time = time();
			$board = createBoard("my first board","a place to test stuff", "intro", false);
            
			$thread = new threadClass($board->getConf(), $time);
			$post = new PostDataClass(	$board->getConf(),"System","","HelloWorld!",
										'Thank you for installing kotatsuBBS!!<br> I have put in a lot of work to make this software be as fluent as posible.<br>Please consider fallowing and leaving a star on my <a href="https://github.com/nashikouen/kotatsuBBS">repo</a>. It means a lot.',"",$time,"127.0.0.1",
										1);

			$POSTREPO->createPost($board->getConf(), $post);
			$THREADREPO->createThread($board->getConf(), $thread, $post);
            
            //$post->setThreadID($thread->getThreadID());
            //$POSTREPO->updatePost($conf, $post);

			$post2 = new PostDataClass( $board->getConf(),"System","","check list of things",
										"things you would want to do.<br><br>1. delete the install.php file, that can leak your database creds<br>2. edit /boardConfigs/baseConf.php and set defults (make sure to set your own salts where needed)","",$time + 1,"127.0.0.1",
										1);
			$POSTREPO->createPost($board->getConf(), $post2);


			echo "intro board Successfully created!<br>";
			echo 'delete this install.php file go to <a href="./intro/">intro</a><br>';
		} catch (Exception $e) {
			echo "installation failed. " . $e;
		}
		echo "</div>";
	} 
?>

		<h1>KotatsuBBS Installer</h1>
		<div class="prompt">
			Once you have a MySQL server set up with a basic username, password, and privileges. enter in the credentals below.<br>
			this would also create a <i>conf.php</i> and a board called <i>/intro/</i>. make sure you have proper rewrites in your configs<br>
			<hr>
			<form method="post">
				<div>
					<label for="username">Username*:</label>
					<input type="text" id="username" name="username" value="<?php echo htmlspecialchars($conf['mysqlDB']['username']); ?>">
				</div>
				<div>
					<label for="dbpassword">Password*:</label>
					<input type="text" id="password" name="password" value="<?php echo htmlspecialchars($conf['mysqlDB']['password']); ?>">
				</div>
				<div>
					<label for="host">Domain/ip*:</label>
					<input type="text" id="host" name="host" value="<?php echo htmlspecialchars($conf['mysqlDB']['host']); ?>">
				</div>
				<div>
					<label for="host">port:</label>
					<input type="text" id="port" name="port" value="<?php echo htmlspecialchars($conf['mysqlDB']['port']); ?>">
				</div>
				<div>
					<label for="databaseName">Database name*:</label>
					<input type="text" id="databaseName" name="databaseName" value="<?php echo htmlspecialchars($conf['mysqlDB']['databaseName']); ?>">
				</div>
				<div>
					<button type="submit" name="install">install</button>
				</div>
			</form>
		</div>
	</body>
</html>
