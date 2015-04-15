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
* A multiple select field
*/
class Form_Field_SelectMultiple extends Form_Field_Select{

    /**
    * Set size for this multiple field
    *
    * @param mixed $size
    * @return self
    */
    public function setSize($size){
        return $this->attr->add("size", $size);
    }

    /**
    * Get html string for this field
    *
    * @return string
    */
    public function getHtml(){
        $this->attr->add("multiple", "multiple");
        return parent::getHtml();
    }
}

