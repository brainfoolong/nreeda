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
    * The reference to the field
    *
    * @var Form_Field
    */
    public $field;

    /**
    * Constructor
    *
    * @param Form_Field $field
    */
    public function __construct(Form_Field $field){
        $this->field = $field;
    }

    /**
    * Convert value in the required member value
    *
    * @param mixed $submittedValue
    * @return mixed
    */
    public function convert($submittedValue){
        if($submittedValue === null) return null;
        $class = $this->field->dbTypeMember->fieldTypeArrayClass ? $this->field->dbTypeMember->fieldTypeArrayClass : $this->field->dbTypeMember->fieldTypeClass;
        if(is_array($submittedValue)){
            if($class){
                $value = $this->field->dbObject->_db->getByIds($class, $submittedValue);
                if(!$value) return null;
                return $value;
            }
            foreach($submittedValue as $key => $value){
                $submittedValue[$key] = $this->convert($value);
                if($submittedValue[$key] === null) unset($submittedValue[$key]);
            }
            return $submittedValue;
        }else{
            if($class){
                return $this->field->dbObject->_db->getById($class, $submittedValue);
            }
            return $this->field->dbTypeMember->convertFromDbValue($submittedValue);
        }
    }
}

