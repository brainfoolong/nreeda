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
* DB Type Members
*/
class CHOQ_DB_TypeMember{

    /**
    * All available types
    *
    * @var array
    */
    static $types = array(
        "tinyint", "smallint", "mediumint", "int", "bigint", "serial",
        "bool",
        "decimal", "float", "double", "real",
        "date", "datetime", "timestamp", "time", "year",
        "char", "varchar", "tinytext", "text", "mediumtext", "longtext",
        "array"
    );

    /**
    * All available types grouped as their php opposite
    *
    * @var array
    */
    static $typesPHP = array(
        "integer" => array("tinyint", "smallint", "mediumint", "int", "bigint", "serial"),
        "boolean" => array("bool"),
        "double" => array("decimal", "float", "double", "real"),
        "datetime" => array("date", "datetime", "timestamp", "time", "year"),
        "string" => array("char", "varchar", "tinytext", "text", "mediumtext", "longtext"),
        "array" => array("array")
    );

    /**
    * Cache for some methods
    *
    * @var mixed
    */
    static private $_cache;

    /**
    * The parent
    *
    * @var CHOQ_DB_Type
    */
    public $type;

    /**
    * The name of the property
    *
    * @var string
    */
    public $name;

    /**
    * The field type
    *
    * @var string
    */
    public $fieldType;

    /**
    * The field type is class, the classname is set
    *
    * @var string
    */
    public $fieldTypeClass;

    /**
    * The field type for a array type
    *
    * @var string
    */
    public $fieldTypeArray;

    /**
    * The field type array is class, the classname is set
    *
    * @var string
    */
    public $fieldTypeArrayClass;

    /**
    * Set the length of the field
    *
    * @var int
    */
    public $length;

    /**
    * Set the length of the decimal types
    *
    * @var int
    */
    public $decimalLength;

    /**
    * Is optional - Default is false
    *
    * @var bool
    */
    public $optional = false;

    /**
    * Set unsigned, only appears for numeric values
    *
    * @var bool
    */
    public $unsigned = false;

    /**
    * If member is stored in another database than the expected db
    *
    * @var CHOQ_DB
    */
    public $getDb;

    /**
    * Constructor
    *
    * @param CHOQ_DB_Type $type
    * @param string $name The name of the type member
    * @return CHOQ_DB_TypeMember
    */
    public function __construct(CHOQ_DB_Type $type, $name){
        $this->type = $type;
        $this->name = $name;
        $this->type->members[$name] = $this;
    }

    /**
    * To string
    *
    * @return string
    */
    public function __toString(){
        return (string)$this->type->class."->".$this->name;
    }

    /**
    * Set the field type for the database - You can use native aliases or any class name
    * Date types automatically be converted into CHOQ_DateTime
    * Available types see self::$types
    *
    * @todo binary, varbinary, *blob
    * @param string $type
    * @param string $arrayType If $type is "array" than you must define the real type for the array
    * @return CHOQ_DB_TypeMember
    */
    public function setFieldType($type, $arrayType = null){
        $tmp = array($type, $arrayType);
        foreach($tmp as $index => $value){
            if($value === null) continue;
            if(!in_array($value, self::$types, true)){
                $cdb = "CHOQ_DB_Object";
                if(class_exists($value) && (is_subclass_of($value, $cdb) || $value == $cdb)){
                    if(!$index){
                        $this->fieldTypeClass = $value;
                    }else{
                        $this->fieldTypeArrayClass = $value;
                    }
                }else{
                    error("DB Type '$value' not found or is not an instance of CHOQ_DB_Object in ".(string)$this);
                }
            }
        }
        $this->fieldType = $type;
        if($type == "array" && !$arrayType) error("You must define the arrayType in ".(string)$this);
        $this->fieldTypeArray = $arrayType;
        return $this;
    }

    /**
    * Set if value can be null or not
    *
    * @param bool $bool
    * @return CHOQ_DB_TypeMember
    */
    public function setOptional($bool){
        if(!is_bool($bool)) error("setOptional parameter must be a boolean value in ".(string)$this);
        $this->optional = (bool)$bool;
        return $this;
    }

    /**
    * Set if value is unsigned or signed
    *
    * @param bool $bool
    * @return CHOQ_DB_TypeMember
    */
    public function setUnsigned($bool){
        if(!is_bool($bool)) error("setUnisgned parameter must be a boolean value in ".(string)$this);
        $this->unsigned = (bool)$bool;
        return $this;
    }

    /**
    * Set the length and, if required, the decimal length of the field
    *
    * @param bool $bool
    * @return CHOQ_DB_TypeMember
    */
    public function setLength($length, $decimalLength = null){
        if(!is_int($length)) error("setLength must be integer value");
        if($decimalLength !== null && !is_int($decimalLength)) error("setLength - Decimal length must be integer value in ".(string)$this);
        $this->length = $length;
        if($decimalLength) $this->length += $decimalLength;
        $this->decimalLength = $decimalLength;
        return $this;
    }

    /**
    * If member is stored in another database than the expected db
    *
    * @param string $db
    * @return CHOQ_DB_TypeMember
    */
    public function setDbForGetter($db){
        $this->getDb = $db;
        return $this;
    }

    /**
    * Add a unique table index for this member
    *
    * @return CHOQ_DB_TypeMember
    */
    public function addUniqueIndex(){
        $this->type->addIndex("unique", array($this));
        return $this;
    }

    /**
    * Add a table index for this member
    *
    * @return CHOQ_DB_TypeMember
    */
    public function addIndex(){
        $this->type->addIndex("index", array($this));
        return $this;
    }

