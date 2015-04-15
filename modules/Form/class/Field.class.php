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
    public $attr;

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
    * The database object assigned to this field
    *
    * @var CHOQ_DB_Object
    */
    public $dbObject;

    /**
    * The db member this field belongs to
    *
    * @var CHOQ_DB_TypeMember
    */
    public $dbTypeMember;

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
        $output .= '(function(){var field = $("#'.$this->attr->get("id").'"); if(!field.data("formtablefield")) new FormTableField(field, '.json_encode($validators).')})();';
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
        $this->attr = new Form_Attributes(array("name" => $name, "data-field-type" => strtolower(slugify(get_class($this)))));
    }

    /**
    * Get submitted value for this field
    * Means $_GET or $_POST
    *
    * @param bool $convert If true than also convert the submitted value by the given converter
    * @return mixed | null
    */
    public function getSubmittedValue($convert = false){
        $value = req()->isPost() ? post($this->name) : get($this->name);
        if($value === null) return $value;
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
    * Quick alias for addValidator(Form_Validator_Required)
    *
    * @return self
    */
    public function addValidatorRequired(){
        return $this->addValidator(new Form_Validator_Required());
    }

    /**
    * Quick alias for addValidator(Form_Validator_Length)
    *
    * @param mixed $min
    * @param mixed $max
    * @return self
    */
    public function addValidatorLength($min = null, $max = null){
        $validator = new Form_Validator_Length();
        $validator->setLength($min, $max);
        return $this->addValidator($validator);
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
    * Set a database object member to this field
    * This will set automatically all required validators and converters depending on type properties
    * Also automatically set the default value
    *
    * @param CHOQ_DB_Object $object
    * @param mixed $memberName If this is null than the name of the field will be used
    * @return self
    */
    public function setDbObjectMember(CHOQ_DB_Object $object, $memberName = null){
        if(!$memberName) $memberName = $this->name;
        $this->dbObject = $object;
        $this->dbTypeMember = CHOQ_DB_Type::get($this->dbObject)->getMember($memberName);
        # set converter
        $this->converter = new Form_Converter($this);
        # set required validator
        if(!$this->dbTypeMember->optional){
            $validator = new Form_Validator_Required();
            $this->addValidator($validator);
        }
        # set length validator
        if($this->dbTypeMember->length){
            $validator = new Form_Validator_Length();
            $validator->setErrorMessage(sprintf(t("form.validation.length"), (string)$this->dbTypeMember->length));
            $validator->setLength(null, $this->dbTypeMember->length);
            $this->addValidator($validator);
        }
        # set default value
        $this->setDefaultValue($object->{$memberName});
        return $this;
    }
}

