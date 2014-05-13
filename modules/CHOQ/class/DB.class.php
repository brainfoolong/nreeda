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
* DB
*/
abstract class CHOQ_DB{

    /**
    * A array of available db connections
    *
    * @var array[]
    */
    static $pool;

    /**
    * All instances
    *
    * @var CHOQ_DB[]
    */
    static $instances;

    /**
    * Is query log enabled
    *
    * @var bool
    */
    static $queryLog = false;

    /**
    * Array of stored queries, if log is enabled
    *
    * @var mixed
    */
    static $queries;

     /**
    * Self id
    *
    * @var string
    */
    public $id;

    /**
    * The identifier quote character at begin and end
    *
    * @var string[]
    */
    public $iqc = array("`", "`");

    /**
    * Database Name
    *
    * @var string
    */
    public $dbname;

    /**
    * Database User
    *
    * @var string
    */
    public $user;

    /**
    * Database Password
    *
    * @var string
    */
    public $password;

    /**
    * Database Host
    *
    * @var string
    */
    public $host;

    /**
    * Database Port
    *
    * @var int
    */
    public $port;

    /**
    * Add a DB Connection
    *
    * @param string $id
    * @param string $connectUrl Anything parseable by parse_url()
    */
    static function add($id = "default", $connectUrl){
        if(substr($connectUrl, 0, 10) == "sqlite3://"){
            $parsedUrl = array("scheme" => "sqlite3", "path" => substr($connectUrl, 10));
        }else{
            $parsedUrl = parse_url($connectUrl);
        }
        self::$pool[$id] = $parsedUrl;
    }

    /**
    * Get a DB instance
    *
    * @param string $id The DB Identifier which added from self::add()
    * @return CHOQ_DB
    */
    static function get($id = "default"){
        if(!isset(self::$pool[$id])) error("No such '$id' DB connection added");
        if(isset(self::$instances[$id])) return self::$instances[$id];
        $connectionData = self::$pool[$id];
        $scheme = ucfirst(strtolower($connectionData["scheme"]));
        $type = "CHOQ_DB_$scheme";
        self::$instances[$id] = new $type($connectionData);
        self::$instances[$id]->id = $id;
        return self::$instances[$id];
    }

    /**
    * Quote a or multiple Identifier
    * Multiple identifiers combined with a dot
    *
    * @param string $identifier,...
    * @return string
    */
    abstract public function quote();

    /**
    * Convert a value for database interaction
    * Quotes automaticly added if necessary
    *
    * @param mixed $value
    * @return mixed
    */
    abstract public function toDb($value);

    /**
    * Convert a boolean value for database usage
    *
    * @param bool $value
    * @return string
    */
    abstract public function toDbBool($value);

    /**
    * Convert a string value for database usage
    *
    * @param string $value
    * @return string
    */
    abstract public function toDbString($value);

    /**
    * Execute multiple queries
    *
    * @param array $querys Array of queries
    */
    abstract public function multipleQuery(array $querys);

    /**
    * Fetch rows of query result as numeric array
    *
    * @param string $query
    * @return array[]
    */
    abstract public function fetchAsArray($query);

    /**
     * Fetch first row of query result as associative array
     *
     * @param string $query
     * @return array
     */
    abstract public function fetchAsAssocOne($query);

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
    abstract public function fetchColumn($query, $valueAsArrayIndex = NULL);

    /**
    * Fetch first column value of first row
    *
    * @param string $query
    * @return string|NULL
    */
    abstract public function fetchOne($query);

    /**
    * Fetch rows of query result as associative array
    *
    * @param string $query
    * @param string $valueAsArrayIndex If not null you need to define a field name of the table
    *  e.g. your table has {id, name, email} fields, if you set it to 'name' the key of resulting
    *  array is the value of the field 'name'
    * @return array[]
    */
    abstract public function fetchAsAssoc($query, $valueAsArrayIndex = NULL);

    /**
    * Execute the query
    *
    * @param string $query
    * @return mixed
    */
    abstract public function query($query);

    /**
    * Test if a error exist
    *
    * @throws CHOQ_Exception
    */
    abstract public function testError();

    /**
     * Get last insert Id
     *
     * @return int
     */
    abstract public function getLastInsertId();

    /**
     * This function returns the number of database rows that were changed or inserted or deleted by the most recently completed SQL statement
     *
     * @return int
     */
    abstract public function getAffectedRows();

    /**
    * Store object
    *
    * @param CHOQ_DB_Object $object
    */
    abstract public function store(CHOQ_DB_Object $object);

    /**
    * Delete object
    *
    * @param CHOQ_DB_Object $object
    */
    abstract public function delete(CHOQ_DB_Object $object);

    /**
    * Get object by id
    *
    * @param string|NULL $type If NULL than auto detect the type
    * @param int $id
    * @return CHOQ_DB_Object|NULL
    */
    abstract public function getById($type, $id);

    /**
    * Get objects by ids
    *
    * @param string|NULL $type If NULL than auto detect the type
    * @param array $id
    * @param bool $resort If true than the resulted array is in the same sort as the given ids
    * @return CHOQ_DB_Object[]
    */
    abstract public function getByIds($type, array $ids, $resort = false);

    /**
    * Get objects by condition
    *
    * @param string $type
    * @param string|NULL $condition If NULL than no condition is added (getAll)
    *   To add a parameters placeholder add brackets with the parameters key - Example: {mykey}
    *   To quote fieldNames correctly enclose a fieldName with <fieldName>
    * @param mixed $parameters Can be a array of parameters, a single parameter or NULL
    * @param mixed $sort Can be a array of sorts, a single sort or NULL
    *   Sort value must be a fieldName with a +/- prefix - Example: -id
    *   + means sort ASC
    *   - means sort DESC
    * @param int|NULL $limit Define a limit for the query
    * @param int|NULL $offset Define a offset for the query
    * @return CHOQ_DB_Object[]
    */
    abstract public function getByCondition($type, $condition = NULL, $parameters = NULL, $sort = NULL, $limit = NULL, $offset = NULL);

    /**
    * Get objects by own defined query
    * You only MUST select the id of the table - SELECT id FROM ...
    *
    * @param string $type
    * @param mixed $query
    * @return CHOQ_DB_Object[]
    */
    abstract public function getByQuery($type, $query);

    /**
    * Lazy load array member - Fetch all array values for all objects that stored in the cache
    *
    * @param CHOQ_DB_Object $object
    * @param CHOQ_DB_TypeMember $member
    */
    abstract public function lazyLoadArrayMember(CHOQ_DB_Object $object, CHOQ_DB_TypeMember $member);

    /**
    * Store multiple objects
    *
    * @param CHOQ_DB_Object[] $objects
    */
    final public function storeMultiple(array $objects){
        foreach($objects as $object) $object->store();
    }

    /**
    * Delete multiple objects
    *
    * @param CHOQ_DB_Object[] $objects
    */
    final public function deleteMultiple(array $objects){
        foreach($objects as $object) $object->delete();
    }

    /**
    * Log query if logging is enabled
    *
    * @param string $query
    */
    final public function logQuery($query){
        if(!self::$queryLog) return;
        if(!isset(self::$queries[$this->id])) self::$queries[$this->id] = array();
        self::$queries[$this->id][] = $query;
    }
}