    /**
    * Check given value if it's the correct value for the member type
    *
    * @param mixed $value
    * @param bool $throwException If true than throw specific exception
    * @return bool
    */
    public function checkValue(&$value, $throwException = false){
        $name = $this->__toString();
        if(is_object($value)){
            $vClass = get_class($value);
            $isDateTime = in_array($this->fieldType, self::$typesPHP["datetime"], true);
            if($isDateTime){
                if($value instanceof CHOQ_DateTime) return true;
                if($throwException) error("'{$name}' is set to '".get_class($value)."' but is required to be type 'CHOQ_DateTime'");
                return false;
            }
            $class = $this->fieldTypeClass;
            if(!$class) {
                if($throwException){
                    $rType = $this->getPHPTypeForDbType($this->fieldType);
                    error("'{$name}' is set to '".get_class($value)."' but is required to be type '{$rType}'");
                }
                return false;
            }
            if(!$value->getId()) error("Could not use transient object '".get_class($value)."' for database usage");
            if($value instanceof $class) return true;
            if($throwException) error("{$name} is set to '".get_class($value)."' but is required to be type '{$class}'");
            return false;
        }
        if(is_array($value)){
            if($this->fieldType != "array") {
                $rType = $this->fieldTypeClass;
                if(!$rType) $rType = $this->getPHPTypeForDbType($this->fieldType);
                if($throwException) error("'{$name}' is set to 'array' but is required to be '{$rType}'");
                return false;
            }
            foreach($value as $key => &$val){
                if(!$this->checkValue($val, false)){
                    if($throwException) $this->checkArrayValue($val, true);
                    return false;
                }
            }
            return true;
        }
        $vType = gettype($value);
        $rType = $this->fieldTypeClass;
        if(!$rType) $rType = $this->getPHPTypeForDbType($this->fieldType);
        if($vType != $rType) {
            if($throwException) error("'{$name}' is set to '{$vType}' but is required to be type '{$rType}'");
            return false;
        }
        if($this->length){
            $length = mb_strlen($value);
            if($length > $this->length){
                if($throwException) error("'{$name}' has length of '$length' but is limited to '{$this->length}'");
                return false;
            }
            if($this->decimalLength && is_double($value)){
                $length = strlen($value);
                $length = $length - strpos($value, ".");
                if($length > $this->decimalLength){
                    if($throwException) error("'{$name}' has decimal length of '$length' but is limited to '{$this->decimalLength}'");
                }
            }
        }
        return true;
    }

    /**
    * Check given value if it's the correct array value for the member type
    *
    * @param mixed $value
    * @param mixed $key
    * @param bool $throwException If true than throw specific exception
    * @return bool
    */
    public function checkArrayValue(&$value, $key, $throwException = false){
        $tmp1 = $this->fieldType;
        $tmp2 = $this->fieldTypeClass;
        $tmp3 = $this->name;
        $return = true;
        $this->fieldType = $this->fieldTypeArray;
        $this->fieldTypeClass = $this->fieldTypeArrayClass;
        $this->name .= "[{$key}]";
        if($throwException){
            $this->checkValue($value, $throwException);
        }else{
            $return = $this->checkValue($value, $throwException);
        }
        $this->fieldType = $tmp1;
        $this->fieldTypeClass = $tmp2;
        $this->name = $tmp3;
        return $return;
    }

    /**
    * Convert the given value in the correct database format value
    *
    * @param mixed $value
    * @return mixed
    */
    public function convertToDbValue($value){
        $fieldType = $this->fieldTypeArray ? $this->fieldTypeArray : $this->fieldType;
        $fieldTypeClass = $this->fieldTypeArrayClass ? $this->fieldTypeArrayClass : $this->fieldTypeClass;
        if($fieldTypeClass){
            if($value instanceof CHOQ_DB_Object) return $value->getId() ? $value->getId() : NULL;
            error('Given value for '.(string)$this.' is not a required instance of CHOQ_DB_Object');
        }
        if($fieldType == "date" ||$fieldType == "datetime" || $fieldType == "timestamp" || $fieldType == "time" || $fieldType == "year"){
            if($value instanceof CHOQ_DateTime && $value->valid){
                $tz = CHOQ_DateTime::$dbTimezone;
                if($fieldType == "date") return $value->format("Y-m-d", $tz);
                if($fieldType == "datetime" || $fieldType == "timestamp") return $value->format("Y-m-d H:i:s", $tz);
                if($fieldType == "time") return $value->format("H:i:s", $tz);
                if($fieldType == "year") return $value->format("Y", CHOQ_DateTime::$dbTimezone);
            }
            error('Could not convert '.(string)$this.' to DB value because given value is invalid');
        }
        $rType = $this->getPHPTypeForDbType($fieldType);
        settype($value, $rType);
        return $value;
    }

    /**
    * Convert the given value from the database into the correct framework format
    * array/object types return null - This special type is handled seperatly by the DB connection
    *
    * @param mixed $value
    * @return mixed
    */
    public function convertFromDbValue($value){
        if($value === NULL) return NULL;
        $rType = $this->getPHPTypeForDbType($this->fieldTypeArray ? $this->fieldTypeArray : $this->fieldType);
        if($rType == "datetime") return dt($value." ".CHOQ_DateTime::$dbTimezone);
        if($rType != "array" && $rType){
            settype($value, $rType);
            return $value;
        }
    }

    /**
    * Get the PHP type description for a db type description
    *
    * @param mixed $type
    * @return string
    */
    public function getPHPTypeForDbType($type){
        if(isset(self::$_cache[$type])) return self::$_cache[$type];
        foreach(self::$typesPHP as $key => $types){
            if(in_array($type, $types)){
                self::$_cache[$type] = $key;
                return $key;
            }
        }
    }
}

