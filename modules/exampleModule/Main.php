<?php

/*
 * you dont have to stick to do OOP, you dont have to shove all your thing in the class below 
 * but below is the requirements needed for making a module.
 * 
 * you can make mutiple files and folder as you need in this directory.
 * you will need to have hooks in init() for functions you want to run.
 * 
 * if you need help making moduels please make issues on the github. [i will not help you not knowing how to code. but i can help with exploring the kotatsu libs for what u need]
 */

// the namespace should be changed. it should be name of folder this is in.
namespace Modules\exampleModule; 

require_once __DIR__ .'/../../classes/hook.php';

use Modules\Module;

class Main extends Module {
    public function getName(): string {
        return "Example Module";
    }
    public function getDescription(): string {
        return "This is an example module for you to steal and make your own module.";
    }
    public function getVersion(): string {
        return "1.0";
    }

    // This function will run each time PHP is invoked. Use it to set up hooks and stuff
    public function init() {
        $hook = \HookClass::getInstance();
        //uncomment the body of this anonymus function and each time a post is made it will display that thext.
        $hook->addHook("postDataLoaded", function($post) {
            //drawErrorPageAndDie("example moduel is working. postDataLoaded hook was listened to");
        });
    }

    // This function will print out a page for this module.
    public function showPage(): string {
        return "<html><body><p>This is a page for the example module. If you are putting forms in here, you should prefix your form names to avoid conflicts.</p></body></html>";
    }
}