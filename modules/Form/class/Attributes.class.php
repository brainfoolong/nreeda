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
* Form Attributes
*/
class Form_Attributes{

    /**
    * Array of attributes
    *
    * @var array
    */
    public $arr = array();

    /**
    * A array for the class attribute
    *
    * @var array
    */
    public $class = array();

    /**
    * Constructor
    *
    * @param mixed $attributes
    * @return Form_Attributes
    */
    public function __construct($attributes = NULL){
        $this->add("id", "field-".md5(uniqid(NULL, true)));
        if(is_array($attributes)){
            foreach($attributes as $key => $value) $this->add($key, $value);
        }
    }

    /**
    * Get attribute value
    *
    * @param mixed $key
    * @return string
    */
    public function get($key){
        return arrayValue($this->arr, $key, "");
    }

    /**
    * Normalize value
    *
    * @param mixed $value
    * @return string
    */
    public function normalizeValue($value){
        return str_replace(array('"', "\n", "\r", "\t"), array("'", "", "", ""), (string)$value);
    }

    /**
    * Add a attribute - Override old value
    * All double quotes will be replaced with single quotes
    * All new line marks will be removed
    *
    * @param string $key
    * @param mixed $value
    * @return self
    */
    public function add($key, $value){
        $key = (string)$key;
        $this->arr[$key] = $this->normalizeValue($value);
        return $this;
    }

    /**
    * Add a class to the class attribute
    *
    * @return self
    */
    public function addClass($class){
        $class = (string)$class;
        $this->class[$class] = $class;
    }

    /**
    * Remove a attribute if it exist
    *
    * @param string $key
    */
    public function remove($key){
        $key = (string)$key;
        if(isset($this->arr[$key])) unset($this->arr[$key]);
    }

    /**
    * Remove a class from the class attribute
    *
    * @return self
    */
    public function removeClass($class){
        $class = (string)$class;
        if(isset($this->class[$class])) unset($this->class[$class]);
    }

    /**
    * Shortcut for disabled attribute
    *
    * @param bool $flag
    * @return self
    */
    public function setDisabled($flag){
        $this->remove("disabled");
        if($flag) $this->add("disabled", "disabled");
        return $this;
    }

    /**
    * Shortcut for checked attribute
    *
    * @param bool $flag
    * @return self
    */
    public function setChecked($flag){
        $this->remove("checked");
        if($flag) $this->add("checked", "checked");
        return $this;
    }

    /**
    * Shortcut for selected attribute
    *
    * @param bool $flag
    * @return self
    */
    public function setSelected($flag){
        $this->remove("selected");
        if($flag) $this->add("selected", "selected");
        return $this;
    }

    /**
    * Get html attribute string for this attributes
    *
    * @param mixed $ignoreAttributes If set as array than this keys will be ignored
    *   Example: array("key1", "key2")
    * @param mixed $overrideAttributes If set as array than this keys will be overriden for just this output
    *   Example: array("key1" => "value1", "key2" => "value2")
    * @return string
    */
    public function getHtml($ignoreAttributes = NULL, $overrideAttributes = NULL){
        $attr = $this->arr;
        $attr["class"] = implode(" ", $this->class);
        if(!$attr["class"]) unset($attr["class"]);
        if(is_array($ignoreAttributes)) foreach($ignoreAttributes as $key) if(isset($attr[$key])) unset($attr[$key]);
        if(is_array($overrideAttributes)) foreach($overrideAttributes as $key => $value) $attr[(string)$key] = $this->normalizeValue($value);
        ksort($attr);
        foreach($attr as $key => $value) $attr[$key] = $key.'="'.$value.'"';
        return implode(" ", $attr);
    }
}

