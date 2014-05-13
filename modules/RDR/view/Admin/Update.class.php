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
class RDR_Admin_Update extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        needRole(RDR_User::ROLE_ADMIN, true);
        if(req()->isAjax()){
            if(post("update")){
                $feed = db()->getById("RDR_Feed", post("update"));
                if($feed){
                    RDR_Import::importFeedEntries($feed);
                }
            }
            if(post("updateDb")){
                $generator = CHOQ_DB_Generator::create(db());
                $generator->addModule("RDR");
                $generator->updateDatabase();
            }
            return;
        }
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        headline(t("admin.update.1"));
        ?>
        <div class="indent">
            <?php echo nl2br(sprintf(t("admin.update.2"), '<a href="https://www.setcronjob.com" target="_blank">setcronjob.com</a>', '<a href="'.RDR_Cron::getLink().'" target="_blank"><b>'.RDR_Cron::getLink().'</b></a>'))?><br/>
            <code style="font-size: 11px;">*/10 * * * * wget -O- "<?php echo RDR_Cron::getLink()?>"</code>
        </div>
        <div class="spacer"></div>

        <?php
        headline(t("admin.update.3"));
        ?>
        <div class="indent">
            <?php echo t("admin.update.4")?>.<br/><br/>
            <div class="btn update-all"><?php echo t("admin.update.5")?></div>
            <div class="spacer"></div>
            <?php
            $feeds = db()->getByCondition("RDR_Feed", NULL, NULL, "+name");
            foreach($feeds as $feed){?>
                <div style="padding-left: 20px; margin-bottom: 2px;" class="feed">
                    <div class="inline-btn update-feed" data-id="<?php echo $feed->getId()?>"><?php echo t("admin.update.6")?></div>
                    <div class="inline-btn delete-feed" data-id="<?php echo $feed->getId()?>"><?php echo t("organize.15")?></div>
                    <img src="<?php echo url()->getByAlias("public", "img/loading-2.gif")?>" alt="" style="position: relative; top:2px; display: none;" class="loading-feed"/>
                    <a href="<?php echo $feed->getLink()?>"><?php echo s($feed->name)?></a><br/>
                    <div class="small" style="margin-top:3px;"><a href="<?php echo s($feed->url)?>"><?php echo s($feed->url)?></a></div>
                </div>
            <?php } ?>
        </div>

        <?php
        headline(t("admin.update.7"));
        ?>
        <div class="indent">
            <?php echo s(t("admin.update.8"), true)?>
            <div class="spacer"></div>
            <input type="button" class="btn update-db" value="<?php echo t("admin.update.9")?>"/>

        </div>

        <script type="text/javascript">
        (function(){
            $("input.update-db").on("click", function(){
                Global.message("Updating...");
                $.post('<?php echo url()->getUri()?>', {updateDb : 1}, function(){
                    Global.message("<?php echo t("admin.update.10")?>");
                })
            });
            $("#content div.update-feed.inline-btn").on("click", function(){
                $("img.loading-feed").hide();
                pipeline = [$(this).attr("data-id")];
                runUpdate();
            });
            $("#content div.delete-feed.inline-btn").on("click", function(){
                if(confirm(<?php echo json_encode(t("organize.13"))?>)){
                    API.req("delete-feed-admin", {"fid" : $(this).attr("data-id")});
                    $(this).closest(".feed").remove();
                }
            });
            $("#content div.update-all").on("click", function(){
                $("#content img.loading-feed").hide();
                pipeline = [];
                $("#content div.update-feed.inline-btn").each(function(){
                    pipeline.push($(this).attr("data-id"));
                });
                runUpdate();
            });

            var pipeline = [];
            function runUpdate(){
                if(!pipeline.length) {
                    Global.updateNewsCache();
                    return;
                }
                var id = pipeline.shift();
                var btn = $("#content div.update-feed[data-id='"+id+"']").parent().find(".loading-feed");
                btn.show();
                $.post('<?php echo url()->getUri()?>', {"update" : id}, function(){
                    btn.hide();
                    runUpdate();
                });
            }
        })();
        </script>
        <?php


    }
}