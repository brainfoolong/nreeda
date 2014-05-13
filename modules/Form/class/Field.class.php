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
* Field
*/
abstract class Form_Field{

    /**
    * The name of this field
    *
    * @var string
    */
    public $name;

    /**
    * The label of this field
    *
    * @var string
    */
    public $label;

    /**
    * The attributes
    *
    * @var Form_Attributes
    */
    public $attributes;

    /**
    * The default value for this field
    *
    * @var mixed
    */
    public $defaultValue;

    /**
    * Add some content before the field html
    *
    * @var string
    */
    public $htmlBeforeField;

    /**
    * Add some content after the field html
    *
    * @var string
    */
    public $htmlAfterField;

    /**
    * The db member this field belongs to
    *
    * @var CHOQ_DB_TypeMember
    */
    public $member;

    /**
    * The db connection this fields type member belongs to
    *
    * @var CHOQ_DB_DB
    */
    public $db;

    /**
    * Array of validators
    *
    * @var Form_Validator[]
    */
    public $validators = array();

    /**
    * The converter
    *
    * @var Form_Converter
    */
    public $converter;

    /**
    * Get the javascript part for this field
    *
    * @return string
    */
    final public function getJsPart(){
        $validators = array();
        foreach($this->validators as $validator) $validators[] = $validator->toJsonData();
        $output = '<script type="text/javascript">';
        $output .= '(function(){var field = $("#'.$this->attributes->get("id").'"); if(!field.data("formtablefield")) new FormTableField(field, '.json_encode($validators).')})();';
        $output .= '</script>';
        return $output;
    }

    /**
    * Get html string for this field
    *
    * @return string
    */
    abstract function getHtml();

    /**
    * Constructor
    *
    * @param string $name
    * @param string $label
    * @return Form_Field
    */
    public function __construct($name, $label = ""){
        $this->label = $label;
        $this->name = $name;
        $this->attributes = new Form_Attributes(array("name" => $name));
    }

    /**
    * Get submitted value for this field
    * Means $_GET or $_POST
    *
    * @param bool $convert If true than also convert the submitted value by the given converter
    * @return mixed | NULL
    */
    public function getSubmittedValue($convert = false){
        $value = req()->isPost() ? post($this->name) : get($this->name);
        if($value === NULL) return $value;
        if($convert && $this->converter) return $this->converter->convert($value);
        return $value;
    }

    /**
    * Set default value for this field
    *
    * @param mixed $value
    * @return self
    */
    public function setDefaultValue($value){
        $this->defaultValue = $value;
        return $this;
    }

    /**
    * Add a validator
    *
    * @param Form_Validator $validator
    * @return self
    */
    public function addValidator(Form_Validator $validator){
        $this->validators[] = $validator;
        return $this;
    }

    /**
    * Validate the submitted value with all validators
    *
    * @return bool
    */
    public function validate(){
        foreach($this->validators as $validator){
            $ret = $validator->validate($this->getSubmittedValue(true), $this->getSubmittedValue());
            if(!$ret) return false;
        }
        return true;
    }

    /**
    * Assign the type member that this field belongs to and also add the required converter
    *
    * @param mixed $member Could be a string "myclass::mymember" or a CHOQ_DB_TypeMember instance
    * @param CHOQ_DB $db The db connection if required for this member
    * @return self
    */
    public function setTypeMember($member, $db = NULL){
        if(is_string($member)){
            $member = str_replace("::", ":", $member);
            $exp = explode(":", $member);
            $member = CHOQ_DB_Type::get($exp[0])->getMember($exp[1]);
        }
        $this->member = $member;
        $this->db = $db;
        $this->setConverterByMember();
        return $this;
    }

    /**
    * Set converter by fields type member properties
    *
    * @return Form_Converter
    */
    public function setConverterByMember(){
        $this->converter = new Form_Converter();
        $this->converter->setMember($this->member);
        if($this->db) $this->converter->setDb($this->db);
        return $this->converter;
    }

    /**
    * Add required validator by fields type member properties
    *
    * @return Form_Validator_Required | NULL
    */
    public function addRequiredValidatorByMember($errorMessage){
        if(!$this->member->optional){
            $validator = new Form_Validator_Required();
            $validator->setErrorMessage($errorMessage);
            $this->addValidator($validator);
            return $validator;
        }
    }

    /**
    * Add length validator by fields type member properties
    *
    * @return Form_Validator_Length | NULL
    */
    public function addLengthValidatorByMember($errorMessage){
        if($this->member->length){
            $validator = new Form_Validator_Length();
            $validator->setErrorMessage(sprintf(t("form.validator.maxlength"), (string)$this->member->length));
            $validator->setLength(NULL, $this->member->length);
            $this->addValidator($validator);
            return $validator;
        }
    }
}

