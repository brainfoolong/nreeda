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
    * Max cron time
    */
    const MAXTIME = 600;

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
    * Get PID file path
    *
    * @return string
    */
    static function getPIDFile(){
        return CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp/cron.pid";
    }

    /**
    * Check if cron is already running
    *
    * @return bool
    */
    static function isRunning(){
        $pid = self::getPIDFile();
        if(file_exists($pid)){
            $filetime = filemtime($pid);
            if($filetime > time() - RDR_Cron::MAXTIME * 1.2){
                return true;
            }
        }
        return false;
    }

    /**
    * Load the View
    */
    public function onLoad(){
        if(!inNormalMode()) return;

        # set time limit to max 10 minutes
        # if this limit is reached than the script stops and continue at next cron
        set_time_limit(RDR_Cron::MAXTIME);

        $cronPidFile = self::getPIDFile();

        # skip when cron is already running
        if(self::isRunning()){
            RDR_Event::log(RDR_Event::TYPE_CRON_RUNNING);
            return;
        }
        # create a tmp file that show us the cron pid
        file_put_contents($cronPidFile, time());

        $param = $this->getParam("param");
        if($param != self::getHash()) die("Not allowed");
        RDR_Event::log(RDR_Event::TYPE_CRON_START);
        RDR_Import::updateAllFeeds();
        RDR_Event::log(RDR_Event::TYPE_CRON_END);
        RDR_Cleanup::cleanupEvents();
        RDR_Cleanup::cleanupEntries();
        RDR_FileContents::cleanupTmpFiles();
        RDR_Proxy::cleanupTmpFiles();

        # optimizing tables
        $generator = CHOQ_DB_Generator::create(db());
        if($generator instanceof CHOQ_DB_Generator_Mysql){
            $generator->addModule("RDR");
            $generator->optimizeTables();
        }

        # delete tmp file that show us the cron pid
        unlink(CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp/cron.pid");
    }
}