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
* Database Update
*/
class RDR_DBUpdate extends CHOQ_View{

    /**
    * Is an update required?
    *
    * @return bool
    */
    static function updateRequired(){
        $setting = RDR_Setting::get("dbversion");
        return $setting->value != RDR_VERSION;
    }

    /**
    * Do the database update
    */
    static function run(){
        $generator = CHOQ_DB_Generator::create(db());
        $generator->addModule("RDR");
        $generator->updateDatabase();

        # setting current version
        $setting = RDR_Setting::get("dbversion");
        $setting->value = RDR_VERSION;
        $setting->store();
    }

    /**
    * On Load
    */
    public function onLoad(){

    }

    /**
    * Get content
    */
    public function getContent(){
        if(get("updatedatabase")){
            RDR_DBUpdate::run();
            redirect(l("RDR_Home"), 302);
        }
        headline(t("dbupdate.1"));
        ?>
        <div class="indent">
            <?php echo s(t("dbupdate.2"), true)?><br/><br/>
            <input type="button" class="btn" value="<?php echo t("dbupdate.3")?>" onclick="window.location.href = '<?php echo url()->getModifiedUri(false)?>?updatedatabase=1'"/>
        </div>
        <?php 
    }
}