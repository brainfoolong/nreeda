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
* SQL Database Abstraction
*/
abstract class CHOQ_DB_Generator_Sql extends CHOQ_DB_Generator{

    /**
    * Update the database
    * Only create missing members/tables, changes (alter) or deletes must be done manually
    */
    public function updateDatabase(){
        $metatable = CHOQ_DB_Object::METATABLE;
        $tables = $this->getExistingTables();
        if(!isset($tables[$metatable])){
            $this->createTable($metatable, true);
            $this->alterTableAdd($metatable, "type", "varchar", 255, NULL, NULL);
        }
        $arr = $this->getRequiredUpdates();
        foreach($arr as $tableLower => $data){
            if(!$data) continue;
            if($data["new"]) $this->createTable($data["type"]->class);
            foreach($data["members"] as $member){
                if($member && $member->new[$tableLower]){
                    if($member->fieldType == "array"){
                        $this->alterTableAdd(
                            $data["type"]->class,
                            $member->name,
                            "int",
                            10,
                            NULL,
                            true
                        );
                        $arrayTable = $data["type"]->class."_".$member->name;
                        $this->createTable($arrayTable, true);
                        $this->alterTableAdd($arrayTable, "o", "bigint", 18, NULL, true);
                        if($member->fieldTypeArrayClass){
                            $this->alterTableAdd($arrayTable, "k", "bigint", 18, NULL, true);
                            $this->alterTableAdd($arrayTable, "v", "bigint", 18, NULL, true);
                        }else{
                            $this->alterTableAdd($arrayTable, "k", "varchar", 255, NULL, NULL);
                            $this->alterTableAdd($arrayTable, "v", $member->fieldTypeArray, $member->length, $member->decimalLength, $member->unsigned);
                        }
                        $this->createIndex($arrayTable, $this->getIndexName(array($arrayTable, "o")), "index", array("o"));
                        $this->createIndex($arrayTable, $this->getIndexName(array($arrayTable, "ok")), "unique", array("o", "k"));
                    }elseif($member->fieldTypeClass){
                        $this->alterTableAdd(
                            $data["type"]->class,
                            $member->name,
                            "bigint",
                            18,
                            NULL,
                            true,
                            $member->fieldTypeClass
                        );
                        $this->createIndex($data["type"]->class, $this->getIndexName(array($data["type"]->class, $member->name)), "index", array($member->name));
                    }else{
                        $this->alterTableAdd(
                            $data["type"]->class,
                            $member->name,
                            $member->fieldType,
                            $member->length,
                            $member->decimalLength,
                            $member->unsigned
                        );
                    }
                }
            }
            $indexes = $this->getExistingIndexes($data["type"]->class);
            $requiredIndexes = $this->getRequiredIndexes($data["type"]->class);
            foreach($requiredIndexes as $name => $index){
                if(!isset($indexes[$name])) {
                    $this->createIndex($data["type"]->class, $name, $index[0], $index[1]);
                }
            }
        }
    }
}
