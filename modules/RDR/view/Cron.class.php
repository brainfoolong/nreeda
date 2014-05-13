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
* The crontasks
*/
class RDR_Cron extends CHOQ_View{

    /**
    * Get full url to this page
    */
    static function getLink(){
        return "http".(req()->isHttps() ? "s" : "")."://".req()->getHost().l(__CLASS__, array("param" => self::getHash()));
    }

    /**
    * Get secure hash
    */
    static function getHash(){
        return md5(__FILE__);
    }

    /**
    * Load the View
    */
    public function onLoad(){
        if(!RDR::$isInstalled) return;
        $param = $this->getParam("param");
        if($param != self::getHash()) die("Not allowed");
        RDR_Event::log(RDR_Event::TYPE_CRON_START);
        RDR_Import::updateAllFeeds();
        RDR_Event::log(RDR_Event::TYPE_CRON_END);
        RDR_Event::cleanup();
        RDR_Entry::cleanup();
        $generator = CHOQ_DB_Generator::create(db());
        if($generator instanceof CHOQ_DB_Generator_Mysql){
            $generator->addModule("RDR");
            $generator->optimizeTables();
        }
    }
}