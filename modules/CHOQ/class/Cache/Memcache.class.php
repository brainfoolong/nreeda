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
* Memcache Cache
* Store values into a memcache server
* Note: Memcache have much limitations and its not the best choice for
* caching big data sets
*
* @requirements A running Memcache daemon and the Memcache PHP Extension
* @see http://code.google.com/p/memcached/wiki/FAQ
*/

class CHOQ_Cache_Memcache extends CHOQ_Cache{

    /**
    * The Memcache Instance
    *
    * @var Memcache
    */
    private $memcache;

    /**
    * Constructor
    *
    * @param array $connectionData Connection Data parsed by parse_url()
    * @return self
    */
    public function __construct(array $connectionData){
        if(!class_exists("Memcache", false)){
            error("Memcache Extension not installed - Update your PHP configuration");
        }
        $this->memcache = new Memcache();
        $host = $connectionData["host"];
        $port = arrayValue($connectionData, "port");
        $port = $port ? $port : 11211;
        $connectState = $this->memcache->connect($host, $port);
        if(!$connectState){
            error("Could not connect to Memcache Server at ".$host.":".$port);
        }
    }

    /**
    * Store a value in the cache
    *
    * @param string $key The cache key
    * @param mixed $value Any value to store, if NULL remove the entry
    * @param CHOQ_DateTime|int|NULL $expires
    *   If integer than the expire time is = now + $expire
    *   If NULL than no expire is set
    */
    public function set($key, $value, $expires = NULL){
        if($expires && is_object($expires)){
            $expires = $expires->getUnixtime();
        }if($expires && is_int($expires)){
            $expires = time() + $expires;
        }else{
            $expires = 0;
        }
        $key = saltedHash("crc32b", $key);
        if($value === NULL){
            $this->memcache->delete($key);
        }else{
            $compress = (is_bool($value) || is_int($value) || is_float($value)) ? false : MEMCACHE_COMPRESSED;
            $this->memcache->set($key, $value, $compress, $expires);
        }
        return $this;
    }

    /**
    * Get value from cache
    * NULL returned if key not found
    *
    * @param string $key The cache key
    * @return mixed|false False if key not found OR value is false
    */
    public function get($key){
        $key = saltedHash("crc32b", $key);
        return $this->memcache->get($key);
    }
}