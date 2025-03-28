<?php

$baseDir = __DIR__;
$installFile = $baseDir . '/install.php';
$configFile = $baseDir . '/conf.php';
$bypassFile = $baseDir . '/.install_bypass';

// Installer enforcement
if (!file_exists($bypassFile)) {
    if (file_exists($installFile)) {
        require $installFile;

        // If conf.php was created, generate bypass marker
        if (file_exists($configFile)) {
            file_put_contents($bypassFile, "installed: " . date('c'));
        }
        exit;
    }
}

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($requestPath, '/');

// Route matching
if (preg_match('#^([a-zA-Z0-9_]+)/?$#', $path, $m)) {
    $_GET['boardNameID'] = $m[1];
    require 'bbs.php';

} elseif (preg_match('#^([a-zA-Z0-9_]+)/thread/([0-9]+)/?$#', $path, $m)) {
    $_GET['boardNameID'] = $m[1];
    $_GET['thread'] = $m[2];
    require 'bbs.php';

} elseif (preg_match('#^([a-zA-Z0-9_]+)/([0-9]+)/?$#', $path, $m)) {
    $_GET['boardNameID'] = $m[1];
    $_GET['page'] = $m[2];
    require 'bbs.php';

} elseif (preg_match('#^([a-zA-Z0-9_]+)/catalog/?$#', $path, $m)) {
    $_GET['boardNameID'] = $m[1];
    $_GET['action'] = 'catalog';
    require 'bbs.php';

} elseif (preg_match('#^([a-zA-Z0-9_]+)/admin/?$#', $path, $m)) {
    $_GET['boardNameID'] = $m[1];
    require 'admin.php';

} elseif (preg_match('#^([a-zA-Z0-9_]+)/admin/ban/([0-9]+)/?$#', $path, $m)) {
    $_GET['boardNameID'] = $m[1];
    $_GET['action'] = 'banPost';
    $_GET['postID'] = $m[2];
    require 'admin.php';

} elseif (preg_match('#^([a-zA-Z0-9_]+)/admin/edit/?$#', $path, $m)) {
    $_GET['boardNameID'] = $m[1];
    require 'boardEditor.php';
} else {
    http_response_code(404);
    echo "404 Not Found";
    exit;
}
