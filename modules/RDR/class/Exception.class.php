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
* The RDR Exception
*/
class RDR_Exception extends Exception{

    /**
    * Display the exception html
    */
    public function getHtml(){
        if(req()->isAjax() || PHP_SAPI == "cli"){
            echo $this->getMessage()."\n\n".$this->getTraceAsString();
            return;
        }
        ?><!DOCTYPE html>
        <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Error</title>
        </head>
        <body>
            <h1>Well, something is going wrong!</h1>
            <h3 style="color:red;"><?php echo nl2br(s($this->getMessage()))?></h3>
            <pre><?php echo $this->getTraceAsString()?></pre>
        </body>
        </html>
        <?php
    }
}