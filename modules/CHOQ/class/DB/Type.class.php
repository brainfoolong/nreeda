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
* DB Type Handling
*/
class CHOQ_DB_Type{

    /**
    * Instances
    *
    * @var CHOQ_DB_Type[]
    */
    static $instances;

    /**
    * The classname of this type
    *
    * @var string
    */
    public $class;

    /**
    * The members of this type
    *
    * @var CHOQ_DB_TypeMember[]
    */
    public $members = array();

    /**
    * Array of added indexes
    *
    * @var array
    */
    public $indexes = array();

    /**
    * Get a instance for a class
    *
    * @param string|CHOQ_DB_Object $class
    * @return CHOQ_DB_Type
    */
    static function get($class){
        if(is_object($class)) $class = get_class($class);
        if(!isset(self::$instances[$class])) return new self($class);
        return self::$instances[$class];
    }

    /**
    * Constructor
    *
    * @param string $class
    * @return CHOQ_DB_Type
    */
    public function __construct($class){
        $this->class = $class;
        self::$instances[$class] = $this;
    }

    /**
    * To string
    *
    * @return string
    */
    public function __toString(){
        return (string)$this->class;
    }

    /**
    * Get the member
    *
    * @param string $name
    * @return CHOQ_DB_TypeMember
    */
    public function getMember($name){
        if(!isset($this->members[$name])) error("Type Member '$name' does not exist in ".(string)$this);
        return $this->members[$name];
    }

    /**
    * Add a index to this type
    *
    * @param mixed $typeOfIndex Types: unique, index
    * @param CHOQ_DB_TypeMember[] $members A array of existing members
    */
    public function addIndex($typeOfIndex, array $members){
        $key = saltedHash("crc32b", implode("-", arrayMapProperty($members, "name"))."-".$typeOfIndex);
        $this->indexes[$key] = array(
            $typeOfIndex,
            arrayMapProperty($members, "name")
        );
    }

    /**
    * Create and return the new member
    *
    * @param string $name
    * @return CHOQ_DB_TypeMember
    */
    public function createMember($name){
        return new CHOQ_DB_TypeMember($this, $name);
    }
}

