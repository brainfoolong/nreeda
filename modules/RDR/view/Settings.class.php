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
* Settings
*/
class RDR_Settings extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        if(post("savepw")){
            if(post("newpw") && post("newpw") == post("newpw2")){
                user()->setPassword(post("newpw"));
                user()->store();
                v("message", t("saved"));
            }
        }
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        headline(t("settings.1"));

        $table = new Form_Table($this->getForm());
        $table->addSubmit(t("saveit"));
        echo $table->getHtml();
    }

    /**
    * Get form
    *
    * @return Form_Form
    */
    public function getForm(){
        $form = new Form_Form("pwchange");

        $field = new Form_Field_Hidden("save");
        $field->setDefaultValue(1);
        $form->addField($field);

        $field = new Form_Field_Password("newpw", t("settings.2"));
        $form->addField($field);

        $field = new Form_Field_Password("newpw2", t("settings.3"));
        $form->addField($field);

        return $form;
    }
}