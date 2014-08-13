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
* Organize
*/
class RDR_Organize extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){

        # OPML
        if(get("opml")){
            $categories = user()->getCategories();
            $opml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><opml></opml>');
            $opml->addAttribute("version", "1.0");
            $head = $opml->addChild("head");
            $head->addChild("title", "Subscriptions from nReeda");
            $body = $opml->addChild("body");
            foreach($categories as $category){
                $cat = $body->addChild("outline");
                $cat->addAttribute("title", $category->name);
                $cat->addAttribute("text", $category->name);
                $feeds = $category->feeds;
                if($feeds){
                    foreach($feeds as $feed){
                        $f = $cat->addChild("outline");
                        $f->addAttribute("type", "rss");
                        $f->addAttribute("text", $feed->getCustomName($category));
                        $f->addAttribute("title", $feed->getCustomName($category));
                        $f->addAttribute("xmlUrl", $feed->url);
                    }
                }
            }
            $data = $opml->asXML();
            CHOQ_OutputManager::cleanAllBuffers();
            header("Content-type: application/octet-stream");
            header("Content-Disposition: filename=\"nreeda.opml\"");
            echo $data;
            die();
        }

        # textfile
        if(get("file")){
            $categories = user()->getCategories();
            $lines = array();
            foreach($categories as $category){
                $feeds = $category->feeds;
                if($feeds){
                    foreach($feeds as $feed){
                        $lines[] = $feed->url;
                    }
                }
            }
            $data = implode("\n", $lines);
            CHOQ_OutputManager::cleanAllBuffers();
            header("Content-type: application/octet-stream");
            header("Content-Disposition: filename=\"nreeda.txt\"");
            echo $data;
            die();
        }

        # Import
        if(isset($_FILES["file"]["tmp_name"])){
            $data = file_get_contents($_FILES["file"]["tmp_name"]);
            if(strpos($data, "<?xml") === false || strpos($data, "</opml>") === false){
                $event = RDR_Import::importFromFile($_FILES["file"]["tmp_name"]);
                if($event->type == RDR_Event::TYPE_FILE_OK) RDR_Import::updateAllFeeds();
                v("message", $event->getText());
            }else{
                $event = RDR_Import::importFromOPML($_FILES["file"]["tmp_name"]);
                if($event->type == RDR_Event::TYPE_OPML_OK) RDR_Import::updateAllFeeds();
                v("message", $event->getText());
            }

        }

        if(post("new") && trim(post("val"))){
            RDR_Category::get(post("val"));
            redirect(url()->getUri(), 302);
        }

        if(req()->isAjax()){
            $categories = user()->getCategories();
            $feeds = user()->getFeeds();
            if(post("action") == "edit" && post("val")){
                if(isset($categories[post("category")])){
                    $category = $categories[post("category")];
                    if(post("feed")){
                        $feed = arrayValue($feeds, post("feed"));
                        if($feed){
                            $feed->setCustomName($category, post("val"));
                            $category->store();
                        }
                    }else{
                        $category->name = post("val");
                        $category->store();
                    }
                }
            }
            if(post("action") == "move"){
                if(isset($categories[post("categoryOld")])){
                    $categoryOld = $categories[post("categoryOld")];
                    $categoryNew = $categories[post("categoryNew")];
                    if(post("feed")){
                        $feed = arrayValue($feeds, post("feed"));
                        if($feed){
                            $name = $feed->getCustomName($categoryOld);
                            $categoryOld->remove("feedsData", $feed->getId()."-name");
                            $categoryOld->remove("feeds", $feed->getId());
                            $categoryOld->store();
                            $feed->setCustomName($categoryNew, $name);
                            $categoryNew->add("feeds", $feed);
                            $categoryNew->store();
                        }
                    }
                }
            }
            if(post("action") == "delete"){
                if(isset($categories[post("category")])){
                    $category = $categories[post("category")];
                    if(post("feed")){
                        $feed = arrayValue($feeds, post("feed"));
                        if($feed){
                            $category->remove("feedsData", $feed->getId()."-name");
                            $category->remove("feeds", $feed);
                            $category->store();
                        }
                    }else{
                        $category->delete();
                    }
                }
            }
            RDR_Cleanup::cleanupFeeds();
            RDR_Cleanup::cleanupFlags();
            user()->updateNewsCache();
            return;
        }

        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        headline(t("organize.3"));
        $categories = user()->getCategories();
        $i = 1;
        foreach($categories as $category){
            $feeds = $category->feeds;
            ?>
            <div class="category">
                <div class="inner">
                    <div class="title" data-category="<?php echo $category->getId()?>" data-feed="0">
                        <span class="delete">X</span> <input class="update" type="text" value="<?php echo s($category->name)?>" style="width:95%; font-weight: bold;"/>
                    </div>
                    <div class="message"></div>
                    <?php if($feeds){
                        foreach($feeds as $feed){
                            ?>
                            <div class="feed" data-category="<?php echo $category->getId()?>" data-feed="<?php echo $feed->getId()?>">
                                <span class="delete">X</span> <span class="move">M</span> <input class="update" type="text" value="<?php echo s($feed->getCustomName($category))?>" style="width:95%"/>
                            </div>
                            <div class="message"></div>
                            <?php
                        }
                    }?>
                </div>
            </div>
            <?php
            if(is_int($i++/2)){
                echo '<div class="clear"></div>';
                $i = 1;
            }
        }?>

