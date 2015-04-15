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
* Abstract SQL Database Basic
*/
abstract class CHOQ_DB_Sql extends CHOQ_DB{

    /**
    * Quote a or multiple Identifier
    * Multiple identifiers combined with a dot
    *
    * @param string $identifier,...
    * @return string
    */
    public function quote(){
        $args = func_get_args();
        $out = "";
        foreach($args as $id) $out .= $this->iqc[0].$id.$this->iqc[1].".";
        return rtrim($out, ".");
    }

    /**
    * Convert a value for database interaction
    * Quotes automaticly added if necessary
    *
    * @param mixed $value
    * @return mixed
    */
    public function toDb($value){
        if(is_object($value)){
            $value = $value->__toString();
        }
        if(is_null($value)){
            $value = "NULL";
        }elseif(is_bool($value)){
            $value = $this->toDbBool($value);
        }elseif(is_string($value)){
            $value = "'".$this->toDbString($value)."'";
        }elseif(is_array($value)){
            if(!$value) error("Array contains no values - Could not use empty arrays for database usage");
            $return = "(";
            foreach($value as $val) $return .= $this->toDb($val).", ";
            $return = trim($return, " ,").")";
            $value = $return;
        }
        return $value;
    }

    /**
    * Convert a boolean value for database usage
    *
    * @param bool $value
    * @return string
    */
    public function toDbBool($value){
        return $value ? "TRUE" : "FALSE";
    }

    /**
    * Execute multiple querys
    *
    * @param array $querys Array of queries
    */
    public function multipleQuery(array $querys){
        foreach($querys as $query) $this->query($query);
    }

    /**
    * Fetch rows of query result as numeric array
    *
    * @param string $query
    * @return array[]
    */
    public function fetchAsArray($query){
        $arr = $this->fetchAsAssoc($query);
        $new = array();
        $c1 = 0;
        foreach($arr as $row){
            $c2 = 0;
            foreach($row as $value){
                $new[$c1][$c2] = $value;
                $c2++;
            }
            $c1++;
        }
        return $new;
    }

    /**
     * Fetch first row of query result as associative array
     *
     * @param string $query
     * @return array
     */
    public function fetchAsAssocOne($query){
        $fetch = $this->fetchAsAssoc($query);
        if($fetch) $fetch = reset($fetch);
        return $fetch;
    }

    /**
    * Fetch only the values of the first column
    * Resulting in a array(0 => value, 1 => value)
    *
    * @param string $query
    * @param string $valueAsArrayIndex If not null you need to define a field name of the table
    *  e.g. your table has {id, name, email} fields, if you set it to 'name' the key of resulting
    *  array is the value of the field 'name'
    * @return array
    */
    public function fetchColumn($query, $valueAsArrayIndex = null){
        $arr = $this->fetchAsAssoc($query, $valueAsArrayIndex);
        if($arr){
            return array_combine(array_keys($arr), arrayMapProperty($arr, NULL));
        }
        return array();
    }

    /**
    * Fetch first column value of first row
    *
    * @param string $query
    * @return string|null
    */
    public function fetchOne($query){
        $arr = $this->fetchAsAssocOne($query);
        if($arr) return array_shift($arr);
    }

    /**
    * Get object by id
    *
    * @param string|null $type If null than auto detect the type
    * @param int $id
    * @return CHOQ_DB_Object|null
    */
    public function getById($type, $id){
        $tmp = $this->getByIds($type, array($id));
        if($tmp) return reset($tmp);
    }

