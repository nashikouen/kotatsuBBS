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
    /*
     * its best to keep logs just outside of the web path.
     * so a place like this. /var/www/logs
     */
    //'logDir' => '../logs',
    //'rootPath' => '/',
    'auditLog' => 'auditlog.txt',
    /* this is the name of the dir to save files. it must be in the project's root. */
    'threadDir' => 'threads',
];

