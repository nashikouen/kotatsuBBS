<?php
/*
 *  DO NOT DELETE THIS FILE OR TRY TO MAKE A conf.php FILE FROM THIS
 *  install.php will use this file to create the proper files. pleas edit stuff you wish to have global before installing
 *  or update the conf.php after it is installed, [note after it is installed you loses these comments in the new file]
 */
return [
    /*
     * this is where you put in the creds for you main data base
     */
    'mysqlDB' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'username' => 'kodomo',
        'password' => 'kodomo',
        'databaseName' => 'boarddb', 
    ],

    /* this is how long a log in session last for. (1 hour) */
    'sessionLifeTime' => 3600,
    'memoryLimit' => '128M',    // the ammount of memeory kotatsu can use 
    'webRootPath' => '/',       // the location where the software is found relitive to your web root.

    // the salt for a site wide secure tripcode and logging in. [note] this code below will be evaluated and saved apon install.
    'tripcodeSalt' => substr(str_replace('+', '.', base64_encode(random_bytes(6))), 0, 8), 
    
    /* these list will hold a list of authed users, format would look like this [[hash, name], [hash, name]] */
    'janitorHashes' => [],      // list of janitor hashes
    'moderatorHashes' => [],    // list of moderator hashes
    'adminHashes' => [],        // board owner hashes

    /* this is to catagorize wether a file has a known drawing method in html */
    /* flash files are a odd ball and enabled by defualt */
    'IMAGE_EXTENTIONS' => ["png", "jpg", "jpeg", "webp", "gif", "tiff", "svg"],
    'VIDEO_EXTENTIONS' => ["mp4", "webm", "avi", "mov", "mkv"],
    'AUDIO_EXTENTIONS' => ["mp3", "wav", "flac", "ogg"],

    /*
     * its best to keep logs just outside of the web path.
     * so a place like this. /var/www/logs
     * this will also atempt to create the logs dir if it dose not exist.
     */
    'logDir' => '../logs/',
];

