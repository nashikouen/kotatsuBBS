<?php
require_once __DIR__ .'/file.php';
require_once __DIR__ .'/auth.php';
require_once __DIR__ .'/../lib/postMagic.php';
require_once __DIR__ .'/../lib/common.php';
require_once __DIR__ .'/repos/repoFile.php';

class PostDataClass {
    private int $postID;//postID 
    private int $threadID;
    private array $files = [];//file objects
    private string $password;//post password
    private string $name;//name
    private string $email;//email
    private string $subject;//subject
    private string $comment;//comment
    private int $unixTime;//time posted
    private string $IP;//poster's ip
    private string $special;//special things like. auto sage, locked, animated gif, etc. split by a _thing_
    private $config;

    private $fileRepo;
    private $isFilesFullyLoaded;
	public function __construct(array $config, string $name, string $email, string $subject, 
                                string $comment, string $password, int $unixTime, string $IP, 
                                int $threadID=-1, int $postID=-1, string $special='') {

        $this->config = $config;
		$this->name = $name;
		$this->email = $email;
		$this->subject = $subject;
		$this->comment = $comment;
        $this->password = $password;

        $this->unixTime = $unixTime;
        $this->IP = $IP;
        $this->postID = $postID;
        $this->threadID = $threadID;
        $this->special = $special;
        $this->fileRepo = FileRepoClass::getInstance();

    }
    public function __toString() {
        return  "BoardID: {$this->config['boardID']}\n" .
                "Name: {$this->name}\n" .
                "Email: {$this->email}\n" .
                "Subject: {$this->subject}\n" .
                "Comment: {$this->comment}\n" .
                "Password: {$this->password}\n" .
                "Unix Time: {$this->unixTime}\n" .
                "IP Address: {$this->IP}\n" .
                "Post ID: {$this->postID}\n" .
                "Thread ID: {$this->threadID}\n" .
                "Special Info: {$this->special}";
    }
    public function validate(){
        if (mb_strlen($this->name, 'UTF-8') > MAX_INPUT_LENGTH ){
            drawErrorPageAndDie("your post's name is invalid. max size: ".MAX_INPUT_LENGTH);
        }  
        if (mb_strlen($this->email, 'UTF-8') > MAX_INPUT_LENGTH){
            drawErrorPageAndDie("your post's email is invalid. max size : ".MAX_INPUT_LENGTH);

        }
        if (mb_strlen($this->subject, 'UTF-8') > MAX_INPUT_LENGTH){
            drawErrorPageAndDie("your post's subject is invalid. max size: ".MAX_INPUT_LENGTH);
        }
        if (mb_strlen($this->comment, 'UTF-8') > $this->config['maxCommentSize']){
            drawErrorPageAndDie("your post's comment is invalid. max size: ".$this->config['maxCommentSize']);
        }
        if (mb_strlen($this->password, 'UTF-8') > MAX_INPUT_LENGTH_PASSWORD){
            drawErrorPageAndDie("your post's password is invalid. max size: ".MAX_INPUT_LENGTH_PASSWORD);
        }
    }
    public function stripHtml(){
        $this->name = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
		$this->email = htmlspecialchars($this->email, ENT_QUOTES, 'UTF-8');
		$this->subject = htmlspecialchars($this->subject, ENT_QUOTES, 'UTF-8');
		$this->comment = htmlspecialchars($this->comment, ENT_QUOTES, 'UTF-8');
    }
    public function embedLinks(){
        $regexUrl  = '/(https?:\/\/[^\s]+)/';
        $this->comment = preg_replace($regexUrl , '<a href="$1" target="_blank">$1</a>', $this->comment);
    }
    public function addLineBreaks(){
        $this->comment = nl2br($this->comment);
    }
    public function applyTripcode(){
        $nameXpass = splitTextAtTripcodePass($this->name);
        $gc = require __DIR__ . '/../conf.php'; 
        $tripcode = genTripcode($nameXpass[1], $gc['tripcodeSalt']);
        $this->name = $nameXpass[0] . $tripcode;
    }
    public function stripTripcodePass(){
        $nameXpass = splitTextAtTripcodePass($this->name);
        $this->name = $nameXpass[0];
    }
    public function quoteLinks(){
        // I dont want to do all that db querrys!!!ヽ(`Д´)ノ 
    }
    public function isBumpingThread(){
        if(stripos($this->getEmail(),"sage")!== false){//bc 2 was not enough...
            return false;
        }else{
            return true;
        }
    }

    public function addFile(FileDataClass $file) {
        $this->files[] = $file;
    }
    public function addFilesToRepo(){
        foreach($this->files as $file){
            $file->setPostID($this->postID);
            $file->setThreadID($this->threadID);
            $file->setConf($this->config);

            $FILEREPO = FileRepoClass::getInstance();
            $FILEREPO->createFile($this->config, $file);
        }
    }
    public function moveFilesToDir($dir){
        foreach($this->files as $file){
            $file->moveToDir($dir);
        }
    }
    public function getFiles() {
        if($this->isFilesFullyLoaded != true){
            $this->files = $this->fileRepo->loadFilesByPostID($this->config, $this->postID);
            $this->isFilesFullyLoaded = true;
        }
        return $this->files;
    }

    public function getPostID(){
        return $this->postID;
    }
    public function getThreadID(){
        return $this->threadID;
    }
    public function getBoardID(){
        return $this->config['boardID'];
    }
    public function getName(){
        return $this->name;
    }
    public function getEmail(){
        return $this->email;
    }
    public function getSubject(){
        return $this->subject;
    }
    public function getComment(){
        return $this->comment;
    }
    public function getPassword(){
        return $this->password;
    }
    public function getUnixTime(){
        return $this->unixTime;
    }
    public function getIP(){
        return $this->IP;
    }
    public function getSpecial(){
        return $this->special;
    }
    public function getConf(){
        return $this->config;
    }


    public function setPostID($id){
        $this->postID = $id;
    }
    public function setThreadID($id){
        $this->threadID = $id;
    }
    public function setName($name){
        $this->name = $name;
    }
    public function setEmail($email){
        $this->email = $email;
    }
    public function setSubject($subject){
        $this->subject = $subject;
    }
    public function setComment($comment){
        $this->comment = $comment;
    }
    public function setPassword($password){
        $this->password = $password;
    }
    public function setUnixTime($unixTime){
        $this->unixTime = $unixTime;
    }
    public function setIP($IP){
        $this->IP = $IP;
    }
    public function setSpecial($special){
        $this->special = $special;
    }
}