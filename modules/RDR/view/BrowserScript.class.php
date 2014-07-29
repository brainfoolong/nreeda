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
* The Browser Script
*/
class RDR_BrowserScript extends CHOQ_View{

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
        if(get("feed") && is_array(get("feed"))){
            if(post("save")){
                $count = 0;
                $feeds = post("feed");
                $message = array();
                if(is_array($feeds)){
                    foreach($feeds as $key => $feed){
                        $href = post("href[{$key}]");
                        $title = post("title[{$key}]");
                        $cat = post("cat[{$key}]");
                        $category = RDR_Category::get($cat);
                        $event = RDR_Import::addFeed($href, $category);
                        if($event->feed) {
                            RDR_Import::importFeedEntries($event->feed);
                            if($href != $title) {
                                $event->feed->setCustomName($category, $title);
                                $category->store();
                            }
                        }
                        $message[] = $event->getText();
                    }
                }
                headline(t("browserscript.log"));
                ?>
                <p><?php echo implode("<br/>", $message)?></p>
                <p><?php echo t("browserscript.close")?></p>
                <?php
                return;
            }

            headline(sprintf(t("browserscript.addfeeds"), s(get("site"))));
            $feeds = get("feed");
            ?>
            <form name="d" method="post" action="">
            <p>
            <?php echo t("browserscript.close")?><br/><br/>
            <?php
            $categories = user()->getCategories();
            foreach($feeds as $id => $feed){
                $exp = explode(";", $feed, 2);
                ?>
                <input type="hidden" name="href[<?php echo s($id)?>]" value="<?php echo $exp[0]?>"/>
                <input type="hidden" name="title[<?php echo s($id)?>]" value="<?php echo $exp[1]?>"/>
                <input type="checkbox" name="feed[<?php echo s($id)?>]" value="<?php echo $feed?>"/>
                <select name="cat[<?php echo s($id)?>]">
                    <option value="<?php echo t("uncategorized")?>"><?php echo t("uncategorized")?></option>
                    <?php foreach($categories as $category){
                        if($category->name == t("uncategorized")) continue;
                        ?>
                        <option value="<?php echo $category->name?>"><?php echo s($category->name)?></option>
                    <?php }?>
                </select>
                <a href="<?php echo urldecode($exp[0])?>" target="_blank"><?php echo urldecode($exp[1])?></a><br/>
                <?php
            }
            ?><br/>
            <input type="submit" name="save" class="btn" value="<?php echo t("browserscript.addfeeds.btn")?>"/>
            </p>
            </form>
            <?php
            return;
        }

        $script = file_get_contents(CHOQ_ACTIVE_MODULE_DIRECTORY."/view/_js/browser-script.js");
        $script = str_replace(array("\t", "\r", "\n"), "", $script);
        $script = preg_replace("~\s{2,99}~", "", $script);
        $script = str_replace("{url}", url()->getByAlias("root", l($this)), $script);
        $script = str_replace("{nofeed}", t("browserscript.nofeed"), $script);
        $script = str_replace("{forward}", t("browserscript.forward"), $script);
        $script = "javascript:".$script;

        headline(t("sidebar.25"));
        ?>
        <p><?php echo t("browserscript.info")?></p>
        <?php
        headline(t("browserscript.use"));
        ?>
        <p><?php echo t("browserscript.bookmark")?><br/><br/>

        <a href='<?php echo $script?>'><?php echo s($script)?></a>
        </p>
        <?php
    }
}