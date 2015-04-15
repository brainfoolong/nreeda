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
* The update
*/
class RDR_Admin_Settings extends CHOQ_View{

    /**
    * The form
    *
    * @var Form_Form
    */
    private $form;

    /**
    * Load the View
    */
    public function onLoad(){
        needRole(RDR_User::ROLE_ADMIN, true);

        $this->form = $this->getForm();
        if(post("save")){
            foreach($this->form->fields as $field){
                if($field->attr->get("data-setting")){
                    RDR_Setting::set($field->name, $field->getSubmittedValue());
                }
            }
            v("message", t("saved"));
            $this->form = $this->getForm();
        }
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        headline(t("admin.settings.1"));

        $table = new Form_Table($this->form);
        $table->addSubmit(t("saveit"));
        echo $table->getHtml();
    }

    /**
    * Get form
    *
    * @return Form_Form
    */
    public function getForm(){
        $form = new Form_Form("settings");

        $field = new Form_Field_Hidden("save");
        $field->setDefaultValue(1);
        $form->addField($field);

        $field = new Form_Field_Select("maxentrylifetime", t("admin.settings.2"));
        $field->attr->add("data-setting", 1);
        $field->addOption("", t("admin.settings.4"));
        $field->addOption("-1 month", "1 ".t("months"));
        $field->addOption("-2 month", "2 ".t("months"));
        $field->addOption("-3 month", "3 ".t("months"));
        $field->addOption("-6 month", "6 ".t("months"));
        $field->addOption("-1 year", "1 ".t("years"));
        $field->setDefaultValue(RDR_Setting::get($field->name)->value);
        $form->addField($field);

        $field = new Form_Field_Select("maxeventlifetime", t("admin.settings.3"));
        $field->attr->add("data-setting", 1);
        $field->addOption("", "1 ".t("days"));
        $field->addOption("-2 day", "2 ".t("days"));
        $field->addOption("-3 days", "3 ".t("days"));
        $field->addOption("-1 week", "1 ".t("weeks"));
        $field->setDefaultValue(RDR_Setting::get($field->name)->value);
        $form->addField($field);

        return $form;
    }
}