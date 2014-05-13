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
* Generate and Update Database for Sqlite3
*/
class CHOQ_DB_Generator_Sqlite3 extends CHOQ_DB_Generator_Sql{

    /**
    * The Database Connection
    *
    * @var CHOQ_DB_Sqlite3
    */
    public $db;

    /**
    * Get all existing Tables
    *
    * @return string[]
    */
    public function getExistingTables(){
        $rows = $this->db->fetchAsArray("select name from sqlite_master WHERE type = 'table'");
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
        $rows = $this->db->fetchAsArray("PRAGMA table_info($table)");
        $fields = array();
        foreach($rows as $row){
            $fields[$row[1]] = strtolower($row[1]);
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
        $rows = $this->db->fetchAsAssoc("select * from sqlite_master WHERE type = 'index'");
        $tables = array();
        foreach($rows as $row){
            if($row["tbl_name"] != $table) continue;
            $tables[$row["name"]] = strtolower($row["name"]);
        }
        return $tables;
    }

    /**
    * Create the table with just a id field
    *
    * @param mixed $table
    * @param mixed $ai Is auto increment
    */
    public function createTable($table, $ai = false){
        $query = "CREATE TABLE ".$this->db->quote($table)." (".$this->db->quote("id")." INTEGER NOT NULL PRIMARY KEY ".($ai ? "AUTOINCREMENT" : "").")";
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
        $query = "CREATE ".($type == "unique" ? "UNIQUE" : "")." INDEX  ".$this->db->quote($name)." ON ".$this->db->quote($table)." (".implode(",", $fields).")";
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
        if($unsigned && preg_match("~int$|^double|^real|^float|^decimal|^serial~", $type)) $query .= " UNSIGNED ";
        $query .= $type." ";
        if(is_int($length)){
            if(is_int($decimalLength)){
                $query .= "({$length}, {$decimalLength})";
            }else{
                $query .= "({$length})";
            }
        }
        $query .= " NULL ";
        $this->db->query($query);
    }
}
