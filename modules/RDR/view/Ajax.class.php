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
* The global ajax handler
*/
class RDR_Ajax extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        if(!needRole()) return;

        # some ajax actions
        switch(post("action")){
            case "admin-feed":
                if(needRole(RDR_User::ROLE_ADMIN)){
                    $entry = RDR_Entry::getById(post("eid"));
                    $feed = RDR_Feed::getById(post("fid"));
                    ?>
                    <b><?php echo t("feedadmin.raw.1")?></b>
                    <div class="small">
                        <?php echo t("feedadmin.raw.2")?>
                    </div>
                    <code class="raw"><?php echo s('<div>'.$entry->text.'</div>')?></code><br/><br/>
                    <b><?php echo t("feedadmin.format.1")?></b>
                    <div class="small">
                        <?php echo t("feedadmin.format.2")?>
                    </div>
                    <code class="formated"></code><br/><br/>
                    <b><?php echo sprintf(t("feedadmin.js.1"), s(cut($feed->name, 30)))?></b>
                    <div class="small">
                        <?php echo nl2br(sprintf(t("feedadmin.js.2"), s('<p>'), 'html = $(html); html.find("p").remove()'))?><br/>
                        <textarea data-field="contentJS" style="width:90%" cols="45" rows="3"><?php echo s($feed->contentJS)?></textarea>
                    </div>
                    <?php
                }
                return;
            break;
            case "readed":
                if(post("ids")){
                    $entries = RDR_Entry::getByIds(post("ids"));
                    if($entries) {
                        user()->loadReadedFlags(array_keys($entries));
                        $insertIds = $deleteIds = array();
                        foreach($entries as $entry){
                            $id = $entry->getId();
                            if($id < user()->setting("init.entry")) continue;
                            if(isset(user()->_cacheReaded[$id])){
                                $deleteIds[$id] = $id;
                            }else{
                                $insertIds[$id] = $id;
                            }
                        }
                        if($insertIds){
                            $query = "INSERT IGNORE INTO RDR_User_readed (o,k,v) VALUES ";
                            foreach($insertIds as $id) $query .= " (".user()->getId().", $id, 1), ";
                            $query = substr($query, 0, -2);
                            db()->query($query);
                        }
                        if($deleteIds){
                            $query = "DELETE FROM RDR_User_readed WHERE o = ".user()->getId()." && k IN ".db()->toDb($deleteIds);
                        }
                        user()->updateReadedCount();
                    }
                }
            break;
            case "saved":
                if(post("ids")){
                    $entry = RDR_Entry::getById(post("ids[0]"));
                    if($entry) {
                        if(user()->getByKey("saved", $entry->getId())){
                            user()->remove("saved", $entry->getId());
                        }else{
                            user()->add("saved", 1, $entry->getId());
                        }
                        user()->store();
                    }
                }
            break;
        }
        $jsonData = user()->getAjaxData();
        echo json_encode($jsonData, JSON_FORCE_OBJECT);
    }
}