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
* Required Validator
*/
class Form_Validator_Required extends Form_Validator{

    /**
    * Constructor
    */
    public function __construct(){
        $this->setErrorMessage(t("form.validation.required"));
    }

    /**
    * Trim values before validation
    *
    * @param mixed $trimChar
    * @return self
    */
    public function setTrim($trimChar = " \n\r\t"){
        return $this->addOption("trim", $trimChar);
    }

    /**
    * Validate the given value, which value is used for validation depends on the validator itself
    *
    * @param mixed $convertedValue
    * @param string $submittedValue
    * @return bool
    */
    public function validate($convertedValue, $submittedValue){
        if(is_array($submittedValue)) {
            if(!count($submittedValue)) return false;
            foreach($submittedValue as $value) {
                if(!$this->validate($value, $value)) return false;
            }
            return true;
        }
        if($this->getOption("trim") !== null) $submittedValue = trim($submittedValue, $this->getOption("trim"));
        if(!mb_strlen($submittedValue)) return false;
        return true;
    }
}