        <div class="category new">
            <div class="inner">
                <div class="title">
                    <form name="newc" method="post" action="">
                    <input type="text" name="val" value="<?php echo t("organize.1")?>" style="width:95%; font-weight: bold;" onfocus="this.value = ''"/><br/>
                    <input type="submit" name="new" value="<?php echo t("organize.2")?>" class="btn"/>
                    </form>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        <div class="spacer"></div>

        <?php
        headline(t("organize.16"));
        ?>
        <div class="indent">
            <?php
            $feeds = user()->getFeeds();
            foreach($feeds as $feed){
                ?>
                <div class="feed">
                    <div class="inline-btn delete-feed" data-id="<?php echo $feed->getId()?>"><?php echo t("organize.15")?></div>
                    <a href="<?php echo $feed->getLink()?>"><?php echo s($feed->name)?></a><br/>
                    <div class="small" style="margin-top:3px;"><a href="<?php echo s($feed->url)?>"><?php echo s($feed->url)?></a></div>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="spacer"></div>

        <?php
        headline(t("organize.4"));
        ?>
        <div class="indent">
            <?php echo s(t("organize.5"), true)?><br/><br/>
            <form name="upl" method="post" enctype="multipart/form-data" onsubmit="Global.message('<?php echo t("organize.6")?>', true)">
                <input type="file" name="file"/> <input type="submit" value="<?php echo t("upload")?>" class="btn"/>
            </form>
        </div>
        <div class="spacer"></div>

        <?php
        headline(t("organize.7"));
        ?>
        <div class="indent">
            <?php echo t("organize.8")?>.<br/><br/>
            <input type="button" class="btn" onclick="window.location.href = '<?php echo url()->getModifiedUri(false)?>?opml=1'" value="<?php echo t("organize.9")?>"/>
            <input type="button" class="btn" onclick="window.location.href = '<?php echo url()->getModifiedUri(false)?>?file=1'" value="<?php echo t("organize.10")?>"/>
        </div>
        <div class="spacer"></div>

        <script type="text/javascript">
        function del(feed, category){
            $.post(window.location.href, {"action" : "delete", "feed" : feed, "category" : category}, function(){
                window.location.href = window.location.href;
            });
        }
        function move(feed, categoryOld, categoryNew){
            $.post(window.location.href, {"action" : "move", "feed" : feed, "categoryOld" : categoryOld, "categoryNew"  : categoryNew}, function(){
                window.location.href = window.location.href;
            });
        }
        (function(){
            $("#content div.delete-feed.inline-btn").on("click", function(){
                if(confirm(<?php echo json_encode(t("organize.13"))?>)){
                    API.req("delete-feed-user", {"fid" : $(this).attr("data-id")});
                    $(this).closest(".feed").remove();
                }
            });
            $("#content span.move").on("click", function(){
                var p = $(this).parent();
                var feed = parseInt(p.attr("data-feed"));
                var category = parseInt(p.attr("data-category"));
                var message = $("<div><?php echo t("organize.11")?><br/><br/><select></select></div>");
                var sel = message.find("select");
                $(".category").not(".new").each(function(){
                    sel.append('<option value="'+$(this).find(".title").attr("data-category")+'">'+$(this).find(".title input").val()+'</option>')
                });
                sel.val($(this).closest(".category").find(".title").attr("data-category"));
                p.next().html(message).append('<input type="button" class="btn" onclick="move('+feed+', '+category+', $(this).parent().find(\'select\').val());" value="<?php echo t("organize.12")?>"/></div>');
                p.next().show();
            });
            $("#content span.delete").on("click", function(){
                var p = $(this).parent();
                var feed = parseInt(p.attr("data-feed"));
                var category = parseInt(p.attr("data-category"));
                var message = "<?php echo t("organize.13")?>";
                if(!feed) message = "<?php echo t("organize.14")?>";
                p.next().html($('<div>'+message+'</div><br/><input type="button" class="btn" onclick="del('+feed+', '+category+');" value="<?php echo t("organize.15")?>"/></div>')).show();
            });
            $("#content .category input.update").on("blur", function(){
                var feed = $(this).parent().attr("data-feed");
                var category = $(this).parent().attr("data-category");
                $.post(window.location.href, {"action" : "edit", "feed" : feed, "category" : category, "val" : this.value});
            });
        })();
        </script>
        <?php
    }
}