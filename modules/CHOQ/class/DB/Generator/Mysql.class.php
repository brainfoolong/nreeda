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
* Generate and Update Database for Mysql
*/
class CHOQ_DB_Generator_Mysql extends CHOQ_DB_Generator_Sql{

    /**
    * Set the database engine
    *
    * @var string
    */
    static $dbEngine = "InnoDB";

    /**
    * The Database Connection
    *
    * @var CHOQ_DB_Mysql
    */
    public $db;

    /**
    * Update the database
    * Only create missing members/tables, changes (alter) or deletes must be done manually
    */
    public function updateDatabase(){
        $this->db->query("ALTER DATABASE ".$this->db->quote($this->db->dbname)." DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        parent::updateDatabase();
    }

    /**
    * Get all existing Tables
    *
    * @return string[]
    */
    public function getExistingTables(){
        $rows = $this->db->fetchAsArray("SHOW TABLES");
        $tables = array();
        foreach($rows as $row){
            $tables[$row[0]] = strtolower($row[0]);
        }
        return $tables;
    }

    /**
    * Get all existing fields for a table
    *
    * @return string[]
    */
    public function getExistingFieldsForTable($table){
        $rows = $this->db->fetchAsArray("SHOW FIELDS FROM ".$this->db->quote($table));
        $fields = array();
        foreach($rows as $row){
            $fields[$row[0]] = strtolower($row[0]);
        }
        return $fields;
    }

    /**
    * Get existing indexes for a table
    *
    * @param string $table
    * @return string[]
    */
    public function getExistingIndexes($table){
        $rows = $this->db->fetchAsAssoc("SHOW INDEXES FROM ".$this->db->quote($table));
        $arr = array();
        foreach($rows as $row) $arr[$row["Key_name"]] = $row["Key_name"];
        return $arr;
    }

    /**
    * Create the table with just a id field
    *
    * @param mixed $table
    * @param mixed $ai Is auto increment
    */
    public function createTable($table, $ai = false){
        $query = "CREATE TABLE ".$this->db->quote($table)." (".$this->db->quote("id")." BIGINT(18) UNSIGNED NOT NULL ".($ai ? "AUTO_INCREMENT" : "")." PRIMARY KEY) ENGINE = ".self::$dbEngine;
        $this->db->query($query);
    }

    /**
    * Create the indexs
    *
    * @param string $table
    * @param string $name
    * @param string $type
    * @param array $fields
    */
    public function createIndex($table, $name, $type, array $fields){
        foreach($fields as $key => $field) $fields[$key] = $this->db->quote($field);
        $query = "ALTER TABLE ".$this->db->quote($table)." ADD ".strtoupper($type)." ".$this->db->quote($name)." (".implode(",", $fields).")";
        $this->db->query($query);
    }

    /**
    * Alter table
    *
    * @param string $table
    * @param string $fieldName
    * @param string $type
    * @param int $length
    * @param int $decimalLength
    * @param bool $unsigned
    * @param string $comment
    */
    public function alterTableAdd($table, $fieldName, $type, $length, $decimalLength, $unsigned, $comment = ""){
        $query = "ALTER TABLE ".$this->db->quote($table)." ADD ".$this->db->quote($fieldName)." ";
        $query .= $type." ";
        if(is_int($length)){
            if(is_int($decimalLength)){
                $query .= "({$length}, {$decimalLength})";
            }else{
                $query .= "({$length})";
            }
        }
        if($unsigned && preg_match("~int$|^double|^real|^float|^decimal|^serial~", $type)) $query .= " UNSIGNED ";
        $query .= " NULL ";
        if($comment) $query .= " COMMENT ".$this->db->toDb((string)$comment);
        $this->db->query($query);
    }

    /**
    * Optimize all existing tables
    * Warning: This does only work for  MyISAM-, BDB- and InnoDB tables
    */
    public function optimizeTables(){
        $tables = $this->getExistingTables();
        foreach($tables as $table => $tableLower){
            $this->db->query("OPTIMIZE TABLE `{$table}`");
        }
    }
}
