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
* The user admin
*/
class RDR_Admin_User extends CHOQ_View{

    /**
    * The user to edit
    *
    * @var RDR_User
    */
    private $user;

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

        $this->user = new RDR_User(db());
        if(get("id")){
            $tmp = db()->getById("RDR_User", get("id"));
            if($tmp) $this->user = $tmp;
        }

        $this->form = $this->getForm();

        if(post("save")){
            if(!$this->form->validateAllFields()){
                v("message", t("form.validation.error"));
            }elseif(post("password") && post("password") != post("password2")){
                v("message", t("admin.user.2"));
            }else{
                $this->form->setObjectMembersBySubmittedValues($this->user);
                if(post("password")) $this->user->setPassword(post("password"));
                $this->user->store();
                v("message", t("saved"));
                $this->form = $this->getForm();
            }
        }

        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        headline(t("admin.user.1"));
        $users = db()->getByCondition("RDR_User", NULL, NULL, "+username");
        foreach($users as $user){
            ?>
            <a href="<?php echo url()->getModifiedUri(array("id" => $user->getId()))?>"><?php echo s($user->username)?> (<?php echo t("user.".$user->role)?>)</a><br/>
            <?php
        }
        ?>
        <div class="spacer"></div>
        <?php
        headline($this->user->getId() ? t("admin.user.3") : t("admin.user.4"));

        $table = new Form_Table($this->form);
        $table->addSubmit(t("saveit"));
        if($this->user->getId()){
            $table->addButton(t("admin.user.4"), array("onclick" => "window.location.href = '". url()->getModifiedUri(false)."'"));
        }
        echo $table->getHtml();
    }

    /**
    * Get form
    *
    * @return Form_Form
    */
    public function getForm(){
        $form = new Form_Form("user");

        $field = new Form_Field_Hidden("save");
        $field->setDefaultValue(1);
        $form->addField($field);

        $field = new Form_Field_Text("username", t("username"));
        $field->setDefaultValue($this->user->username);
        $field->setTypeMember("RDR_User::username");
        $field->addLengthValidatorByMember(t("form.validator.maxlength"));
        $field->addRequiredValidatorByMember(t("form.validator.required"));
        $form->addField($field);

        $field = new Form_Field_Password("password", t("password"));
        if(!$this->user->getId()){
            $field->setTypeMember("RDR_User::password");
            $field->addRequiredValidatorByMember(t("form.validator.required"));
        }
        $form->addField($field);

        $field = new Form_Field_Password("password2", t("settings.3"));
        if(!$this->user->getId()){
            $field->setTypeMember("RDR_User::password");
            $field->addRequiredValidatorByMember(t("form.validator.required"));
        }
        $form->addField($field);

        $field = new Form_Field_Select("role", t("admin.user.5"));
        $field->setTypeMember("RDR_User::role");
        $field->addOption(RDR_User::ROLE_USER, t("user.".RDR_User::ROLE_USER));
        $field->addOption(RDR_User::ROLE_ADMIN, t("user.".RDR_User::ROLE_ADMIN));
        $field->setDefaultValue($this->user->role);
        $form->addField($field);

        return $form;
    }
}