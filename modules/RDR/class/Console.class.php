<?php
/**
 * This file is part of Choqled PHP Framework and/or part of a BFLDEV Software Product.
 * This file is licensed under "GNU General Public License" Version 3 (GPL v3).
 * If you find a bug or you want to contribute some code snippets, let me know at http://bfldev.com/nreeda
 * Suggestions and ideas are also always helpful.

 * @author Roland Eigelsreiter (BrainFooLong)
 * @product nReeda - Web-based Open Source RSS/XML/Atom Feed Reader
 * @link http://bfldev.com/nreeda
**/

if(!defined("CHOQ")) die();
/**
* The console
*/
class RDR_Console{
    /**
    * Process the tasks
    */
    static function process(){
        if(!inNormalMode()) return;
        switch($_SERVER["argv"][1]){
            case "cron":
                $view = new RDR_Cron();
                $view->parameters["param"] = RDR_Cron::getHash();
                $view->onLoad();
            break;
            default:
                echo "Unknown console job '{$_SERVER["argv"][1]}'";
        }
    }
}