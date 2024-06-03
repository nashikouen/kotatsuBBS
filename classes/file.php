<?php

class FileDataClass {
    // move this into its own object.
    private $fileID;
    private $postID;
    private $threadID;
    private $conf;
    
    private string $fileName; //file name
    private string $filePath;//filename as stored on the system
    private $fileSize; //file size
    private string $md5chksum; //file hash
    private $thumnailPath;

    
    public function __construct($conf, string $filePath, string $fileName='noName', string $md5chksum='null', $fileID=-1, $postID=0, $threadID=0) {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->md5chksum = $md5chksum;

        $this->fileID = $fileID;
        $this->conf = $conf;
        $this->postID = $postID;
        $this->threadID = $threadID;
    }

    public function moveToDir($dir){
        // Ensure the directory ends with a slash
        $dir = rtrim($dir, '/') . '/';

        // Check and create the directory if it does not exist
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new Exception("Failed to create directory: $dir");
        }
        
        $fileName = basename($this->filePath);
        $newFilePath = $dir . $fileName;
        if(is_null($this->filePath) == false && file_exists($this->filePath)){
            rename($this->filePath, $newFilePath);
        }else{
            // LOG!!
        }

        $tFileName = "t". pathinfo($this->filePath, PATHINFO_FILENAME) . ".jpg"; 
        $newThumbnailPath = $dir . $tFileName ;
        if(is_null($this->thumnailPath) == false && file_exists($this->thumnailPath)){
            rename($this->thumnailPath, $newThumbnailPath);
        }
        
        // Update the object's file paths
        $this->filePath = $newFilePath;
        $this->thumnailPath = $newThumbnailPath;
    }
    public function setFileSize($s){
        $this->fileSize = $s;
    }
    public function setFilePath($n){
        $this->filePath = $n;
    }
    public function setMD5($m){
        $this->md5chksum = $m;
    }
    public function setFileName($n){
        $this->fileName = $n;
    }
    public function setPostID($id){
        $this->postID = $id;
    }
    public function setThreadID($id){
        $this->threadID = $id;
    }
    public function setConf($c){
        $this->conf = $c;
    }
    public function setFileID($id){
        $this->fileID = $id;
    }
    public function setThumnailPath($path){
        $this->thumnailPath = $path;
    }

    public function getFileSize(){
        return $this->fileSize;
    }
    public function getFilePath(){
        return $this->filePath;
    }
    public function getMD5(){
        return $this->md5chksum;
    }
    public function getFileName(){
        return $this->fileName;
    }
    public function getSizeFormated(){
        if(file_exists($this->filePath) == false){
            return "(?x?)";
        }
        $size = filesize($this->filePath);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($size, 1024));
        $formattedSize = round($size / (1024 ** $i), 2) . ' ' . $units[$i];
    
        // Check if the file is an image
        if (exif_imagetype($this->filePath) !== false) {
            if ($this->getFileExtention() == "swf"){
                return $formattedSize;
            }
            // Get image dimensions if applicable
            $imageSize = getimagesize($this->filePath);
            if ($imageSize !== false && isset($imageSize[0]) && isset($imageSize[1])) {
                $width = $imageSize[0];
                $height = $imageSize[1];
                $formattedSize .= " ({$width}x{$height})";
            }
        }
    
        return $formattedSize;
    }
    public function getStoredTName(){
        //this is so fucking bad....
        if(is_null($this->thumnailPath)){
            return "t".pathinfo($this->filePath, PATHINFO_FILENAME).".jpg"; 
        }
        return basename($this->thumnailPath);
    }
    public function getStoredName(){
        return basename($this->filePath);
    }
    public function getFileExtention(){
        return pathinfo($this->filePath, PATHINFO_EXTENSION);
    }
    public function hasThumbnail(){
        return file_exists($this->getThumbnailPath());
    }
    public function isMissing(){
        return !file_exists($this->filePath);
    }
    public function isSpoiler(){
        return false;
    }

    public function getPostID(){
        return $this->postID;
    }
    public function getThreadID(){
        return $this->threadID;
    }
    public function getConf(){
        return $this->conf;
    }
    public function getFileID(){
        return $this->fileID;
    }
    public function getThumbnailPath(){
        if(is_null($this->thumnailPath)){
            $this->thumnailPath = dirname($this->filePath). "/t".pathinfo($this->filePath, PATHINFO_FILENAME).".jpg";
        }
        return $this->thumnailPath;
    }
}




/*
		if (function_exists('exif_read_data') && function_exists('exif_imagetype')) {
			$imageType = exif_imagetype($dest);
			if ($imageType == IMAGETYPE_JPEG) {
				$exif = @exif_read_data($dest);
				if ($exif !== false) {
					// Remove Exif data
					$image = imagecreatefromjpeg($dest);
					imagejpeg($image, $dest, 100);
					imagedestroy($image);
				}
			}
		}
*/
/*
        // Now $validFiles contains information about all validly uploaded files
        // Process or save these files as needed
        foreach ($validFiles as $file) {
            // For example, moving the file to a permanent directory
            $uploadPath = 'uploads/' . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                echo "The file " . htmlspecialchars($file['name']) . " has been uploaded.";
            } else {
                echo "Error: Failed to save the file " . htmlspecialchars($file['name']) . ".";
            }
        }
*/