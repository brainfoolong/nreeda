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
* The feed listing
*/
class RDR_Feeds extends CHOQ_View{

    /**
    * The feed the user is watching
    *
    * @var RDR_Feed
    */
    private $feed;

    /**
    * The category the user is watching
    *
    * @var RDR_Category
    */
    private $category;

    /**
    * Readed entries are lightgray if this is enabled - Deactivate it on some special pages
    *
    * @var bool
    */
    private $readedLayoutEnabled = true;

    /**
    * Load the View
    */
    public function onLoad(){
        needRole(NULL, true);

        $p = $this->getParam("param");
        $explodeParam = explode("-", $p);
        $pType = arrayValue($explodeParam, 0);
        $pVal = arrayValue($explodeParam, 1);
        $title = t("sidebar.10");
        $categories = user()->getCategories();
        $feeds = user()->getFeeds();
        switch($pType){
            case "cat":
                if(isset($categories[$pVal])){
                    $this->category = $categories[$pVal];
                    $title = $this->category->name;
                    $feeds = $this->category->feeds;
                    if(isset($exp[1]) && isset($feeds[$exp[1]])){
                        $this->feed = $feeds[$exp[1]];
                        $title = $this->feed->name;
                    }
                }
            break;
            case "feed":
                if(isset($feeds[$pVal])){
                    $this->feed = $feeds[$pVal];
                    $this->category = user()->getCategoryToFeed($this->feed);
                    $title = $this->feed->getCustomName($this->category);
                }elseif(needRole(RDR_User::ROLE_ADMIN)){
                    $this->feed = RDR_Feed::getById($pVal);
                    if($this->feed){
                        $this->feed = $feeds[$pVal];
                        $title = $this->feed->name;
                    }
                }
            break;
            case "search":
                $this->readedLayoutEnabled = false;
                $title = sprintf(t("feeds.1"), s(get("q")));
            break;
            case "archive":
                $this->readedLayoutEnabled = false;
                $title = t("archive.title");
            break;
            case "saved":
                $this->readedLayoutEnabled = false;
                $title = t("sidebar.11");
            break;
        }
        if(req()->isAjax()){
            # fetch all html to pass it to the json output
            ob_start();
            if(!post("requestCounter")){
                $hasUnreaded = false;
                $cache = session("entry.cache");
                if(!$categories){
                    $ids = array();
                }elseif($pType == "search"){
                    $feeds = user()->getFeeds();
                    $query = "
                        SELECT t0.id
                        FROM RDR_Entry as t0
                        WHERE t0.feed IN ".db()->toDb($feeds)." && t0.title LIKE ".db()->toDb("%".get("q")."%")."
                        ORDER BY datetime DESC
                    ";
                    $ids = db()->fetchColumn($query);
                }elseif($pType == "archive"){
                    $feeds = user()->getFeeds();
                    $condition = "1 ";
                    if(get("from") && dt(get("from"))->valid) $condition .= " && t0.datetime >= ".db()->toDb(dt(get("from"))->getSQLDate());
                    if(get("to") && dt(get("to"))->valid) $condition .= " && t0.datetime <= ".db()->toDb(dt(get("to"))->getSQLDate());
                    $query = "
                        SELECT t0.id
                        FROM RDR_Entry as t0
                        WHERE t0.feed IN ".db()->toDb($feeds)." && $condition
                        ORDER BY datetime DESC
                    ";
                    $ids = db()->fetchColumn($query);
                }elseif($pType == "saved"){
                    $ids = user()->saved;
                    if($ids) $ids = array_keys($ids);
                    if(!$ids) $ids = array();
                }else{
                    $ids = array();
                    if($cache){
                        user()->loadReadedFlags(array_keys($cache));
                        foreach($cache as $id => $row){
                            # if already readed than hide the entry
                            if(isset(user()->_cacheReaded[$id])) continue;
                            # feed mode
                            if($this->feed){
                                if($row["feed"] == $this->feed->getId()) $ids[$id] = $id;
                                continue;
                            }
                            # category mode
                            if($this->category){
                                if($row["category"] == $this->category->getId()) $ids[$id] = $id;
                                continue;
                            }
                            # all mode
                            $ids[$id] = $id;
                        }
                    }
                }
                # load readed flags only if required
                if($this->readedLayoutEnabled) user()->loadReadedFlags($ids);
                # if view all and no article exist, update init entry to latest read id
                if($pType == "all" && !$ids){
                    if($feeds){
                        $id = db()->fetchOne("
                            SELECT MAX(CAST(t0.k as signed))
                            FROM RDR_User_readed as t0
                            WHERE t0.o = ".user()->getId()."
                        ");

                        user()->updateInitEntry($id);
                        user()->updateNewsCache();
                        user()->readed = NULL;
                        user()->store();
                    }
                }
                session("entry.ids", $ids);
                session("entry.ids.original", $ids);
            }

            $max = RDR_Entry::ENTRIES_PER_PAGE;
            $ids = session("entry.ids");
            $entryIds = array_slice($ids, 0, $max);
            session("entry.ids", array_slice($ids, $max));

            $entries = RDR_Entry::getByIds($entryIds, true);
            foreach($entries as $entry) $this->displayEntry($entry);

            $c = count($entries);
            if($c == $max){?>
                <div id="feed-view-trigger"></div>
            <?php }else{?>
                <div class="feeds-footer">
                    <div style="display: none;"><input type="button" value="<?php echo t("mark.all.category")?>" class="btn" id="all-read-btn"/></div>
                    <?php echo t("end.newssection.1")?><br/>
                    <div class="small"><?php echo t("end.newssection.2")?></div>
                </div>
            <?php }
            $jsonData = user()->getAjaxData();
            $jsonData["count"] = count(session("entry.ids.original"));
            if($pType == "archive") $jsonData["archive"] = count($ids);
            $jsonData["content"] = ob_get_contents();
            ob_end_clean();
            echo json_encode($jsonData);
            return;
        }
        $this->parameters["title"] = $title;
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        ?>
        <div class="feeds-header"><div class="feeds-header-inner">
            <div class="small">
                <?php echo sprintf(t("found.news"), '<span id="c-news" style="font-weight: bold;">-</span>')?> · <a href="#" id="all-read"><?php echo t("mark.all.category")?></a>
            </div>
            <?php
            headline($this->getParam("title"));
            ?>
        </div></div>
        <div id="feeds"></div>
        <div id="feeds-loading" class="feeds-footer">
            <?php echo t("wait.check")?>
        </div>
        <script type="text/javascript">
        Feeds.feedPage = true;
        Feeds.initPage();
        </script>
        <?php
    }

    /**
    * Display contents for a entry
    *
    * @param RDR_Entry $entry
    */
    private function displayEntry(RDR_Entry $entry){
        $jsonData = $entry->_dbValues;
        $jsonData["id"] = $entry->getId();
        $readed = isset(user()->_cacheReaded[$entry->getId()]) || user()->setting("init.entry") >= $entry->getId();
        $saved = user()->getByKey("saved", $entry->getId());
        $categories = user()->getCategories();
        $feed = $entry->feed;
        $jsonData["contentJS"] = $feed->contentJS;
        $jsonData["feedId"] = $feed->getId();
        $category = user()->getCategoryToFeed($feed);
        $layout = "default";
        $favicon = $entry->feed->getFaviconUrl();
        if(user()->setting("layout")) $layout = user()->setting("layout");

        $smallTag = '<div class="feed-options small">';
        if(!$readed) $smallTag .= '<span><a href="#" class="readed">'.t("mark.read").'</a> · </span>';
        $smallTag .= '<span><a href="#" class="saved">'.((!$saved) ? t("saveit") : t("remove.save")).'</a> · </span>';
        $smallTag .= '<time datetime="'.$entry->datetime->getUnixtime().'"></time> · ';
        $smallTag .= t("feed").': ';
        if($favicon) $smallTag .= '<span class="favicon" style="background-image:url('.$favicon.')"></span>';
        $smallTag .= sprintf(t("feeds.2"), '<a href="'.$entry->feed->getLink().'">'.s(cut($entry->feed->name, 30)).'</a>', '<a href="'.$category->getLink().'">'.s(cut($category->name, 30)).'</a>')." · ";
        $smallTag .= t("url").': <a href="'.$entry->link.'" target="_blank">'.s(cut($entry->link, 30)).'</a>';
        if(needRole(RDR_User::ROLE_ADMIN)){
            $smallTag .= ' · <a href="#" class="adminview">'.t("adminview").'</a>';
        }
        $smallTag .= '</div>';

        $titleTag = '<h2><a href="'.$entry->link.'" target="_blank" onclick="return false;" rel="noreferrer">'.s($entry->title).'</a></h2>';

        $imageTag = '<div class="image"></div>';
        ?>
        <div class="<?php echo user()->setting("hideimages") ? 'no-feed-images' : NULL?> entry <?php echo $readed && $this->readedLayoutEnabled ? 'readed' : NULL?> layout-<?php echo s($layout)?>" id="entry-<?php echo $entry->getId()?>" data-id="<?php echo $entry->getId()?>" data-feed="<?php echo $feed->getId()?>">
            <div class="feed-start"></div>
            <?php
            switch($layout){
                case "big":
                    ?>
                    <?php echo $titleTag?>
                    <div class="clear"></div>
                    <div class="float-one">
                        <div class="inner"><?php echo $imageTag?></div>
                    </div>
                    <div class="float-two">
                        <div class="inner">
                           <?php echo $smallTag?>
                           <div class="text"></div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <?php
                break;
                case "headline":
                    echo $titleTag;
                    echo $smallTag;
                break;
                default:
                    ?>
                    <div class="float-one">
                        <div class="inner"><?php echo $imageTag?></div>
                    </div>
                    <div class="float-two">
                        <div class="inner">
                            <?php echo $titleTag?>
                            <?php echo $smallTag?>
                            <div class="text"></div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <?php
                break;
            }
            ?>
            <div class="feed-end"></div>
            <?php if(!user()->setting("noautoread") && !$readed){?>
                <div class="entry-readed" data-id="<?php echo $entry->getId()?>"></div>
            <?php }?>
            <?php if(needRole(RDR_User::ROLE_ADMIN)){?>
                <div class="adminview"></div>
            <?php }?>
        </div>

        <script type="text/javascript">
        (function(){
            var d = <?php echo json_encode($jsonData, JSON_FORCE_OBJECT)?>;
            Feeds.feedInit(d);
        })();
        </script>
        <?php
    }
}