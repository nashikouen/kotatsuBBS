<?php
/*
 *
 * this file might look weird in your editor. i am using vscode and the logic lines up with the html.
 * this is to help make it more understandable where the logic is being apply to.
 * 
 */

require_once __DIR__ .'/hook.php';
require_once __DIR__ .'/repos/repoThread.php';
require_once __DIR__ .'/../lib/common.php';
require_once __DIR__ .'/auth.php';

$HOOK = HookClass::getInstance();
$THREADREPO = ThreadRepoClass::getInstance();
$AUTH = AuthClass::getInstance();

class adminhtmlclass {
    private string $html = "";
    private array $conf;
    private boardClass $board;
    private htmlclass $htmlObj;
    public function __construct(array $conf, boardClass $board) {
        $this->conf = $conf;
        $this->board = $board;
        $this->htmlObj = new htmlclass($conf, $board);
    }

    private function drawLoginForm() {
        $this->html .='
        <!--drawLoginForm()-->
        <center class="loginForm">
        <form method="POST" action="'.ROOTPATH.'admin.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="boardID" value="'.$this->board->getBoardID().'">
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        </center>';
    }
    private function drawLogOutForm(){
        $this->html .='
        <!--drawLogOutForm()-->
        <form method="post" action="'.ROOTPATH.'admin.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="logout">
            <input type="hidden" name="boardID" value="'.$this->board->getBoardID().'">
            [<button type="submit" class="hyperButton">Logout</button>]
        </form>';
    }

}