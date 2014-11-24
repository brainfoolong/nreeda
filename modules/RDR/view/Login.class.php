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
* The login
*/
class RDR_Login extends CHOQ_View{

    /**
    * On load not implemented
    */
    public function onLoad(){
        if(user()) redirect(get("redirect") ? get("redirect") : l("RDR_Home"), 302);
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        session("name");
        session_unset();
        if(post("login")){
            v("message", t("login.1"));
            $user = RDR_User::login(post("username"), post("password"), post("remember"));
            if($user) {
                if(get("redirect")){
                    redirect(get("redirect"));
                }
                redirect(l("RDR_Home"), 302);
            }
        }
        ?>

        <div class="center">
            <img src="<?php echo url()->getByAlias("public", "img/logo-1.png")?>" alt=""/>
        </div>
        <?php
        $table = new Form_Table($this->getForm());
        $table->addSubmit("Login");
        echo $table->getHtml();
    }

    /**
    * Get form
    *
    * @return Form_Form
    */
    public function getForm(){
        $form = new Form_Form("login");

        $field = new Form_Field_Hidden("login");
        $field->setDefaultValue(1);
        $form->addField($field);

        $field = new Form_Field_Text("username", t("username"));
        $form->addField($field);

        $field = new Form_Field_Password("password", t("password"));
        $form->addField($field);

        $field = new Form_Field_Checkbox("remember", t("login.2"));
        $form->addField($field);

        return $form;
    }
}