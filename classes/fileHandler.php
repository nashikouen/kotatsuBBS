<?php
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

require_once __DIR__ .'/repos/repoFile.php';
require_once __DIR__ .'/repos/repoBan.php';


class fileHandlerClass {
    private $config;
    private $disableProssesing;
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function getFilesFromPostRequest(int $maxFiles = null): array {
        $fileConf = $this->config['fileConf'];
        $filesGotten = [];
        $processedFilesCount = 0;
        $fileLimit = $maxFiles ?? $fileConf['maxFiles'];
        $BANREPO = BanRepoClass::getInstance();

        

        if (!isset($_FILES['upfile'])) {
            return $filesGotten;
        }

        // Loop through each file
        foreach ($_FILES['upfile']['error'] as $key => $error) {
            if ($error != UPLOAD_ERR_OK) {
                continue;
            }
            // Stop processing if the maximum number of files has been reached
            if ($processedFilesCount >= $fileLimit) {
                break;
            }
            $tmpName = $_FILES['upfile']['tmp_name'][$key];
            $fileName = $_FILES['upfile']['name'][$key];
            $fileSize = $_FILES['upfile']['size'][$key];

            // Validate file size.
            if ($fileSize > $fileConf['maxFileSize']) {
                echo "Error: File {$fileName} is too large. Maximum file size is ".$fileConf['maxFileSize'];
                continue;
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $realMimeType = $finfo->file($tmpName);

            // Validate file type
            if (!in_array($realMimeType, $fileConf['allowedMimeTypes'])) {
                echo "Error: File {$fileName} type is not allowed.";
                continue; 
            }

            $md5 = md5_file($tmpName);
            if ($BANREPO->isFileBanned(getBoardFromRequest()->getBoardID(), $md5, false)){
                drawErrorPageAndDie("banned file uploaded");
            }

            $filesGotten [] = new fileDataClass($this->config, $tmpName, $fileName, $md5);
            $processedFilesCount++;
        }

        foreach($filesGotten as $file){
            $tmpName = $file->getFilePath();
            
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $realMimeType = $finfo->file($tmpName);

            $fileExtention = getExtensionByMimeType($realMimeType);
            $fileNameOnDisk =  uniqid() . $fileExtention;
            $newFilePath = __DIR__ . "/../threads/staging/" . $fileNameOnDisk;
            move_uploaded_file($tmpName, $newFilePath);
            $file->setFilePath($newFilePath);
        }

        return $filesGotten;
    }

    public function createThumbnail($file, $isOp) {
        // Quality and dimensions settings
        $imgConf = $this->config['fileConf'];

        $quality = $imgConf['compressQuality'];
        $maxWidth = $imgConf['thumNailWidth'];
        $maxHeight = $imgConf['thumNailHight'];
        $backgroundRGB = sscanf($imgConf['backgroundColor'], "#%02x%02x%02x");
        if($isOp == false){
            $maxWidth = (int)$maxWidth / 2;
            $maxHeight = (int)$maxHeight / 2;
        }
    
        $filePath = $file->getFilePath();

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $fileType = $finfo->file($filePath);
    
        if (strpos($fileType, 'image/') === 0) {
            $image = imagecreatefromstring(file_get_contents($filePath));
            
            if (function_exists('exif_read_data')) {
                $exif = exif_read_data($filePath);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $image = imagerotate($image, 180, 0);
                            break;
                        case 6:
                            $image = imagerotate($image, -90, 0);
                            break;
                        case 8:
                            $image = imagerotate($image, 90, 0);
                            break;
                    }
                }
            }

            $width = imagesx($image);
            $height = imagesy($image);
    
            $newWidth = $width;
            $newHeight = $height;
    

            if ($width > $maxWidth || $height > $maxHeight) {
                $aspectRatio = $width / $height;
    
                if ($width > $height) {
                    $newWidth = $maxWidth;
                    $newHeight = $maxWidth / $aspectRatio;
                } else {
                    $newHeight = $maxHeight;
                    $newWidth = $maxHeight * $aspectRatio;
                }
            }
    
            // Create a new image
            $thumbnail = imagecreatetruecolor((int)$newWidth, (int)$newHeight);
            $backgroundColor = imagecolorallocate($thumbnail, $backgroundRGB[0], $backgroundRGB[1], $backgroundRGB[2]);
            imagefill($thumbnail, 0, 0, $backgroundColor);
    
            // Resize the image to the new dimensions
            imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, (int)$newWidth, (int)$newHeight, $width, $height);
    
            // Save the thumbnail to a temporary file
            $thumbnailPath = tempnam(sys_get_temp_dir(), 'thumbnail');
            imagejpeg($thumbnail, $thumbnailPath, $quality);
    
            // Free up memory
            imagedestroy($image);
            imagedestroy($thumbnail);
    
