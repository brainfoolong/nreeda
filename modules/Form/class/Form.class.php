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
* Form
*/
class Form_Form{

    /**
    * The name of this form
    *
    * @var string
    */
    public $name;

    /**
    * The attributes
    *
    * @var Form_Attributes
    */
    public $attributes;

    /**
    * The fields
    *
    * @var Form_Field[]
    */
    public $fields = array();

    /**
    * Constructor
    *
    * @param mixed $name
    * @return Form
    */
    public function __construct($name){
        $this->attributes = new Form_Attributes(array("name" => $name, "method" => "post", "action" => "", "id"=> "form-".$name));
    }

    /**
    * Enable file upload for this form
    *
    * @return self
    */
    public function enableFileUpload(){
        $this->attributes->add("enctype", "multipart/form-data");
        return $this;
    }

    /**
    * Add a field
    *
    * @param Form_Field $field
    * @return self
    */
    public function addField(Form_Field $field){
        $this->fields[$field->name] = $field;
        return $this;
    }

    /**
    * Validate all given fields
    *
    * @return bool
    */
    public function validateAllFields(){
        foreach($this->fields as $field){
            $ret = $field->validate();
            if(!$ret) return false;
        }
        return true;
    }


    /**
    * Set members in the given object
    *
    * @param CHOQ_DB_Object $object
    */
    public function setObjectMembersBySubmittedValues(CHOQ_DB_Object $object){
        foreach($this->fields as $field){
            if($field->member){
                $value = $field->getSubmittedValue(true);
                if(is_array($value)){
                    $object->{$field->member->name} = $value;
                }else{
                    preg_match("~\[(.*?)\]~i", $field->name, $arrayKey);
                    if($arrayKey){
                        $key = $arrayKey[1];
                        $object->remove($field->member->name, $key);
                        if($value !== NULL){
                            $object->add($field->member->name, $value, $key);
                        }
                    }else{
                        $object->{$field->member->name} = $value;
                    }
                }
            }
        }
    }
}

