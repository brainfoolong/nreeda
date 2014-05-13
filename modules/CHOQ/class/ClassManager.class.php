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
* Class Manager
*/
class CHOQ_ClassManager{

    /**
    * The class folders
    *
    * @var string[]
    */
    static private $classFolders = array("class", "type", "view");

    /**
    * Load a class
    * Return true if class loaded successfully or is already loaded
    *
    * @param string $className
    * @todo Odd Classes
    * @return bool
    */
    static function loadClass($className){
        if(class_exists($className, false)) return true;
        $pos = strpos($className, "_");
        if($pos !== false){
            $module = substr($className, 0, $pos);
            if(isset(CHOQ_Module::$instances[$module])){
                foreach(self::$classFolders as $folder){
                    $classPath = CHOQ_Module::$instances[$module]->directory.DS.$folder.DS.str_replace(array("\/", "/", "_"), DS, substr($className, $pos+1)).".class.php";
                    if(file_exists($classPath)){
                        if(CHOQ::isMode(CHOQ::MODE_DEV)){
                            foreach(self::$classFolders as $subFolder){
                                if($subFolder == $folder) continue;
                                $path = str_replace(DS.$folder.DS, DS.$subFolder.DS, $classPath);
                                if(file_exists($path)) error("Duplicate classes found in {$classPath} and {$path}");
                            }
                        }
                        require($classPath);
                        if(method_exists($className, "onAutoload")) $className::onAutoload();
                        return true;
                    }
                }
            }
        }
        $path = CHOQ_ROOT_DIRECTORY.DS."modules".DS.$className.DS.$className.".class.php";
        if(file_exists($path)){
            require($path);
            return true;
        }
        return false;
    }
}

spl_autoload_register("CHOQ_ClassManager::loadClass");