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
* Validator
*/
abstract class Form_Validator{

    /**
    * The error message when validation is not true
    *
    * @var string
    */
    public $errorMessage;

    /**
    * The validator options
    *
    * @var array
    */
    public $options = array();

    /**
    * Return the json representation for this validator
    *
    * @return array
    */
    public function toJsonData(){
        return array("errorMessage" => $this->errorMessage, "options" => $this->options, "type" => get_class($this));
    }

    /**
    * Get option value
    *
    * @param mixed $key
    * @return string | NULL
    */
    public function getOption($key){
        return arrayValue($this->options, $key);
    }

    /**
    * Add a option
    *
    * @param mixed $key
    * @param mixed $value
    * @return self
    */
    public function addOption($key, $value){
        if($value === NULL) return $this;
        $key = (string)$key;
        $value = (string)$value;
        $this->options[$key] = $value;
        return $this;
    }

    /**
    * Set error message
    *
    * @param mixed $message
    * @return self
    */
    public function setErrorMessage($message){
        $this->errorMessage = $message;
        return $this;
    }

    /**
    * Validate the given value, which value is used for validation depends on the validator itself
    *
    * @param mixed $convertedValue
    * @param mixed $submittedValue
    * @return bool
    */
    abstract function validate($convertedValue, $submittedValue);
}

