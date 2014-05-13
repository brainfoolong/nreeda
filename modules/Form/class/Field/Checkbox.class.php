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
* A checkbox field
*/
class Form_Field_Checkbox extends Form_Field_Input{

    /**
    * Get html string for this field
    *
    * @return string
    */
    public function getHtml(){
        $this->attributes->add("type", "checkbox");
        $this->attributes->add("value", "1");
        if($this->defaultValue) $this->attributes->setChecked(true);
        return $this->htmlBeforeField.'<input '.$this->attributes->getHtml().'/>'.$this->htmlAfterField.$this->getJsPart();
    }
}

