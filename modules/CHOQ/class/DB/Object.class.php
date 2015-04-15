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
* The base class for a storable database object
*
* @property CHOQ_DateTime $createTime
* @property CHOQ_DateTime $updateTime
*/
abstract class CHOQ_DB_Object{

    /**
    * The metatable
    */
    const METATABLE = "_choqled_metadata";

    /**
    * The object cache for db fetch
    *
    * @var mixed
    */
    static $_objectCache;

    /**
    * Some caches for the object getter/setter
    *
    * @var mixed
    */
    static private $_cache;

    /**
    * The real values that setted by the user or converted into the correct type if defined
    * This values match the required member properties
    *
    * @var mixed[]
    */
    public $_realValues;

    /**
    * The values that come from the database
    *
    * @var mixed[]
    */
    public $_dbValues;

    /**
    * A array of properties that already has changed by the user
    *
    * @var bool[]
    */
    public $_changes;

    /**
    * A array of properties that already has been loaded by a getter
    *
    * @var mixed
    */
    public $_loaded;

    /**
    * The database instance from/to which the object is fetched/stored
    *
    * @var CHOQ_Db
    */
    public $_db;

    /**
    * Register the Object itself as a Type.
    * Define all required Database properties here.
    */
    static function onAutoload(){
        $type = new CHOQ_DB_Type(__CLASS__);
        $type->createMember("createTime")->setFieldType("datetime");
        $type->createMember("updateTime")->setFieldType("datetime")->setOptional(true);
    }

    /**
    * Create object from a database fetch
    * Each member must be represented in the $row
    * CHOQ_DB $db
    * @param
    * @param string $type
    * @param array $row
    * @return self
    */
    static function _createFromFetch(CHOQ_DB $db, $type, array $row){
        $object = new $type($db);
        $members = $object->_getMembers();
        foreach($members as $member){
            $object->_dbValues[$member->name] = arrayValue($row, $member->name);
        }
        $object->_dbValues["id"] = (int)$row["id"];
        return $object;
    }

    /**
    * Add a object to the cache
    *
    * @param CHOQ_DB_Object $object
    */
    static function _addToCache(CHOQ_DB_Object $object){
        $dbId = $object->_db->id;
        self::$_objectCache[$dbId][$object->getId()] = $object;
        self::$_objectCache[$dbId][get_class($object)][$object->getId()] = $object;
    }

    /**
    * Remove a object from the cache
    *
    * @param CHOQ_DB_Object $object
    */
    static function _removeFromCache(CHOQ_DB_Object $object){
        $dbId = $object->_db->id;
        if(isset(self::$_objectCache[$dbId][$object->getId()])) unset(self::$_objectCache[$dbId][$object->getId()]);
        if(isset(self::$_objectCache[$dbId][get_class($object)][$object->getId()])) unset(self::$_objectCache[$dbId][get_class($object)][$object->getId()]);
    }

    /**
    * Get all cached objects for a specific type
    *
    * @param CHOQ_DB $db
    * @param CHOQ_DB_Type $type
    * @return CHOQ_DB_Object[] | null
    */
    static function getCachedObjects(CHOQ_DB $db, CHOQ_DB_Type $type){
        $dbId = $db->id;
        if(isset(self::$_objectCache[$dbId][$type->class])) return self::$_objectCache[$dbId][$type->class];
    }

    /**
    * Get a cached object by id
    *
    * @param CHOQ_DB $db
    * @param mixed $id
    * @return CHOQ_DB_Object | null
    */
    static function getCachedObjectById(CHOQ_DB $db, $id){
        $dbId = $db->id;
        if(isset(self::$_objectCache[$dbId][$id])) return self::$_objectCache[$dbId][$id];
    }

    /**
    * Get object by id
    *
    * @param int $id
    * @param mixed $db The db connection to use
    * @return static | null
    */
    static function getById($id, $db = null){
        return db($db)->getById(get_called_class(), $id);
    }

    /**
    * Get objects by ids
    *
    * @param array $ids
    * @param bool $resort If true than the resulted array is in the same sort as the given ids
    * @param mixed $db The db connection to use
    * @return static[]
    */
    static function getByIds(array $ids, $resort = false, $db = null){
        return db($db)->getByIds(get_called_class(), $ids, $resort);
    }

