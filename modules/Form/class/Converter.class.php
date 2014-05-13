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
* Converter
*/
class Form_Converter{

    /**
    * The member
    *
    * @var CHOQ_DB_TypeMember
    */
    public $member;

    /**
    * The db instance
    *
    * @var CHOQ_DB
    */
    public $db;

    /**
    * Set converter to convert to the type of this member
    *
    * @param CHOQ_DB_TypeMember $member
    * @return self
    */
    public function setMember(CHOQ_DB_TypeMember $member){
        $this->member = $member;
        return $this;
    }

    /**
    * Set converters db connection if required for a type member
    *
    * @param CHOQ_DB $db
    * @return self
    */
    public function setDb(CHOQ_DB $db){
        $this->db = $db;
        return $this;
    }

    /**
    * Convert value in the required member value
    *
    * @param mixed $submittedValue
    * @return mixed
    */
    public function convert($submittedValue){
        if($submittedValue === NULL) return NULL;
        $class = $this->member->fieldTypeArrayClass ? $this->member->fieldTypeArrayClass : $this->member->fieldTypeClass;
        if(is_array($submittedValue)){
            if($class){
                $value = $this->db->getByIds($class, $submittedValue);
                if(!$value) return NULL;
                return $value;
            }
            foreach($submittedValue as $key => $value){
                $submittedValue[$key] = $this->convert($value);
                if($submittedValue[$key] === NULL) unset($submittedValue[$key]);
            }
            return $submittedValue;
        }else{
            if($class){
                return $this->db->getById($class, $submittedValue);
            }
            return $this->member->convertFromDbValue($submittedValue);
        }
    }
}

