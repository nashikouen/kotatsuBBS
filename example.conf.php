<?php

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
    'passwordSalt' => 'qwerty', // this is the salt used for anything a mod or admin needs for logging in.
    'tripcodeSalt' => 'uiop[]', // the salt for a site wide secure tripcode.

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