    /**
    * Get objects by ids
    *
    * @param string|null $type If null than auto detect the type
    * @param array $id
    * @param bool $resort If true than the resulted array is in the same sort as the given ids
    * @return CHOQ_DB_Object[]
    */
    public function getByIds($type, array $ids, $resort = false){
        $ids = array_unique($ids);
        $tmp = $ids;
        $array = array();
        foreach($ids as $k => $id){
            $object = CHOQ_DB_Object::getCachedObjectById($this, $id);
            if($object){
                $array[$id] = $object;
                unset($ids[$k]);
            }
        }
        if($ids){
            if(!$type){
                $fetch = $this->fetchColumn("
                    SELECT ".$this->quote("type").", ".$this->quote("id")."
                    FROM ".$this->quote(CHOQ_DB_Object::METATABLE)."
                    WHERE ".$this->quote("id")." IN ".$this->toDb($ids)."
                ", "id");
                $idGroups = array();
                foreach($fetch as $id => $type){
                    $idGroups[$type][$id] = $id;
                }
                foreach($idGroups as $type => $ids){
                    $array += $this->getByCondition($type, $this->quote("id")." IN {0}", array($ids));
                }
            }else{
                $array += $this->getByCondition($type, $this->quote("id")." IN {0}", array($ids));
            }
        }
        if($resort){
            $newArray = array();
            foreach($tmp as $id){
                if(isset($array[$id])) $newArray[$id] = $array[$id];
            }
            $array = $newArray;
        }
        return $array;
    }

    /**
    * Get objects by condition
    *
    * @param string $type
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
    * @return CHOQ_DB_Object[]
    */
    public function getByCondition($type, $condition = null, $parameters = null, $sort = null, $limit = null, $offset = null){
        $t = CHOQ_DB_Type::get($type);
        if(!$t) error("Cannot fetch '{$type}' - doesn't exist");
        $query = "SELECT * FROM ".$this->quote($type);
        if($condition !== null){
            if($parameters !== null){
                if(!is_array($parameters)) $parameters = array($parameters);
                foreach($parameters as $key => $value) $condition = str_replace('{'.$key.'}', $this->toDb($value), $condition);
            }
            $query .= " WHERE $condition";
        }
        if($sort !== null){
            if(!is_array($sort)) $sort = array($sort);
            $query .= " ORDER BY ";
            foreach($sort as $value) {
                $dir = substr($value, 0, 1);
                if($dir != "+" && $dir != "-") error("Sort direction must be defined - Add a +/- before the fieldName");
                $dir = $dir == "+" ? "ASC" : "DESC";
                $query .= $this->quote(substr($value, 1))." ".$dir.", ";
            }
            $query = rtrim($query, ", ");
        }
        if(is_int($limit)) $query .= " LIMIT ".$limit;
        if(is_int($offset)) $query .= " OFFSET ".$offset;
        $fetch = $this->fetchAsAssoc($query);
        $arr = array();
        foreach($fetch as $row){
            $object = CHOQ_DB_Object::getCachedObjectById($this, $row["id"]);
            if($object){
                $arr[$row["id"]] = $object;
                continue;
            }
            $arr[$row["id"]] = CHOQ_DB_Object::_createFromFetch($this, $type, $row);
            CHOQ_DB_Object::_addToCache($arr[$row["id"]]);
        }
        return $arr;
    }

    /**
    * Get objects by own defined query
    * You only MUST select the id of the table - SELECT id FROM ...
    *
    * @param string $type
    * @param mixed $query
    * @return CHOQ_DB_Object[]
    */
    public function getByQuery($type, $query){
        return $this->getByIds($type, $this->fetchColumn($query));
    }

    /**
    * Store object
    *
    * @param CHOQ_DB_Object $object
    */
    public function store(CHOQ_DB_Object $object){
        $id = $initId = $object->getId();
        $type = $object->_getType();
        $members = $object->_getMembers();

        if(!$object->createTime) $object->createTime = dt("now");
        if(!$id || !$object->updateTime) $object->updateTime = dt("now");

        $dbValuesChanged = array();
        foreach($members as $member){
            if(!$id || isset($object->_changes[$member->name])){
                $value = $object->{$member->name};
                if($value !== null){
                    if($member->fieldType == "array"){
                        $value = count($value);
                        $dbValue = (string)$value;
                    }else{
                        $dbValue = $member->convertToDbValue($value);
                    }
                }else{
                    $dbValue = null;
                }

                if(!$member->optional && $dbValue === null) {
                    error("'$member' is not optional - Must be set ".$value);
                }
                $existingDbValue = arrayValue($object->_dbValues, $member->name);
                if(!$id || $member->fieldType == "array" || $existingDbValue !== $dbValue || (string)$existingDbValue !== (string)$dbValue){
                    $object->_dbValues[$member->name] = $dbValuesChanged[$member->name] = $dbValue !== null ? (string)$dbValue : NULL;
                }
            }
        }
        if(!$id){
            $this->query("INSERT INTO ".$this->quote(CHOQ_DB_Object::METATABLE)." (".$this->quote("id").", ".$this->quote("type").") VALUES (NULL, ".$this->toDb($type->class).")");
            $id = (int)$this->getLastInsertId();
            $object->_dbValues["id"] = $id;
            $query = "INSERT INTO ".$this->quote($type->class)." (".$this->quote("id").", ";
            foreach($dbValuesChanged as $key => $value) $query .= $this->quote($key).", ";
            $query = substr($query, 0, -2).") VALUES (".$this->toDb($id).", ";
            foreach($dbValuesChanged as $key => $value) $query .= $this->toDb($value).", ";
            $query = substr($query, 0, -2).")";
            $this->query($query);
        }else{
            $set = null;
            foreach($dbValuesChanged as $key => $value){
                $set .= $this->quote($key)." = ".$this->toDb($value).", ";
            }
            if(!$set) return;
            $object->updateTime = dt("now");
            $object->_dbValues["updateTime"] = $member->convertToDbValue($object->updateTime);
            $member = $object->_getMember("updateTime");
            $set .= $this->quote("updateTime")." = ".$this->toDb($object->_dbValues["updateTime"]);
            $query = "UPDATE ".$this->quote($type->class)." SET {$set} WHERE ".$this->quote("id")." = $id";
            $this->query($query);
        }
        # array
        $o = $this->quote("o");
        $k = $this->quote("k");
        $v = $this->quote("v");
        foreach($members as $member) {
            if($member->fieldType == "array"){
                if(array_key_exists($member->name, $dbValuesChanged)){
                    $table = $this->quote($member->type->class."_".$member->name);
                    $values = $object->getByKey($member->name);
                    $arrayIds = db()->fetchAsAssoc("SELECT * FROM {$table} WHERE $o = ".$this->toDb($id), "k");
                    if($values){
                        foreach($values as $key => $value){
                            $value = $this->toDb($member->convertToDbValue($value));
                            if(isset($arrayIds[$key])){
                                if((string)$value !== (string)$arrayIds[$key]["v"]){
                                    db()->query("UPDATE {$table} SET $v = $value WHERE ".$this->quote("id")." = ".$this->toDb($arrayIds[$key]["id"]));
                                }
                                unset($arrayIds[$key]);
                            }else{
                                db()->query("INSERT INTO {$table} ($o, $k, $v) VALUES (".$this->toDb($id).", ".$this->toDb($key).", ".$value.")");
                            }
                        }
                    }
                    if($arrayIds) db()->query("DELETE FROM {$table} WHERE ".$this->quote("id")." IN ".$this->toDb(arrayMapProperty($arrayIds, "id")));
                }
            }
        }
        CHOQ_DB_Object::_addToCache($object);
        $object->_changes = null;
    }

    /**
    * Delete object
    *
    * @param CHOQ_DB_Object $object
    */
    public function delete(CHOQ_DB_Object $object){
        $id = $object->getId();
        if(!$id) return;
        $type = $object->_getType();
        $members = $object->_getMembers();
        $queries = array();
        $o = $this->quote("o");
        $k = $this->quote("k");
        $v = $this->quote("v");
        foreach($members as $member){
            if($member->fieldType == "array"){
                $table = $this->quote($member->type->class."_".$member->name);
                $queries[] = "DELETE FROM {$table} WHERE $o = ".$this->toDb($id);
            }
        }
        $queries[] = "DELETE FROM ".$this->quote($type->class)." WHERE ".$this->quote("id")." = ".$this->toDb($id);
        $queries[] = "DELETE FROM ".$this->quote(CHOQ_DB_Object::METATABLE)." WHERE ".$this->quote("id")." = ".$this->toDb($id);
        $this->multipleQuery($queries);
        CHOQ_DB_Object::_removeFromCache($object);
        $object->_clear();
    }

    /**
    * Lazy load array member - Fetch all array values for all objects that stored in the cache
    *
    * @param CHOQ_DB_Object $object
    * @param CHOQ_DB_TypeMember $member
    */
    public function lazyLoadArrayMember(CHOQ_DB_Object $object, CHOQ_DB_TypeMember $member){
        $db = $member->getDb ? $member->getDb : $this;
        $objects = CHOQ_DB_Object::getCachedObjects($db, $object->_getType());
        $objects[$object->getId()] = $object;
        $ids = array();
        if($objects){
            foreach($objects as $o){
                if(!isset($o->_loaded[$member->name]) && isset($o->_dbValues[$member->name])) {
                    $ids[$o->getId()] = $o->getId();
                }
                $o->_loaded[$member->name] = true;
            }
        }
        if($ids){
            $table = $db->quote($member->type->class."_".$member->name);
            $tmp = $db->fetchAsAssoc("SELECT * FROM {$table} WHERE ".$db->quote("o")." IN ".$db->toDb($ids));
            if($member->fieldTypeArrayClass){
                $arr = $db->getByIds(NULL, arrayMapProperty($tmp, "v"));
                foreach($tmp as $row){
                    if(isset($arr[$row["v"]])){
                        $objects[$row["o"]]->add($member->name, $arr[$row["v"]], $arr[$row["v"]]->getId(), false);
                    }
                }
            }else{
                foreach($tmp as $row){
                    if(isset($ids[$row["o"]])){
                        $objects[$row["o"]]->add($member->name, $member->convertFromDbValue($row["v"]), $row["k"], false);
                    }
                }
            }
        }
    }
}

