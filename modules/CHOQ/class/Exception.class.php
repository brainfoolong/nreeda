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
* The CHOQ Exception
*/
class CHOQ_Exception extends Exception{

    /**
    * If set to false than the cleanAllBuffers will not be called before error output
    *
    * @var bool
    */
    static $cleanBufferBeforeOutput = true;

    /**
    * Is error log enabled
    *
    * @var bool
    */
    static $errorLog = true;

    /**
    * Is exception handler active, if false than exceptions would be thrown as normal instead of display a formatted output
    *
    * @var bool
    */
    static $customHandler = true;

    /**
    * for set_error_handler()
    *
    * @param mixed $errno
    * @param mixed $errstr
    * @param mixed $errfile
    * @param mixed $errline
    */
    static function errorHandler($errno, $errstr, $errfile, $errline){
        if(!ini_get("error_reporting")) return NULL;
        $class = CHOQ_ACTIVE_MODULE."_Exception";
        if(!class_exists($class)) $class = "CHOQ_Exception";
        $line = $errstr." in file $errfile ($errline)";
        switch($errno){
            case E_NOTICE:
            case E_USER_NOTICE:
                $message = ("PHP Notice: $line");
            break;
            case E_RECOVERABLE_ERROR:
                $message = ("PHP Recoverable Error: $line");
            break;
            case E_STRICT:
                $message = ("PHP Strict: $line");
            break;
            case E_WARNING:
            case E_USER_WARNING:
                $message = ("PHP Warning: $line");
            break;
            case E_ERROR:
            case E_USER_ERROR:
            case E_COMPILE_ERROR:
                $message = ("PHP Error: $line");
            break;
            case E_PARSE:
                $message = ("PHP Parse Error: $line");
            break;
            case E_DEPRECATED:
                $message = ("PHP Deprecated: $line");
            break;
            default:
                $message = ("PHP Undefined Error ($errno): $line");
        }
        self::exceptionHandler(new $class($message));
    }

    /**
    * Exception Handler
    *
    * @param Exception $exception
    */
    static function exceptionHandler($exception){
        if(!self::$customHandler) throw $exception;
        if(self::$cleanBufferBeforeOutput) CHOQ_OutputManager::cleanAllBuffers();
        self::setHeader($exception->getCode() ? $exception->getCode() : 500, $exception->getMessage());
        if(method_exists($exception, "getHtml")) {
            $exception->getHtml();
        }else{
            echo '<pre style="overflow-x:auto; overflow-y:auto; font-family:courier new; font-size:13px; line-height: 17px; background:white; margin:5px; padding:5px; border-left:5px solid red; color:black;">';
            echo '<h3 style="color:red; font-family:arial; padding:0px; margin:0px; margin-bottom:10px;">'.nl2br(s($exception->getMessage())).'</h3>';
            if(CHOQ::isMode(CHOQ::MODE_DEV)){
                echo s(str_replace(CHOQ_ROOT_DIRECTORY, ".", $exception->getTraceAsString()));
            }
            echo '</pre>';
        }
        if(self::$errorLog){
            $dir = CHOQ_ACTIVE_MODULE_DIRECTORY.DS."logs";
            if(is_dir($dir) && is_writable($dir)){
                $count = 0;
                $file = $dir.DS."error.log";
                if(file_exists($file) && filesize($file) > 1024 * 1024 * 20){
                    rename($file, $file.".".time());
                }
                $message = "[".date("d.m.Y H:i:s")."] ".$exception->getMessage()."\n";
                $message .= $exception->getTraceAsString()."\n===============\n";
                file_put_contents($file, $message, FILE_APPEND);
            }
        }
        die();
    }

    /**
    * Constructor
    *
    * @param mixed $message
    * @param int $code
    * @return CHOQ_Exception
    */
    public function __construct($message = NULL, $code = 500){
        parent::__construct($message, $code);
    }

    /**
    * Set error header
    *
    * @param mixed $code
    * @param string $message
    */
    static public function setHeader($code, $message){
        if(!headers_sent()) {
            header("HTTP/1.1 $code Aliens disrupting");
            if(CHOQ::isMode(CHOQ::MODE_DEV)){
                header("X-ERROR: ".str_replace(array("\n", "\r", "\t"), "", $message));
            }
        }
    }
}