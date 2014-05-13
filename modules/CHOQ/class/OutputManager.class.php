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
* Output Manager
*/
class CHOQ_OutputManager{

    /**
    * Clean all existing user buffers
    */
    static function cleanAllBuffers(){
        for($i = 0; $i <= 30; $i++){
            if(!ob_get_level()) break;
            $status = ob_get_status();
            if($status &&  strpos(strtolower($status["name"]), "zlib") === false) ob_end_clean();
        }
    }
}