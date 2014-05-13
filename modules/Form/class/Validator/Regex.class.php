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
* Regex Validator
*/
class Form_Validator_Regex extends Form_Validator{

    /**
    * Set the regex that must match
    *
    * @param mixed $regex
    * @param mixed $regexOptions
    * @return self
    */
    public function setRegex($regex, $regexOptions = ""){
        $this->addOption("regex", $regex);
        $this->addOption("regexoptions", $regexOptions);
        return $this;
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
        return preg_match("~".$this->getOption("regex")."~".$this->getOption("regexoptions"), $submittedValue) ? true : false;
    }
}

