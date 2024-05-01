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
    'loginSalt' => 'qwerty', // this is the tripsalt used for anything auth related

    /*
     * its best to keep logs just outside of the web path.
     * so a place like this. /var/www/logs
     */
    //'logDir' => '../logs',
    'auditLog' => 'auditlog.txt',
    /* this is the name of the dir to save files. it must be from the project's root. some may consider a nfs */
    'threadDir' => 'threads',
];