            return $thumbnailPath;
        } elseif (strpos($fileType, 'video/') === 0) {
            global $globalConf;
            $thumbnailPath = tempnam(sys_get_temp_dir(), 'thumbnail') . ".jpg";
            $safeFilePath = escapeshellarg($filePath);

            if($globalConf['isOpenBSD']){
                $videoInfo = shell_exec("LD_LIBRARY_PATH=/usr/local/lib:/usr/X11R6/lib /usr/local/bin/ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=s=x:p=0 {$safeFilePath}");
            }else{
                $videoInfo = shell_exec("ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of csv=s=x:p=0 {$safeFilePath}");
            }
            
            // Get video dimensions
            list($width, $height) = explode('x', $videoInfo);
    
            // Calculate new dimensions while maintaining aspect ratio
            $newWidth = $width;
            $newHeight = $height;
    
            if ($width > $maxWidth || $height > $maxHeight) {
                $aspectRatio = $width / $height;
    
                if ($width > $height) {
                    $newWidth = $maxWidth;
                    $newHeight = $maxWidth / $aspectRatio;
                } else {
                    $newHeight = $maxHeight;
                    $newWidth = $maxHeight * $aspectRatio;
                }
            }

            if($globalConf['isOpenBSD']){
                $ffmpegCommand = "LD_LIBRARY_PATH=/usr/local/lib:/usr/X11R6/lib /usr/local/bin/ffmpeg -i {$safeFilePath} -vframes 1 -vf scale={$newWidth}:{$newHeight} -q:v 2 -y {$thumbnailPath} 2>&1";
            }else{
                $ffmpegCommand = "ffmpeg -i {$safeFilePath} -vframes 1 -vf scale={$newWidth}:{$newHeight} -q:v 2 -y {$thumbnailPath} 2>&1";
            }
            exec($ffmpegCommand);
    
            return $thumbnailPath;
        }
    
        // Unsupported file type
        return null;
    }    
    public function procssesFiles($files, $isOp){
        $fileConf = $this->config['fileConf'];
        $FILEREPO = FileRepoClass::getInstance();
        $BANREPO = BanRepoClass::getInstance();
        $filesProcssed = [];
        
        if($fileConf['doPreseptualBan'] == true){
            $bannedHashes = $BANREPO->getAllPerceptualHashes();
            $hasher = new ImageHash(new DifferenceHash());
        }

        foreach ($files as $file) {

            if($fileConf['doPreseptualBan'] == true){
                $preseptualHash = $hasher->hash($file->getFilePath());
                $globalConf = require __DIR__ ."/../conf.php";


                foreach($bannedHashes as $badHash){
                    $hamming = $hasher->distance($badHash, $preseptualHash);
                    if ($hamming <= $globalConf['hamming']) { 
                        foreach ($files as $bfile) {
                            unlink($bfile->getFilePath());
                        }
                        drawErrorPageAndDie("bad file detected... fukkin saved!");
                    }
                }
            }
            
            $md5Hash = $file->getMD5();

            // a good moduel would be deduplication. 
            if($fileConf['allowDuplicateFiles'] == false){
                if($FILEREPO->isDuplicateFile($this->config, $md5Hash)){
                    drawErrorPageAndDie("duplicate file detected: ". $file->getFileName());
                    continue;
                }
            }

            $thumbnailPath = $this->createThumbnail($file, $isOp);
            $file->setThumnailPath($thumbnailPath);
            $filesProcssed[] = $file;
        }
        return $filesProcssed;
    }
    public function perceptualHash($filePath) {
        // Load the image and resize to 32x32
        $img = imagecreatefromstring(file_get_contents($filePath));
        $smallImg = imagecreatetruecolor(32, 32);
        imagecopyresized($smallImg, $img, 0, 0, 0, 0, 32, 32, imagesx($img), imagesy($img));
    
        // Convert to grayscale and calculate the average brightness
        $totalBrightness = 0;
        $pixels = [];
        for ($y = 0; $y < 32; $y++) {
            for ($x = 0; $x < 32; $x++) {
                $rgb = imagecolorat($smallImg, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $gray = (int)(($r + $g + $b) / 3);
    
                $pixels[$y][$x] = $gray;
                $totalBrightness += $gray;
            }
        }
        $averageBrightness = $totalBrightness / (32 * 32);
    
        // Generate the hash based on comparing pixel brightness to the average
        $hash = [];
        for ($y = 0; $y < 32; $y++) {
            for ($x = 0; $x < 32; $x++) {
                $hash[] = $pixels[$y][$x] > $averageBrightness ? '1' : '0';
            }
        }
    
        imagedestroy($img);
        imagedestroy($smallImg);
    
        return implode('', $hash); // Return the binary string
    }
    public function calculateHammingDistance($hash1, $hash2) {
        if (strlen($hash1) !== strlen($hash2)) {
            throw new InvalidArgumentException("Hashes must be of the same length");
        }
    
        $distance = 0;
        for ($i = 0; $i < strlen($hash1); $i++) {
            if ($hash1[$i] !== $hash2[$i]) {
                $distance++;
            }
        }
    
        return $distance;
    }

}