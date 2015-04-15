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
    * @param string $path Anything parsable by parse_url()
    * @param mixed $id null for default or a string id
    */
    static function add($path, $id = null){
        if(is_null($id)) $id = "default";
        $parsedUrl = parse_url($path);
        self::$instances[$id] = $parsedUrl;
    }

    /**
    * Get a instance
    * If $id is not set than the "default" is used
    *
    * @param mixed $id The instance id
    * @return self
    */
    static function getInstance($id = null){
        if(is_null($id)) $id = "default";
        if($id instanceof CHOQ_Cache) $id = $id->id;
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
    * @param mixed $value Any value to store, if null remove the entry
    * @param CHOQ_DateTime|int|null $expires
    *   If integer than the expire time is = now + $expire
    *   If null than no expire is set
    * @return self
    */
    abstract public function set($key, $value, $expires = null);

    /**
    * Get value from cache
    * null returned if key not found
    *
    * @param string $key The cache key
    * @return mixed|false False if key not found OR value is false
    */
    abstract public function get($key);
}
