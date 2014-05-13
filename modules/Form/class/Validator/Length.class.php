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
* Length Validator
*/
class Form_Validator_Length extends Form_Validator{

    /**
    * Set limits for length validation
    *
    * @param int $min
    * @param int $max
    * @return Form_Validator
    */
    public function setLength($min = NULL, $max = NULL){
        return $this->addOption("min", $min)->addOption("max", $max);
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
            foreach($submittedValue as $value) {
                if(!$this->validate($value, $value)) return false;
            }
            return true;
        }
        $length = mb_strlen($submittedValue);
        if($this->getOption("min") !== NULL && $length < $this->getOption("min")) return false;
        if($this->getOption("max") !== NULL && $length > $this->getOption("max")) return false;
        return true;
    }
}

