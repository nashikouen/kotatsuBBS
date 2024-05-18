<?php
/*
 * This is the base config file. new boards created will have this a default.
 */

return [
    'boardID' => -1,                    // do not change this.
    'boardNameID' => 'nothing',         // this is the small name /b/ /unix/ /oekaki/
    'boardTitle' => "no title",         // name you board
    'boardSubTitle' => "no description",// give a small descrtipion
    'boardLogoPath' => "",              //leave blank for no logo, or put in a path to a image.

    //these are the hard coded nav links on each page.
    'navLinksLeft'=> [
        'cgi' => 'https://example.net/cgi-bin/',
        'upload' => 'https://up.example.net/', 
        // just copy this format above to add another link.
    ],
    'navLinksRight'=> [
        'wiki' => 'https://wiki.example.net/'
    ],

    'fileConf' =>[
        'allowedMimeTypes'=> [   
            'image/jpeg',
            'image/png', 
            'image/gif'
            // add more mimetypes here to allow more types of files to be uploaded
        ],
        'maxFileSize'=> 5242880,    // 5mb (remember to edit php's setting to increese the limit)
        'maxFiles'=> 3,             // max amount of files a user can upload
        'compressQuality' => 65,    // amount of compresstion added to thumbnails
        'backgroundColor' => "#f0e0d6",  //thumbnail background color
        'thumNailWidth' => 250,
        'thumNailHight' => 250,
        'allowDuplicateFiles' => false, // allow duplicate files?
    ],

    'staticPath' => "/static/",
    'defaultCSS' => '/static/css/default.css',  //change this to use ur own css. make sure to update backgroundColor to match post reply background color 
    'defaultFavicon' => '/static/image/favicon.png', 

    'unlisted' => true,             // this will only hide your board from the nav bar. [NOTE] new boards wont respect this by defualt. you must explicitly relist your board when creating or after creation.
    'timeZone' => 'UTC',            // time zone you want your board to opporate in
    'allowRuffle' => false,         // setting this to true will add the Ruffle js script to your board. this allows flash files to be played. you will also need to enable allowJS
    'allowJS' => true,              // this will enbable js on the board. if you want ruffle you need this enabled too.

    'cookieExpireTime'=> 7*24*60*60,    //the day the cookie will expire. default is 7days from the curent time

    'threadsPerPage' => 15,         // this is how many threads will be showed per page
    'maxActiveThreads' => 150,      // this is how many threads can be active at once
    'postPerThreadListing' => 5,    // this is how many post will be shown wh
    'postUntilCantBump' => 150,     // max number of post untill thread can be bumped any more
    'timeUntilCantBump' => 7*24*60*60, // this is the number of days untill a thread cant be bumped anymore

    'maxCommentSize' => 2048,       // this is how many characters are allowed in your comment. there is no limit in the DB

    'postMustHaveFileOrComment' => true,    // this can be over ruled by require file or require comment
    'opMustHaveFile' => true,
    'requireName' => false,
    'requireEmail' => false,
    'requireSubject' => false,
    'requireComment' => false,
    'requireFile' => false,
    'defaultName'=> 'Anonymous', 
    'defaultEmail'=> '', 
    'defaultSubject'=> '',
    'defaultComment'=> '', 

    'canTripcode' => false,         // true will allow you to tripcode
    'canFortune' => false,
    'fortunes' => ['Very bad luck', 'Bad luck', 'Average luck', 'Good luck', 'Godly luck'], 

    'allowQuoteLinking'=> true,     // allow quoting a postid to create a hyperlink to the post
    'autoEmbedLinks'=> true,        // allow converting all links to hyperlinks

    'drawFooter' => true,
];