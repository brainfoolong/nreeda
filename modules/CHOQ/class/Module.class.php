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
* Module Bae Class and Manager
*/
class CHOQ_Module{

    /**
    * Array of module instances
    *
    * @var CHOQ_Module[]
    */
    static $instances = array();

    /**
    * The module name
    *
    * @var string
    */
    public $name;

    /**
    * The module directory
    *
    * @var string
    */
    public $directory;

    /**
    * Get a module instance
    * If not exist instantiate it
    *
    * @param mixed $module
    * @return CHOQ_Module
    */
    static function get($module){
        if(isset(self::$instances[$module])) return self::$instances[$module];
        $instance = new $module;
        self::$instances[$module] = $instance;
        $instance->name = $module;
        $instance->directory = CHOQ_ROOT_DIRECTORY.DS."modules".DS.$module;
        $instance->onInit();
        $instance->afterInit();
        foreach(self::$instances as $object) $object->afterOtherModuleInit($instance);
        return $instance;
    }

    /**
    * Return a array of module instances
    *
    * @param array $modules
    * @return CHOQ_Module[]
    */
    static function getArrayOfModules(array $modules){
        $arr = array();
        foreach($modules as $module) $arr[$module] = self::get($module);
        return $arr;
    }

    /**
    * Fired on instantiate the module
    * At this point nothing from the module is loaded
    * Override it for your case
    */
    public function onInit(){}

    /**
    * Fired after the initialisation is done
    * Override it for your case
    */
    public function afterInit(){}

    /**
    * Fired after the initialisation is done, for any other module than itself
    * Override it for your case
    *
    * @param CHOQ_Module $module
    */
    public function afterOtherModuleInit(CHOQ_Module $module){}

    /**
    * Fired before the matched view is loaded
    * Override it for your case
    *
    * @param CHOQ_View $view
    */
    public function beforeLoadView(CHOQ_View $view){}

    /**
    * Fired after the matched view is loaded
    * Override it for your case
    *
    * @param CHOQ_View $view
    */
    public function afterLoadView(CHOQ_View $view){}
}