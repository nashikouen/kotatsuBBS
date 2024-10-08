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

    /* if you are preseptualy banning files. this will be the hamming used. 0 would be a prefect 200 would be a totaly diffrent image. */
    'hamming' => 10,
    /*
     *  file object will be stored relitive to threads dir in the project.
     *  you should give the absolute path to the threads folder here.
     *  openbsd guys are /htdocs/kotatsuBBS/threads/
     */
    'threadsDir' => '/var/www/html/kotatsuBBS/threads/',
    /* this is how long a log in session last for. (1 hour) */
    'sessionLifeTime' => 3600,
    'memoryLimit' => '128M',    // the ammount of memeory kotatsu can use 
    'webRootPath' => '/',       // the location where the software is found relitive to your web root.
    'domain' => 'example.com',  // doamin of ur site
    'webhook' => '',            // discord webhook
    'isOpenBSD' => false,       // webhooks dont curetly work on openbsd, i am not sure how to get it working... ffmpeg command is difrent on openbsd too.

    // the salt for a site wide secure tripcode and logging in. [note] this code below will be evaluated and saved apon install.
    'tripcodeSalt' => substr(str_replace('+', '.', base64_encode(random_bytes(6))), 0, 8), 
    
    /* 
     * these list will hold a list of authed users, format would look like this [[hash, name], [hash, name]] 
     * these use the secure tripcode hash for names.      
     */
    'janitorHashes' => [],      // list of janitor hashes
    'moderatorHashes' => [],    // list of moderator hashes
    'adminHashes' => [],        // board owner hashes

    /* this is to catagorize wether a file has a known drawing method in html */
    /* flash files are a odd ball and enabled by defualt */
    'IMAGE_EXTENTIONS' => ["png", "jpg", "jpeg", "webp", "gif", "tiff", "svg"],
    'VIDEO_EXTENTIONS' => ["mp4", "webm", "avi", "mov", "mkv"],
    'AUDIO_EXTENTIONS' => ["mp3", "wav", "flac", "ogg"],

    /*
     * its best to keep logs just outside of the webroot.
     * it will create a dir called kotatsuLog in the dir below and store its logs there.
     *      ^^(you might have to make it manualy and give webserver premistions to it)
     * if you are keeping it in the web root. make sure you hide the logs from web srever.
     */
    'logDir' => '/var/www/bbsLog/',
];