    /**
    * Get objects by condition
    * Search without any parameter will return all objects
    *
    * @param string|null $condition If null than no condition is added (getAll)
    *   To add a parameters placeholder add brackets with the parameters key - Example: {mykey}
    *   To quote fieldNames correctly enclose a fieldName with <fieldName>
    * @param mixed $parameters Can be a array of parameters, a single parameter or NULL
    * @param mixed $sort Can be a array of sorts, a single sort or NULL
    *   Sort value must be a fieldName with a +/- prefix - Example: -id
    *   + means sort ASC
    *   - means sort DESC
    * @param int|null $limit Define a limit for the query
    * @param int|null $offset Define a offset for the query
    * @param mixed $db The db connection to use
    * @return static[]
    */
    static function getByCondition($condition = null, $parameters = null, $sort = null, $limit = null, $offset = null, $db = null){
        return db($db)->getByCondition(get_called_class(), $condition, $parameters, $sort, $limit, $offset);
    }


    /**
    * Get objects by own defined query
    * You only MUST select the id of the table - SELECT id FROM ...
    *
    * @param mixed $query
    * @param mixed $db The db connection to use
    * @return static[]
    */
    static function getByQuery($query, $db = null){
        return db($db)->getByQuery(get_called_class(), $query);
    }

    /**
    * Constructor
    *
    * @param CHOQ_DB $db
    * @return CHOQ_DB_Object
    */
    public function __construct(CHOQ_DB $db){
        $this->_db = $db;
    }

    /**
    * To string
    *
    * @return string
    */
    public function __toString(){
        return (string)$this->getId();
    }

    /**
    * Magic Getter
    *
    * @param string $name
    * @return mixed
    */
    public function __get($name){
        $member = $this->_getMember($name);
        if($member && property_exists($this, $name)) error("Property '{$member}' is declared in the class structure but must be undeclared");
        if($member){
            if($this->getId() && !isset($this->_loaded[$name])){
                if($member->fieldTypeClass){
                    $db = $member->getDb ? $member->getDb : $this->_db;
                    $objects = self::getCachedObjects($db, $this->_getType());
                    $ids = array();
                    if($objects){
                        foreach($objects as $object){
                            $id = arrayValue($object->_dbValues, $member->name);
                            if($id && !isset($object->_loaded[$name])) $ids[$object->getId()] = $id;
                            $object->_loaded[$name] = true;
                        }
                    }
                    if($ids){
                        $arr = $db->getByIds(NULL, $ids);
                        foreach($ids as $oid => $id){
                            $objects[$oid]->_realValues[$name] = arrayValue($arr, $id);
                        }
                    }
                }elseif($member->fieldType == "array"){
                    return $this->getByKey($name);
                }else{
                    $object->_loaded[$name] = true;
                    $this->_realValues[$name] = $member->convertFromDbValue($this->_dbValues[$name]);
                    return $this->_realValues[$name];
                }
            }
            return arrayValue($this->_realValues, $name);
        }
        return $this->{$name};
    }

    /**
    * Magic Setter
    *
    * @param string $name
    * @param mixed $value
    */
    public function __set($name, $value){
        $member = $this->_getMember($name);
        if($member) {
            $this->_loaded[$name] = true;
            $this->_changes[$name] = true;
            if($value !== null){
                $member->checkValue($value, true);
                if($member->fieldType == "array"){
                    $this->_realValues[$name] = null;
                    foreach($value as $k => $v) $this->add($name, $v, $k);
                }else{
                    $this->_realValues[$name] = $value;
                }
            }else{
                $this->_realValues[$name] = null;
            }
        }else{
            $this->{$name} = $value;
        }
    }

    /**
    * Get the CHOQ_DB_Type for this class
    *
    * @return CHOQ_DB_Type
    */
    public function _getType(){
        return CHOQ_DB_Type::get(get_class($this));
    }

    /**
    * Get the CHOQ_DB_TypeMember for the property
    *
    * @return CHOQ_DB_TypeMember|null
    */
    public function _getMember($property){
        $members = $this->_getMembers();
        if(isset($members[$property])) return $members[$property];
    }

