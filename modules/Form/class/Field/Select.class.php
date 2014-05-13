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
* A select field
*/
class Form_Field_Select extends Form_Field{

    /**
    * The options
    *
    * @var Form_Attributes[]
    */
    public $options = array();

    /**
    * The options labels
    *
    * @var array
    */
    public $labels = array();

    /**
    * Add a option - Override old keys
    *
    * @param mixed $key
    * @param mixed $label
    * @param mixed $attributes
    * @return self
    */
    public function addOption($key, $label, $attributes = NULL){
        $key = (string)$key;
        $label = (string)$label;
        if(!isset($this->options[$key])) $this->options[$key] = new Form_Attributes();
        $attr = $this->options[$key];
        $attr->add("value", $key);
        if(is_array($attributes)) foreach($attributes as $key => $value) $attr->add($key, $value);
        $this->labels[$key] = $label;
        return $this;
    }

    /**
    * Remove a option if exist
    *
    * @param mixed $key
    * @return self
    */
    public function removeOption($key){
        $key = (string)$key;
        if(isset($this->options[$key])) unset($this->options[$key]);
    }

    /**
    * Get html string for this field
    *
    * @return string
    */
    public function getHtml(){
        $defaultValue = $this->defaultValue;
        if(!is_array($defaultValue)) $defaultValue = array($defaultValue);
        foreach($defaultValue as $key => $value) $defaultValue[(string)$key] = (string)$value;

        $output = $this->htmlBeforeField.'<select '.$this->attributes->getHtml().'>';
        foreach($this->options as $key => $attr){
            $overrideAttributes = array();
            $key = (string)$key;
            if(in_array($key, $defaultValue, true)) $overrideAttributes["selected"] = "selected";
            $output .= "<option ".$attr->getHtml(NULL, $overrideAttributes).">".s($this->labels[$key])."</option>";
        }
        $output .= "</select>".$this->htmlAfterField;
        $output .= $this->getJsPart();
        return $output;
    }
}

