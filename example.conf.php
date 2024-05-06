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
    'passwordSalt' => 'qwerty', // this is the salt used for anything a mod or admin needs for logging in.

    /*
     * its best to keep logs just outside of the web path.
     * so a place like this. /var/www/logs
     * this will also atempt to create the logs dir if it dose not exist.
     */
    'logDir' => '../logs/',
];

