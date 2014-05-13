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
* Sqlite3 DB Handler
*/
class CHOQ_DB_Sqlite3 extends CHOQ_DB_Sql{

   /**
    * Sqlite
    *
    * @var SQLite3
    */
    public $sqlite;

   /**
    * Last Result
    *
    * @var SQLite3Result
    */
    public $lastResult;

    /**
    * Check requirements for sqlite on the system
    *
    * @return bool
    */
    static function isAvailable(){
        return class_exists("SQLite3", false);
    }

    /**
    * Constructor
    *
    * @param array $connectionData Connection Data passed from the CHOQ_DB handler
    */
    public function __construct(array $connectionData){
        if(!self::isAvailable()) error("SQLite3 and/or SQLite Extension not installed - Update your PHP configuration");
        $path = $connectionData["path"];
        if(!is_dir(dirname($path)) or !is_writable(dirname($path))){
            error("Directory path '".dirname($path)."' does not exist or is not writeable. Create or update directory chmod");
        }
        $this->sqlite = new SQLite3($path, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $this->testError();
        $this->sqlite->busyTimeout(5000);
    }

    /**
    * Convert a string value for database usage
    *
    * @param string $value
    * @return string
    */
    public function toDbString($value){
        return $this->sqlite->escapeString($value);
    }

    /**
    * Convert a boolean value for database usage
    *
    * @param bool $value
    * @return string
    */
    public function toDbBool($value){
        return $value ? "1" : "0";
    }

    /**
    * Fetch rows of query result as associative array
    *
    * @param string $query
    * @param string $valueAsArrayIndex If not null you need to define a field name of the table
    *  e.g. your table has {id, name, email} fields, if you set it to 'name' the key of resulting
    *  array is the value of the field 'name'
    * @return array[]
    */
    public function fetchAsAssoc($query, $valueAsArrayIndex = NULL){
        $fetch = array();
        $this->query($query);
        while($row = $this->lastResult->fetchArray(SQLITE3_ASSOC)){
            if($valueAsArrayIndex){
                if(!isset($row[$valueAsArrayIndex])) error("Field '$valueAsArrayIndex' does not exist in SQL Result");
                $fetch[$row[$valueAsArrayIndex]] = $row;
            }else{
                $fetch[] = $row;
            }
        }
        return $fetch;
    }

    /**
    * Execute the query
    *
    * @param string $query
    * @return mixed
    */
    public function query($query){
        $this->lastResult = $this->sqlite->query($query);
        $this->testError($query);
        $this->logQuery($query);
    }

    /**
    * Test if a error exist, if yes than throw error
    *
    * @throws CHOQ_Exception
    * @param string $query
    */
    public function testError($query = NULL){
        if($this->sqlite->lastErrorCode()){
            $error = $this->sqlite->lastErrorMsg();
            if($query) $error .= "\nSQL Query: $query";
            error($error);
        }
    }

    /**
     * Get last insert Id
     *
     * @return int
     */
    public function getLastInsertId(){
        return $this->sqlite->lastInsertRowID();
    }

    /**
     * This function returns the number of database rows that were changed or inserted or deleted by the most recently completed SQL statement
     *
     * @return int
     */
    public function getAffectedRows(){
        return $this->sqlite->changes();
    }
}

