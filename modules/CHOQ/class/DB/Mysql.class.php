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
* Mysql DB Handler
*/
class CHOQ_DB_Mysql extends CHOQ_DB_Sql{

   /**
    * MySQLi
    *
    * @var MySQLi
    */
    public $mysqli;

   /**
    * Last Result
    *
    * @var MySQLi_Result
    */
    public $lastResult;

    /**
    * Check requirements for mysql on the system
    *
    * @return bool
    */
    static function isAvailable(){
        return class_exists("MySQLi", false);
    }

    /**
    * Constructor
    *
    * @param array $connectionData Connection Data parsed by parse_url()
    */
    public function __construct(array $connectionData){
        if(!self::isAvailable()) error("MySQLi not installed - Update your PHP configuration");
        $port = isset($connectionData["port"]) ? (int)$connectionData["port"] : 3306;
        $pass = isset($connectionData["pass"]) ? $connectionData["pass"] : NULL;
        $db = trim($connectionData["path"], "/ ");
        $this->dbname = $db;
        $this->user = $connectionData["user"];
        $this->password = $pass;
        $this->host = $connectionData["host"];
        $this->port = $port;
        $this->mysqli = new MySQLi($this->host, $this->user, $this->password, $this->dbname, $this->port);
        $this->testError();
        $this->mysqli->set_charset(str_replace("-", "", CHOQ::$encoding));
    }

    /**
    * Convert a string value for database usage
    *
    * @param string $value
    * @return string
    */
    public function toDbString($value){
        return $this->mysqli->real_escape_string($value);
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
    public function fetchAsAssoc($query, $valueAsArrayIndex = null){
        $fetch = array();
        $this->query($query);
        while($row = $this->lastResult->fetch_assoc()){
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
        $this->lastResult = $this->mysqli->query($query);
        $this->testError($query);
        $this->logQuery($query);
    }

    /**
    * Test if a error exist, if yes than throw error
    *
    * @throws CHOQ_Exception
    * @param string $query
    */
    public function testError($query = null){
        if($this->mysqli->connect_error){
            error("Mysql Connect Error for DBID '".$this->id."'");
        }
        if($this->mysqli->error){
            # retry query when deadlock error is found
            if($this->mysqli->errno == 1213){
                $this->query($query);
                return;
            }
            $error = $this->mysqli->error;
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
        return $this->mysqli->insert_id;
    }

    /**
     * This function returns the number of database rows that were changed or inserted or deleted by the most recently completed SQL statement
     *
     * @return int
     */
    public function getAffectedRows(){
        return $this->mysqli->affected_rows;
    }
}

