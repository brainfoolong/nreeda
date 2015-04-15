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
* Simple values for global use
*/
class CHOQ_Var{

    /**
    * The array
    *
    * @var mixed[]
    */
    static private $data = array();

    /**
    * Add a value
    *
    * @param mixed $key
    * @param mixed $value
    */
    static function add($key, $value){
        self::$data[$key] = $value;
    }

    /**
    * Get a value
    *
    * @param string $key
    * @return mixed
    */
    static function get($key){
        return arrayValue(self::$data, $key);
    }
}
