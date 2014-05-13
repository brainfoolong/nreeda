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
* Cacher
*/
abstract class CHOQ_Cache{
    /**
    * The cacher instances
    *
    * @var CHOQ_Cache[]
    */
    static $instances;

    /**
    * The instance id
    *
    * @var string
    */
    public $id;

    /**
    * Add a instance
    *
    * @param string $id
    * @param string $path Anything parsable by parse_url()
    */
    static function add($id = "default", $path){
        $parsedUrl = parse_url($path);
        self::$instances[$id] = $parsedUrl;
    }

    /**
    * Get a instance
    *
    * @param mixed $id
    * @return self
    */
    static function getInstance($id = "default"){
        if(!isset(self::$instances[$id])){
            error("No Cache Instance added for '$id' Instance");
        }
        if(is_object(self::$instances[$id])){
            return self::$instances[$id];
        }
        $connectionData = self::$instances[$id];
        $scheme = ucfirst(strtolower($connectionData["scheme"]));
        $type = "CHOQ_Cache_$scheme";
        self::$instances[$id] = new $type($connectionData);
        self::$instances[$id]->id = $id;
        return self::$instances[$id];
    }
    /**
    * Store a value in the cache
    *
    * @param string $key The cache key
    * @param mixed $value Any value to store, if NULL remove the entry
    * @param CHOQ_DateTime|int|NULL $expires
    *   If integer than the expire time is = now + $expire
    *   If NULL than no expire is set
    * @return self
    */
    abstract public function set($key, $value, $expires = NULL);

    /**
    * Get value from cache
    * NULL returned if key not found
    *
    * @param string $key The cache key
    * @return mixed|false False if key not found OR value is false
    */
    abstract public function get($key);
}
