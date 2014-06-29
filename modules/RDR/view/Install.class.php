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
* Install
*/
class RDR_Install extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        $errors = $checkErrors = array();
        $writeableFolders = array(
            CHOQ_ACTIVE_MODULE_DIRECTORY,
            CHOQ_ACTIVE_MODULE_DIRECTORY."/public/static",
            CHOQ_ACTIVE_MODULE_DIRECTORY."/public/img/favicons",
            CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp"
        );
        foreach($writeableFolders as $folder){
            if(!is_dir($folder)){
                $errors[] = sprintf(t("install.folder.notexist"), $folder);
                break;
            }
            if(!is_writable($folder)){
                $errors[] = sprintf(t("install.folder.writeable"), $folder);
                break;
            }
        }
        if(!ini_get("allow_url_fopen")) $errors[] = sprintf(t("install.php.feature"), "allow_url_fopen");
        if(!function_exists("fsockopen")) $errors[] = sprintf(t("install.php.feature"), "fsockopen/sockets");
        if(!function_exists("simplexml_load_file")) $errors[] = sprintf(t("install.php.feature"), "SimpleXML");
        if(!CHOQ_DB_Mysql::isAvailable()) $errors[] = sprintf(t("install.php.feature"), "MySQLi");
        if(post("install")){
            try{
                $connString = "mysql://".post("mysql-user").":".post("mysql-pass")."@".post("mysql-host")."/".post("mysql-db");
                CHOQ_DB::add("test", $connString);
                db("test")->query("DROP TABLE IF EXISTS ".db("test")->quote("RDR_test"));
                db("test")->query("CREATE TABLE ".db("test")->quote("RDR_test")." (".db("test")->quote("id")." INT NOT NULL)");
                db("test")->query("DROP TABLE IF EXISTS ".db("test")->quote("RDR_test"));

                $fileData = "<?php\nif(!defined(\"CHOQ\")) die();\n/**\n * Local Configuration\n**/\n\n";
                $fileData .= 'CHOQ_DB::add("default", \''.$connString.'\');'."\n";
                $fileData .= 'v("hash.salt", "'.uniqid(NULL, true).sha1(microtime().uniqid(NULL, true)).'");'."\n";
                $tmpfile = CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp/_RDR.local.tmp.php";
                file_put_contents($tmpfile, $fileData);
                include($tmpfile);

                $generator = CHOQ_DB_Generator::create(db());

                $tables = $generator->getExistingTables();
                foreach($tables as $table) {
                    if($table == "_choqled_metadata" || substr(strtolower($table), 0, 3) == "RDR_"){
                        db()->query("DROP TABLE ".db()->quote($table));
                    }
                }

                $generator->addModule("RDR");
                $generator->updateDatabase();

                $user = new RDR_User(db());
                $user->username = post("admin-user");
                $user->setPassword(post("admin-pass"));
                $user->role = RDR_User::ROLE_ADMIN;
                $user->store();
                rename($tmpfile, CHOQ_ACTIVE_MODULE_DIRECTORY."/_RDR.local.php");
                redirect(url()->getByAlias("base"));
            }catch(Exception $e){
                $checkErrors[] = $e->getMessage();
            }
        }
        ?>
        <div class="center">
            <img src="<?php echo url()->getByAlias("public", "img/logo-1.png")?>" alt=""/>
        </div>
        <?php 
        if(!$errors){
            $this->showErrors($checkErrors);

            $form = $this->getForm();
            $formTable = new Form_Table($form);
            $formTable->addSubmit(t("install.finish"));
            echo $formTable->getHtml();
            echo "<div class='center'><br/>".t("install.warn")."</div>";
        }else{
            $this->showErrors($errors);
        }
    }

    /**
    * Show errors
    *
    * @param mixed $errors
    */
    private function showErrors($errors){
        foreach($errors as $error){
            echo '<div style="color:red">'.s($error).'</div>';
        }
    }

    /**
    * Get form
    *
    * @return Form_Form
    */
    private function getForm(){
        $form = new Form_Form("install");

        $validator = new Form_Validator_Required();
        $validator->setErrorMessage(t("form.validator.required"));

        $field = new Form_Field_Hidden("install", "");
        $field->setDefaultValue(1);
        $form->addField($field);

        $field = new Form_Field_Text("mysql-host", t("install.host"));
        $field->addValidator($validator);
        $field->setDefaultValue("localhost");
        $form->addField($field);

        $field = new Form_Field_Text("mysql-db", t("install.db"));
        $field->addValidator($validator);
        $field->setDefaultValue("opass");
        $form->addField($field);

        $field = new Form_Field_Text("mysql-user", t("install.user"));
        $field->addValidator($validator);
        $field->setDefaultValue("opass");
        $form->addField($field);

        $field = new Form_Field_Password("mysql-pass", t("install.pw"));
        $form->addField($field);

        $field = new Form_Field_Text("admin-user", t("install.admin.user"));
        $field->addValidator($validator);
        $form->addField($field);

        $field = new Form_Field_Password("admin-pass", t("install.admin.pw"));
        $field->addValidator($validator);
        $form->addField($field);

        return $form;
    }
}