    /**
    * Get array of all CHOQ_DB_TypeMember for this object
    *
    * @return CHOQ_DB_TypeMember[]
    */
    public function _getMembers(){
        $type = $this->_getType();
        $class = $type->class;
        if(isset(self::$_cache["members"][$class])) return self::$_cache["members"][$class];
        self::$_cache["members"][$class] = $type->members;
        $parents = class_parents($class);
        foreach($parents as $parent){
            $type = CHOQ_DB_Type::get($parent);
            self::$_cache["members"][$class] += $type->members;
        }
        return self::$_cache["members"][$class];
    }

    /**
    * Set all available members to NULL
    * Used after delete the object in the database
    *
    * @return self
    */
    public function _clear(){
        foreach($this as $key => $val) $this->{$key} = null;
    }

    /**
    * Get the database id
    * 0 if not stored in database
    *
    * @return int
    */
    public function getId(){
        return (int)arrayValue($this->_dbValues, "id");
    }

    /**
    * Get the array value by index key or get complete array if key is null
    * Keys for referenced objects are always the ID of the object, for all other the keys are that what you've set previously
    *
    * @param string $property
    * @param string|null $key If null than all keys will be returned
    * @return mixed|null Null if key not exist
    */
    public function getByKey($property, $key = null){
        $member = $this->_getMember($property);
        if($member->fieldType != "array") error("Cannot getByKey for '{$member}', is not a array member");
        if($this->getId() && !isset($this->_loaded[$property])){
            $this->_db->lazyLoadArrayMember($this, $member);
        }
        if($key === null && isset($this->_realValues[$property])) return $this->_realValues[$property];
        if($key !== null && isset($this->_realValues[$property][$key])) return $this->_realValues[$property][$key];
    }

    /**
    * Add a value to a array member
    *
    * @param string $property
    * @param mixed $value
    * @param string|int|CHOQ_DB_Object|null $key
    * @param bool $integrityChecks If false than the values will be setted without any validation (for lazy loading)
    * @return self
    */
    public function add($property, $value, $key = null, $integrityChecks = true){
        if(!$integrityChecks){
            $this->_realValues[$property][$key] = $value;
            return;
        }

        $member = $this->_getMember($property);
        if($member->fieldType != "array") error("Cannot add() for '{$member}', is not a array member");
        if($value instanceof CHOQ_DB_Object) $key = $value->getId();

        $member->checkArrayValue($value, $key, true);

        if($key === null){
            if(!isset($this->_realValues[$property])) $this->_realValues[$property] = array();
            $this->_realValues[$property][] = $value;
            end($arr);
            $key = key($arr);
            reset($arr);
            return $this->add($property, $value, $key);
        }

        $name = (string)$member;
        if(is_object($value) && !is_int($key)) error("{$name}[], keys must be integer for objects");
        if(!is_int($key) && mb_strlen($key) > 255) if($throwException) error("Array keys can only have a length of max. 255 chars");
        $existValue = $this->getByKey($property, $key);
        if((string)$existValue !== (string)$value) {
            $this->_realValues[$property][$key] = $value;
            $this->_changes[$property] = true;
        }
        return $this;
    }

    /**
    * Remove a value from a array member
    *
    * @param string $property
    * @param string|int|CHOQ_DB_Object $key
    * @return self
    */
    public function remove($property, $key){
        $member = $this->_getMember($property);
        if($member->fieldType != "array") error("Cannot remove() for '{$member}', is not a array member");
        if($key instanceof CHOQ_DB_Object) {
            $id = $key->getId();
            if(!$id) error("Could not use transient object '".get_class($key)."' for database usage, in member '{$member}'");
            $key = $id;
        }
        $existValue = $this->getByKey($property, $key);
        if($existValue !== null){
            unset($this->_realValues[$property][$key]);
            if(!$this->_realValues[$property]) $this->_realValues[$property] = null;
            $this->_changes[$property] = true;
        }
        return $this;
    }

    /**
    * Store the object
    */
    public function store(){
        $this->_db->store($this);
    }

    /**
    * Delete the object
    */
    public function delete(){
        $this->_db->delete($this);
    }

}