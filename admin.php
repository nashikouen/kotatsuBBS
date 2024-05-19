<?php

include __DIR__ .'/includes.php';

require_once __DIR__ .'/classes/html.php';
require_once __DIR__ .'/classes/auth.php';

require_once __DIR__ .'/lib/common.php';
require_once __DIR__ .'/lib/adminControl.php';

$boardID = $_POST['boardID'] ?? @nameIDToBoardID($_GET['boardNameID']) ?? '';

if (!is_numeric($boardID)) {
	drawErrorPageAndDie("you must have a boardID");
}

