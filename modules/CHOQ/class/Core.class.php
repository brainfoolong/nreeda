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

define("CHOQ", 1);
/**
* Core
*/
class CHOQ_Core{

    /**
    * Activate the Choqled error and shutdown handling
    *
    * @var bool
    */
    static $overrideErrorHandling = true;

    /**
    * If active than CHOQ will set error reporting to E_ALL
    *
    * @var bool
    */
    static $overrideErrorReporting = true;


    /**
    * The really first core initialisation
    */
    static function initialise(){

        ini_set("display_errors", 1);
        if(self::$overrideErrorReporting) error_reporting(E_ALL);
        ini_set("zlib.output_compression", 4096);
        if(!ini_get('date.timezone')) ini_set("date.timezone", "UTC");

        define("DS", DIRECTORY_SEPARATOR);
        define("CHOQ_ROOT_DIRECTORY", dirname(dirname(dirname(__DIR__))));
        define("CHOQ_ACTIVE_MODULE_DIRECTORY", CHOQ_ROOT_DIRECTORY.DS."modules".DS.CHOQ_ACTIVE_MODULE);
        define("CHOQ_STARTTIME", microtime(true));

        require(__DIR__.DS."Module.class.php");
        require(__DIR__.DS."ClassManager.class.php");
        require(__DIR__.DS."Exception.class.php");
        require(__DIR__.DS."OutputManager.class.php");

        if(self::$overrideErrorHandling){
            set_error_handler("CHOQ_Exception::errorHandler");
            set_exception_handler("CHOQ_Exception::exceptionHandler");
            register_shutdown_function("CHOQ_Core::shutdown");
        }

        session_name("CHOQSESS");

        ob_start();
        CHOQ_Module::get("CHOQ");
    }

    /**
    * Fired on shutdown the php script execution
    * Activate zlib output compression when no errors are happened and headers are not send
    */
    static function shutdown(){
        if($error = error_get_last()){
            try{
                CHOQ_Exception::errorHandler($error["type"], $error["message"], $error["file"], $error["line"]);
            }catch(CHOQ_Exception $e){
                $e->getHtml();
            }
            return;
        }
    }
}