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
* Generate and Update Database
*/
abstract class CHOQ_DB_Generator{

    /**
    * Modules need for generator
    *
    * @var CHOQ_Module[]
    */
    public $modules = array();

    /**
    * The Database Connection
    *
    * @var CHOQ_DB
    */
    public $db;

    /**
    * Create a generator instance
    *
    * @param CHOQ_DB $db
    * @return self
    */
    static function create(CHOQ_DB $db){
        $class = explode("_", get_class($db));
        $class = "CHOQ_DB_Generator_".array_pop($class);
        $object = new $class;
        $object->db = $db;
        return $object;
    }

    /**
    * Update the database
    * Only create missing members/tables, changes (alter) or deletes must be done manually
    */
    abstract function updateDatabase();

    /**
    * Get all existing Tables
    *
    * @return string[]
    */
    abstract public function getExistingTables();

    /**
    * Get all existing fields for a table
    *
    * @return string[]
    */
    abstract public function getExistingFieldsForTable($table);

    /**
    * Get existing indexes for a table
    *
    * @param string $table
    * @return string[]
    */
    abstract public function getExistingIndexes($table);

    /**
    * Create the table with just a id field
    *
    * @param mixed $table
    * @param mixed $ai Is auto increment
    */
    abstract public function createTable($table, $ai = false);

    /**
    * Create the indexs
    *
    * @param string $table
    * @param string $name
    * @param string $type
    * @param array $fields
    */
    abstract public function createIndex($table, $name, $type, array $fields);

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
    abstract public function alterTableAdd($table, $fieldName, $type, $length, $decimalLength, $unsigned, $comment = "");

    /**
    * Add a Module for Database Generation
    *
    * @param string $module
    */
    public function addModule($module){
        $this->modules[$module] = CHOQ_Module::get($module);
        $this->registerTypesOfAllModules();
    }

    /**
    * Get all required updates
    *
    * @return mixed[]
    */
    public function getRequiredUpdates(){
        $arr = array();
        foreach(CHOQ_DB_Type::$instances as $type){
            $parents = array($type->class) + class_parents($type->class);
            foreach($parents as $class){
                $subType = CHOQ_DB_Type::get($class);
                $table = strtolower($type->class);
                if(!isset($arr[$table])) $arr[$table] = array("type" => $type, "members" => array(), "new" => true);
                foreach($subType->members as $member){
                    $member->new[$table] = true;
                    $arr[$table]["members"][strtolower($member->name)] = $member;
                }
            }
        }
        $tables = $this->getExistingTables();
        foreach($tables as $tKey => $tableLower){
            $fields = $this->getExistingFieldsForTable($tKey);
            if(isset($arr[$tableLower])) {
                $arr[$tableLower]["new"] = false;
                unset($tables[$tKey]);
            }
            foreach($fields as $key => $fieldLower){
                if(isset($arr[$tableLower]["members"][$fieldLower])){
                    $arr[$tableLower]["members"][$fieldLower]->new[$tableLower] = false;
                    unset($fields[$key]);
                }
            }
            foreach($fields as $fieldLower){
                $arr[$tableLower]["members"][$fieldLower] = false;
            }
            unset($arr[$tableLower]["members"]["id"]);
        }
        foreach($tables as $tableLower){
            if($tableLower == strtolower(CHOQ_DB_Object::METATABLE) && isset($arr[$tableLower])) {
                unset($arr[$tableLower]);
                continue;
            }
            $arr[$tableLower] = false;
        }
        unset($arr["choq_db_object"]);
        return $arr;
    }

    /**
    * Get a html print for required updates
    *
    * @return mixed[]
    */
    public function printRequiredUpdates(){
        $arr = $this->getRequiredUpdates();
        $tables = $this->getExistingTables();
        echo '
            <style type="text/css">
            span.new, span.deletable, span.exist{
                font-size:80%;
                color:#888888;
            }
            span.deletable{color:blue;}
            span.new{color:red;}
            span.exist{color:green;}
            </style>
            <h2>Required Table Updates - All table/field names are displayed in lowercase</h2>
        ';
        echo '<ul>';
        foreach($arr as $table => $data){
            if($data){
                echo '<li>'.$table;
                if($data["new"]) echo '<span class="new"> (New)</span>';
                echo '<ul>';
                foreach($data["members"] as $memberName => $member){
                    echo '<li>'.$memberName;
                    if($member){
                        echo s("<".$member->fieldType);
                        if($member->fieldTypeArray) echo s("<".$member->fieldTypeArray.">");
                        echo s(">");
                    }

                    if(!$member){
                        echo '<span class="deletable">';
                        echo ' (Deletable)';
                    }elseif($member->new[$table]){
                        echo '<span class="new">';
                        echo ' (New)';
                    }elseif(!$member->new[$table]){
                        echo '<span class="exist">';
                        echo ' (Exist)';
                    }
                    echo '</span></li>';
                }
                $requiredIndexes = $this->getRequiredIndexes($data["type"]->class);
                $indexes = array();
                if(in_array($table, $tables)){
                    $indexes = $this->getExistingIndexes($data["type"]->class);
                }
                foreach($requiredIndexes as $name => $index){
                    echo '<li>Index: '.strtoupper($index[0]).' ('.implode(",", $index[1]).') ';
                    if(!isset($indexes[$name])) {
                        echo '<span class="new">New</span>';
                    }else{
                        echo '<span class="exist">Exist</span>';
                    }
                }
            }
            echo "</ul></li>";
        }
        echo '</ul>';
    }

    /**
    * Register all types of added modules
    */
    public function registerTypesOfAllModules(){
        foreach($this->modules as $module){
            $files = CHOQ_FileManager::getFiles($module->directory.DS."type", true, true);
            foreach($files as $file){
                if(!is_dir($file) && strpos($file, ".class.php") !== false) {
                    $class = str_replace(array(CHOQ_ROOT_DIRECTORY.DS."modules".DS, DS, "_type_"), array("", "_", "_"), $file);
                    $class = substr($class, 0, strpos($class, "."));
                    CHOQ_ClassManager::loadClass($class);
                }
            }
        }
    }

    /**
    * Get a hashed index name
    *
    * @param array $parts
    * @return string
    */
    public function getIndexName(array $parts){
        return "i".saltedHash("crc32b", implode("-", $parts));
    }

    /**
    * Get required indexes for a type
    *
    * @param mixed $type
    * @return array
    */
    public function getRequiredIndexes($type){
        $requiredIndexes = array();
        $object = new $type($this->db);
        foreach($object->_getMembers() as $member){
            foreach($member->type->indexes as $name => $index){
                $name = $this->getIndexName(array($name, $index[0], $type));
                $requiredIndexes[$name] = $index;
            }
        }
        return $requiredIndexes;
    }
}
