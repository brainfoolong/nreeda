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
* A html table for a form
*/
class Form_Table{

    /**
    * The table id
    *
    * @var mixed
    */
    public $id;

    /**
    * The form
    *
    * @var Form_Form
    */
    public $form;

    /**
    * Add buttons to the end of the output
    *
    * @var Form_Field[]
    */
    public $buttons = array();

    /**
    * Constructor
    *
    * @param Form_Form $form
    * @return self
    */
    public function __construct(Form_Form $form){
        $this->id = "formtable-".md5(uniqid(false, true));
        $this->form = $form;
    }

    /**
    * Add a submit button to the end of the output
    *
    * @param string $value
    * @param string $name
    * @param array $attr
    * @return Form_Field_Button
    */
    public function addSubmit($value, $name = "", $attr = array()){
        $field = new Form_Field_Submit($name);
        $field->setDefaultValue($value);
        foreach($attr as $key => $value) $field->attributes->add($key, $value);
        $this->buttons[] = $field;
        return $field;
    }

    /**
    * Add a button to the end of the output
    *
    * @param string $value
    * @param array $attr
    * @return Form_Field_Button
    */
    public function addButton($value, $attr = array()){
        $field = new Form_Field_Button("");
        $field->setDefaultValue($value);
        foreach($attr as $key => $value) $field->attributes->add($key, $value);
        $this->buttons[] = $field;
        return $field;
    }

    /**
    * Get html for this table
    *
    * @param mixed $fieldsPerRow How many fields will be displayed per row
    * @param mixed $displayOrder Order "vertical" or "horizontal"
    * @return string
    */
    public function getHtml($fieldsPerRow = 1, $displayOrder = "vertical"){
        $fields = $this->form->fields;
        $fieldsHidden = array();
        $fieldsBlock = array();
        foreach($fields as $key => $field){
            if($field instanceof Form_Field_Hidden){
                $fieldsHidden[] = $field;
                unset($fields[$key]);
            }
        }
        $fieldsPerBlock = ceil(count($fields) / $fieldsPerRow);
        if($displayOrder == "horizontal"){
            for($i = 1 ; $i <= $fieldsPerBlock; $i++){
                for($b = 1; $b <= $fieldsPerRow; $b++){
                    $field = array_shift($fields);
                    if($field === NULL) break 2;
                    $fieldsBlock[$b][$field->name] = $field;
                }
            }
        }elseif($displayOrder == "vertical"){
            for($b = 1; $b <= $fieldsPerRow; $b++){
                for($i = 1 ; $i <= $fieldsPerBlock; $i++){
                    $field = array_shift($fields);
                    if($field === NULL) break 2;
                    $fieldsBlock[$b][$field->name] = $field;
                }
            }
        }

        $output = '<div id="'.$this->id.'" class="formtable">';
        $output .= '<form '.$this->form->attributes->getHtml().'>';
        foreach($fieldsHidden as $field){
            $output .= $field->getHtml();
        }
        if($fieldsBlock){
            $blockWidth = round(100 / $fieldsPerRow, 2, PHP_ROUND_HALF_DOWN);
            foreach($fieldsBlock as $block => $fields){
                $output .= '<div class="formtable-block" style="width:'.$blockWidth.'%">';
                if($fields){
                    $output .= '<table class="formtable-table">';
                    foreach($fields as $field){
                        $output .= '<tr class="formtable-fieldtype-'.strtolower(slugify(get_class($field))).'">';
                        $output .= '<td class="formtable-label"><label for="'.$field->attributes->get("id").'">'.$field->label.'</label></td>';
                        $output .= '<td class="formtable-field">'.$field->getHtml().'</td>';
                        $output .= '</tr>';
                    }
                    $output .= '</table>';
                }
                $output .= '</div>';
            }
        }
        if($this->buttons){
            $output .= '<div class="formtable-buttons">';
            foreach($this->buttons as $field){
                $output .= $field->getHtml();
            }
            $output .= '</div>';
        }
        $output .= '</form></div>';
        $output .= '<script type="text/javascript">(function(){var container = $("#'.$this->id.'"); if(!container.data("formtable")) new FormTable(container)})();</script>';
        return $output;
    }
}

