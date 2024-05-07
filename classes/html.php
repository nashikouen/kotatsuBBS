<?php

require_once __DIR__ .'/hook.php';
require_once __DIR__ .'/repos/repoThread.php';
require_once __DIR__ .'/../lib/common.php';


$HOOK = HookClass::getInstance();
$THREADREPO = ThreadRepoClass::getInstance();


class htmlclass {
    private string $html = "";
    private array $conf;
    private boardClass $board;
    public function __construct(array $conf, boardClass $board) {
        $this->conf = $conf;
        $this->board = $board;
    }
    private function drawHead(){
        $this->html .= '
        <!--drawHead() Hello!! If you are looking to modify this webapge. please check out kotatsu github and look in /classes/html.php-->
        <head>
            <!--tell browsers to use UTF-8-->
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <!--always get newest content-->
            <meta http-equiv="cache-control" content="max-age=0">
            <meta http-equiv="cache-control" content="no-cache">
            <meta http-equiv="expires" content="0">
            <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
            <meta http-equiv="pragma" content="no-cache">
            <!--mobile view-->
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <!--tell bots its ok to scrape the whole site. disallowing this wont stop bots FYI-->
            <meta name="robots" content="follow,archive">
            <!--board specific stuff-->
            <title>' . $this->conf['boardTitle'] . '</title>
            <link class="linkstyle" rel="stylesheet" type="text/css" href="'. $this->conf['defaultCSS'] .'" title="defaultcss">
            <link rel="shortcut icon" href="'. $this->conf['defaultFavicon'] .'">';

            if($this->conf['allowRuffle']){
                $this->html .= '<script src="https://unpkg.com/@ruffle-rs/ruffle"></script>';
            }
            
            $this->html .= 
            //'<link rel="alternate" type="application/rss+xml" title="RSS 2.0 Feed" href="//nashikouen.net/main/koko.php?mode=module&amp;load=mod_rss">
        '</head>';
    }
    /*drawNavGroup expects a key,value pair. where key is displayname and value is url*/
    private function drawNavGroup($URLPair){
        //this is what i mean by grouping [ webpage1 / webpage2 / webpage3 / etc.. ]
        $this->html .= "[";
        foreach ($URLPair as $key => $value) {
            $this->html .= '<a class="navLink" href="'.$value.'">'.$key.'</a>';
            $this->html .= "/";
        }
        $this->html = substr($this->html, 0, -1);
        $this->html .= "]";
    }
    private function drawNavBar(){
        global $HOOK;
        $conf = $this->conf;

        $this->html .= '
        <!--drawNavBar()-->
        <div class="navBar">
        <span class="navLeft">';
            $this->drawNavGroup(getBoardListing());
            $this->drawNavGroup($conf['navLinksLeft']);
            $res = $HOOK->executeHook("onDrawNavLeft");// HOOK drawing to left side of nav
            foreach ($res as $urlGroup) {
                $this->drawNavGroup($urlGroup);
            }
            $this->html .= '
        </span>
        <span class="navRight">';
            $res = $HOOK->executeHook("onDrawNavRight");// HOOK drawing to right side of nav
            foreach ($res as $urlGroup) {
                $this->drawNavGroup($urlGroup);
            }
            $this->drawNavGroup($conf['navLinksRight']);
            $this->drawNavGroup(['admin' => 'admin.php?boardID='.$conf['boardID']]);
            $this->html .= '
        </span>
        </div>';
    }
    private function drawBoardTitle(){
        $conf = $this->conf;
        $conf['boardTitle'];
        $conf['boardSubTitle'];
        $conf['boardLogoPath'];
        $this->html .= '
        <!--drawBoardTitle()-->
        <div class="boardTitle">';
        if ($conf['boardLogoPath'] != ""){
            $this->html .= '<img class="logo" src="'.$conf['boardLogoPath'].'">';
        }
        $this->html .= '<h1 class="title">'.$conf['boardTitle'].'</h1>';
        $this->html .= '<h5 class="subtitle">'.$conf['boardSubTitle'].'</h5>
        </div>';
    }
    private function drawFooter(){
        $this->html .= '<br><br><br><center>- you are running <a rel="nofollow noreferrer license" href="https://github.com/nashikouen/KotatsuBBS/" target="_blank">KotatsuBBS</a>. a clear and easy to read image board software -</center>' ;
    }
    private function postManagerWraper($drawFunc, $parameter){
        $this->html .= '
        <!--postManagerWraper()-->
        <form name="managePost" id="managePost" action="'.ROOTPATH.'bbs.php" method="post">';
        $drawFunc($parameter);
        $this->html .= '
            <!--make dropdown with other options-->
            <table align="right">
            <tr>
            <td align="">
			<input type="hidden" name="action" value="deletePosts">
            <input type="hidden" name="boardID" value="'.$this->conf['boardID'].'">

                Delete Post: [<label><input type="checkbox" name="fileOnly" id="fileOnly" value="on">File only</label>]<br>
                Password: <input type="password" name="password" size="16" maxlength="'.MAX_INPUT_LENGTH_PASSWORD.'">
                <input type="submit" value="Submit">
            </td>
            </tr>
            </table>
        </form>';
    }
    private function drawMainFormBody($buttonText){
        $this->html .= '
        <table>
        <tr>
            <td class="accent"><label for="name">Name</label></td>
            <td><input type="text" id="name" name="name" maxlength="'.MAX_INPUT_LENGTH.'"></td>
        </tr>
        <tr>
            <td class="accent"><label for="email">Email</label></td>
            <td>
                <input type="text" id="email" name="email" maxlength="'.MAX_INPUT_LENGTH.'">
            </td>
        </tr>
        <tr>
            <td class="accent"><label for="subject">Subject</label></td>
	        <td><input type="text" id="subject" name="subject" maxlength="'.MAX_INPUT_LENGTH.'">
	            <button type="submit">'.$buttonText.'</button>
	        </td>
        </tr>
        <tr>
            <td class="accent"><label for="comment">Comment</label></td>
            <td><textarea type="text" id="comment" name="comment" cols="48" rows="4" maxlength="'.$this->conf['maxCommentSize'].'"></textarea></td>
        </tr>
        <tr>
            <td class="accent"><label for="files">Files</label></td>
            <td><input type="file" name="upfile[]" multiple></td>
        </tr>
        <tr>
            <td class="accent"><label for="password">Password</label></td>
            <td><input type="text" id="password" name="password" maxlength="'.MAX_INPUT_LENGTH_PASSWORD.'"></td>
        </tr>
        </table>';
    }
    private function drawFormNewThread(){
        $this->html .= '
        <!--drawFormNewThread()-->
        <center id="mainForm">
            <form id="formThread" action="'.ROOTPATH.'bbs.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="postNewThread">
            <input type="hidden" name="boardID" value="'.$this->board->getBoardID().'">';
            $this->drawMainFormBody("New Thread");
            $this->html .= '
            </form>
        </center>';
    }
    private function drawFormNewPost($threadID){
        $this->html .= '
        <!--drawFormNewPost()-->
        <a href="/'.$this->conf['boardNameID'].'/">[Return]</a>
        <center class="theading"><b>Posting mode: Reply</b></center>
        <center id="mainForm">
            <form id="formPost" action="'.ROOTPATH.'bbs.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="postToThread">
            <input type="hidden" name="threadID" value="'.$threadID.'">
            <input type="hidden" name="boardID" value="'. $this->board->getBoardID().'">';
            $this->drawMainFormBody("New Post");
            $this->html .= '
            </form>
        </center>';
    }
    private function drawFiles($files, $threadID){
        $this->html .= '
        <!--drawFiles-->
        <div class="files">
            <div class="fileName">';
                foreach($files as $file){
                    $webLocation = ROOTPATH.'threads/'.$threadID.'/';
                    $this->html .= '
                    [<a href="'.$webLocation. $file->getStoredName().'" download="'. $file->getFileName() .'">
                        download
                    </a>]
                    <small>'. $file->getSizeFormated() .'</small>
                    <a href="'.$webLocation. $file->getStoredName().'" target="_blank" rel="nofollow"> 
                        '. $file->getFileName() .'
                    </a> 
                    <br>';
                }
                $this->html.='
            </div>
            <div class="file">';
                foreach($files as $file){

                    $webLocation = ROOTPATH.'threads/'.$threadID.'/';
                    
                    $thumbnailOnWeb = $webLocation. $file->getStoredTName();
                    $fileOnWeb = $webLocation. $file->getStoredName();
                    $thumbSWF = $this->conf['staticPath'] . "image/flash.png";
                    $noThumnial = $this->conf['staticPath'] ."image/noThumbnail.jpg";

                    if(in_array($file->getFileExtention(), IMAGE_EXTENTIONS)){
                        $this->html .= '
                        <a href="'.$webLocation. $file->getStoredName().'" target="_blank" rel="nofollow">
                            <img src="'.$thumbnailOnWeb.'" title="'.$file->getStoredName().'">
                        </a>';
                    }elseif(in_array($file->getFileExtention(), VIDEO_EXTENTIONS)){
                        $this->html .=
                        '<a href="'.$webLocation. $file->getStoredName().'" target="_blank" rel="nofollow">
                            <img src="'.$thumbnailOnWeb.'" title="'.$file->getStoredName().'">
                        </a>';
                    }elseif($file->getFileExtention() == "swf"){
                        $this->html .=
                        '<a href="'.$webLocation. $file->getStoredName().'" target="_blank" rel="nofollow">
                            <img src="'.$thumbSWF.'" title="'.$file->getStoredName().'">
                        </a>';
                    }elseif(in_array($file->getFileExtention(), AUDIO_EXTENTIONS)){
                        $this->html .= '<audio loading="lazy" controls=""><source src="'.$fileOnWeb.'" type="audio/mpeg"></audio>';
                    }else{
                        $this->html .= '
                        <a href="'.$webLocation. $file->getStoredName().'" target="_blank" rel="nofollow">
                            <img src="'.$noThumnial.'" title="'.$file->getStoredName().'">
                        </a>';
                    }
                        //<img src="'..'" class="postimg" title="Click to show full image">
                    $this->html .= '
                    </a>';
                }
                $this->html .= '
            </div>
        </div>';

    }
    private function drawPosts($thread, $posts, $isListingMode=false ,$omitedPosts=0){
        $this->html .= '
        <!--drawPosts()-->';
        foreach($posts as $post){
            $postID = $post->getPostID();
            $type = "reply";
            $isOP = $postID == $thread->getOPPostID();
            if($isOP){
                $type = "op";
            }
            $threadID = $post->getThreadID();
	        $email = $post->getEmail(); 

            $this->html .= '
            <div class="post '.$type.'" id="'.$postID.'">';
                if($isOP){
                    $this->drawFiles($post->getFiles(), $threadID);
                }
                $this->html .= '
                <div class="postinfo">
                    <input type="checkbox" name="postIDs[]" value="'.$postID.'">
                    <span class="bigger"><b class="subject">'.$post->getSubject().'</b></span>
                    <span class="name">';
                        if($email != ""){
                            $this->html .= '<a href="mailto:'.$email.'"><b>'.$post->getName().'</b></a>';
                        }else{
                            $this->html .= '<b>'.$post->getName().'</b>';
                        }
                        $this->html .= '
                    </span>
                    <span class="time">'.date('Y-m-d H:i:s', $post->getUnixTime()).'</span>
                    <span class="postnum">
				        <a href="/'.$this->conf['boardNameID'].'/thread/'.$threadID.'#p'.$postID.'" class="no">No.</a>
                        <a href="/'.$this->conf['boardNameID'].'/thread/'.$threadID.'#postForm" title="Quote">'.$postID.'</a>
                    </span>';
                    if($isOP  && $isListingMode){
                        $this->html .= '
                        [
                            <a href="/'.$this->conf['boardNameID'].'/thread/'.$threadID.'" class="no">Reply</a>
                        ]';
                    }
                    $this->html .= '
                </div>';
                if($isOP == false){
                    $this->drawFiles($post->getFiles(), $threadID);
                }
                $this->html .= '
                <blockquote class="comment">'.$post->getComment().'</blockquote>';
                if($isOP && $isListingMode && $omitedPosts > 0){
                    $this->html .= '<span class="omittedposts">'.$omitedPosts.' posts omitted. Click Reply to view.</span>';
                }
                $this->html .= '
            </div><br>';
        }
    }
    private function drawThread($thread){
        $posts = $thread->getPosts();

        $this->html .='
        <!--drawThreads()-->
        <div id="t'.$thread->getThreadID().'" class="thread">';
            $this->drawPosts($thread, $posts);
            $this->html .='
        </div>';
    }
    private function drawThreadListing($threads){
        $this->html .='
        <!--drawThreadListing()-->';
        foreach ($threads as $thread) {
            //NEEDS FIXING.
            // if op post and child has same time. there is a cance they will swap positions on thread listing and 2nd post will just be OP post.
            // maybe make a paramitr for just OP and remove op from the drawing list if it exist?
            $posts = $thread->getLastNPost($this->conf['postPerThreadListing']);
            $posts[0] = $thread->getPostByID($thread->getOPPostID());
            $omitedPost = $thread->getPostCount() - sizeof($posts);

            $this->html .='
            <div id="t'.$thread->getThreadID().'" class="thread">';
                $this->drawPosts($thread, $posts, true, $omitedPost);
                $this->html .='
            </div>';
        }
    }
    private function drawPageNumbers($curentPage){
        global $THREADREPO;
        $threadCount = $THREADREPO->getThreadCount($this->conf);
        $maxThreadsPerPage = $this->conf['threadsPerPage'];
        
        $pages = floor($threadCount / $maxThreadsPerPage);
        $this->html .='
        <!--drawPageNumbers()-->
        <div class="pages">';
        if($curentPage > 0){
            $this->html .='
            <a href="/'. $this->conf['boardNameID'] .'/'. 0 .'">&lt;&lt;</a>
            [
            <a href="/'. $this->conf['boardNameID'] .'/'.$curentPage - 1 .'">back</a>
            ]';
        }
        for($i = 0; $i <=$pages ; $i++){
            if ($curentPage == $i){
                $this->html .='
                [
                <b>'.$i.'</b>
                ]';
            }else{
                $this->html .='
                [
                <a href="/'. $this->conf['boardNameID'] .'/'.$i.'">'.$i.'</a>
                ]';
            }
        }
        if($curentPage < $pages){
            $this->html .='
            [
                <a href="/'. $this->conf['boardNameID'] .'/'.$curentPage + 1 .'">next</a>
            ]
            <a href="/'. $this->conf['boardNameID'] .'/'. $pages .'">&gt;&gt;</a>';
        }
        $this->html .='</div>';
    }
    public function drawPage($pageNumber = 0){
        global $THREADREPO;
        $threads = $THREADREPO->loadThreadsByPage($this->conf, $pageNumber);
        
        $this->html .='
        <!DOCTYPE html>
        <html lang="en-US">';
        $this->drawHead();
        $this->html .= '<body>';
        $this->drawNavBar();
        $this->drawBoardTitle();
        $this->drawFormNewThread();
        $this->postManagerWraper(
            [$this, 'drawThreadListing'] , $threads
        );
        $this->drawPageNumbers($pageNumber);
        $this->html .= '</body>';
        if ($this->conf['drawFooter']){
            $this->drawFooter();
        }
        echo $this->html;
    }
    public function drawThreadPage($thread){
        $this->html .='
        <!DOCTYPE html>
        <html lang="en-US">';
        $this->drawHead();
        $this->html .= '<body>';
        $this->drawNavBar();
        $this->drawBoardTitle();
        $this->drawFormNewPost($thread->getThreadID());
        $this->postManagerWraper(
            [$this, 'drawThread'] , $thread
        );

        $this->html .= '</body>';
        if ($this->conf['drawFooter']){
            $this->drawFooter();
        }
        echo $this->html;
    }
}