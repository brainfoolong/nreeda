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

/**
* Global Setting
*
* @property string $key
* @property string $value
*/
class RDR_Setting extends CHOQ_DB_Object{

    /**
    * Settings cache
    *
    * @var self[]
    */
    static private $_cache;

    /**
    * Register the Object itself as a Type.
    * Define all required Database properties here.
    */
    static function onAutoload(){
        $type = new CHOQ_DB_Type(__CLASS__);
        $type->createMember("key")->setFieldType("varchar")->setLength(255)->addUniqueIndex();
        $type->createMember("value")->setFieldType("text");
    }

    /**
    * Get settings entry, create if not exist
    *
    * @param mixed $key
    * @return self
    */
    static function get($key){
        if(isset(self::$_cache[$key])) return self::$_cache[$key];
        $object = self::getByCondition(db()->quote("key")." = {0}", array($key));
        if($object) {
            self::$_cache[$key] = reset($object);
            return self::$_cache[$key];
        }else{
            $object = new self(db());
            $object->key = $key;
            $object->value = "";
            $object->store();
            self::$_cache[$key] = $object;
            return $object;
        }
    }

    /**
    * Set a setting
    *
    * @param mixed $key
    * @param mixed $value
    * @return self
    */
    static function set($key, $value){
        $object = self::get($key);
        $object->value = $value;
        $object->store();
        return $object;
    }

    /**
    * Create cache for all settings
    */
    static private function createCache(){
        if(self::$_cache === null){
            self::$_cache = db()->fetchColumn("SELECT value, ".db()->quote("key")." FROM ".__CLASS__, "key");
        }
    }
}
