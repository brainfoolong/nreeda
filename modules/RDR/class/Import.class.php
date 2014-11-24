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
* Imports
*/
class RDR_Import{

    /**
    * Update all existing feeds in the database
    */
    static function updateAllFeeds(){
        ini_set("memory_limit", "256M");
        set_time_limit(6000);
        $feeds = RDR_Feed::getByCondition(NULL, NULL, "+lastImport");
        foreach($feeds as $feed){
            try{
                self::importFavicon($feed);
                self::importFeedEntries($feed);
                $feed->lastImport = dt("now");
                $feed->store();
            }catch(Exception $e){
                RDR_Event::log(RDR_Event::TYPE_ERROR, array("text" => $e->getMessage()));
                $feed->lastImport = dt("now");
                $feed->store();
            }
        }
        try{
            # cleanup old tmp files
            $dir = CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp";
            $files = CHOQ_FileManager::getFiles($dir, false, true);
            foreach($files as $file){
                if(!is_dir($file) && preg_match("~^import\.|proxy\.~i", $file) && filemtime($file) < dt("now -1 day")->getUnixtime()) unlink($file);
            }
        }catch(Exception $e){
            RDR_Event::log(RDR_Event::TYPE_ERROR, array("text" => $e->getMessage()));
        }
    }

    /**
    * Import favicon for a feeds domain
    * Refetch each favicon after 1 week
    *
    * @param RDR_Feed $feed
    */
    static function importFavicon(RDR_Feed $feed){
        $url = parse_url($feed->url);
        if($url){
            $sluggedHost = $feed->getSluggedHostFromUrl();
            $fileTypes = array("ico", "gif", "jpg", "png");
            foreach($fileTypes as $type){
                $filename = CHOQ_ACTIVE_MODULE_DIRECTORY."/public/img/favicons/{$sluggedHost}.{$type}";
                if(file_exists($filename)) {
                    if(filemtime($filename) < dt("-1  week")->getUnixtime()){
                        unlink($filename);
                    }else{
                        return;
                    }
                }
                $favUrl = $url["scheme"]."://".$url["host"]."/favicon.{$type}";
                $favData = @file_get_contents($favUrl);
                if($favData){
                    file_put_contents($filename, $favData);
                    return;
                }
                if($type == "png"){
                    $favUrl = $url["scheme"]."://".$url["host"]."/apple-touch-icon.{$type}";
                    $favData = @file_get_contents($favUrl);
                    if($favData){
                        file_put_contents($filename, $favData);
                        return;
                    }
                }
            }
        }
    }

    /**
    * Import entries for feed
    *
    * @param RDR_Feed $feed
    * @return RDR_Event The last saved event for that import
    */
    static function importFeedEntries(RDR_Feed $feed){
        ini_set("default_socket_timeout", 5);
        RDR_Event::log(RDR_Event::TYPE_FEED_UPDATE_START, array("feed" => $feed));
        $xml = self::getSimpleXMLFromUrl($feed->url);
        if(!$xml) return RDR_Event::$lastEvent;
        $count = 0;
        $rss = $xml->xpath("/rss");
        if($rss){
            $rss = reset($rss);
            switch(self::xmlAttr($rss, "version")){
                default:
                    $items = $xml->xpath("/rss/channel/item");
                    foreach($items as $item) if(self::createEntryForItem($item, $feed)) $count++;
                    return RDR_Event::log(RDR_Event::TYPE_FEED_UPDATE_END, array("int" => $count, "feed" => $feed));
                break;
            }
        }
        if(isset($xml->entry)){
            foreach($xml->entry as $item) if(self::createEntryForItem($item, $feed)) $count++;
            return RDR_Event::log(RDR_Event::TYPE_FEED_UPDATE_END, array("int" => $count, "feed" => $feed));
        }
        if(isset($xml->item)){
            foreach($xml->item as $item) if(self::createEntryForItem($item, $feed)) $count++;
            return RDR_Event::log(RDR_Event::TYPE_FEED_UPDATE_END, array("int" => $count, "feed" => $feed));
        }
        return RDR_Event::log(RDR_Event::TYPE_MISSING_PARSER, array("feed" => $feed));
    }

    /**
    * Import new feed from url
    *
    * @param mixed $url
    * @param RDR_Category $category
    * @return RDR_Event The last event for that import
    */
    static function addFeed($url, RDR_Category $category){
        $feed = RDR_Feed::get($url);
        if($feed->getId()){
            $categories = user()->getCategories();
            $feedExist = false;
            foreach($categories as $category){
                if($category->getByKey("feeds", $feed->getId())) {
                    $feedExist = true;
                    break;
                }
            }
            if(!$feedExist){
                $category->add("feeds", $feed);
                $category->store();
                return RDR_Event::log(RDR_Event::TYPE_FEED_ADD_OK, array("feed" => $feed));
            }
            return RDR_Event::log(RDR_Event::TYPE_FEED_EXIST, array("text" => $url));
        }

        $xml = self::getSimpleXMLFromUrl($feed->url);
        if(!$xml) return RDR_Event::$lastEvent;

        $title = "";
        $tmp = $xml->xpath("/rss/channel/title");
        if($tmp) $title = self::xmlValue($tmp);
        if(!$title){
            $tmp = $xml->title;
            if($tmp) $title = self::xmlValue($tmp);
        }
        if(!$title){
            $tmp = $xml->channel && $xml->channel->title ? $xml->channel->title : NULL;
             if($tmp) $title = self::xmlValue($tmp);
        }
        if(!$title){
            return RDR_Event::log(RDR_Event::TYPE_MISSING_PARSER, array("text" => $url.":title"));
        }

        $feed->name = trim($title);
        $feed->store();
        $category->add("feeds", $feed);
        $category->store();
        return RDR_Event::log(RDR_Event::TYPE_FEED_ADD_OK, array("feed" => $feed));
    }

    /**
    * Import feeds from a line based file
    *
    * @param string $file
    * @return RDR_Event The last event for that import
    */
    static function importFromFile($file){
        $data = file($file);
        foreach($data as $url){
            $url = trim($url);
            if(substr($url, 0, 4) == "http"){
                self::addFeed($url, RDR_Category::get(t("uncategorized")));
            }
        }
        return RDR_Event::log(RDR_Event::TYPE_FILE_OK);
    }

    /**
    * Import feeds from opml file
    *
    * @param string $file
    * @return RDR_Event The last event for that import
    */
    static function importFromOPML($file){
        $xml = self::getSimpleXMLFromUrl($file);
        if(!$xml) return RDR_Event::$lastEvent;
        if($xml->getName() != "opml"){
            return RDR_Event::log(RDR_Event::TYPE_MISSING_PARSER, array("text" => "OPML:".$file));
        }
        self::_importFromOPML($xml->xpath("/opml/body/outline"), RDR_Category::get(t("uncategorized")));
        return RDR_Event::log(RDR_Event::TYPE_OPML_OK);
    }

    /**
    * Recursive through all entries
    *
    * @param SimpleXMLElement[] $elements
    * @param int $level
    */
    static private function _importFromOPML(array $elements, RDR_Category $category){
        foreach($elements as $element){
            $attributes = $element->attributes();
            if(!isset($attributes->xmlUrl)){
                self::_importFromOPML($element->xpath("outline"), RDR_Category::get((string)$attributes->title));
            }else{
                self::addFeed((string)$attributes->xmlUrl, $category);
            }
        }
    }

    /**
    * Get correct formted value from a simplexml node
    *
    * @param mixed $node
    * @return string
    */
    static private function xmlValue($node){
        if(is_array($node)) $node = reset($node);
        $string = trim((string)$node);
        if(defined("ENT_HTML401")){
            $string = html_entity_decode($string, ENT_QUOTES | ENT_HTML401, CHOQ::$encoding);
            $string = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, CHOQ::$encoding);
            $string = html_entity_decode($string, ENT_QUOTES | ENT_XHTML, CHOQ::$encoding);
        }else{
            $string = html_entity_decode($string, ENT_QUOTES, CHOQ::$encoding);
            $string = html_entity_decode($string, ENT_QUOTES, CHOQ::$encoding);
        }
        return $string;
    }

    /**
    * Get correct formted attribute from a simplexml node
    *
    * @param mixed $node
    * @param string $attr
    * @return string
    */
    static private function xmlAttr($node, $attr){
        if(!$node) return "";
        if(is_array($node)) $node = reset($node);
        $tmp = $node->attributes();
        return isset($tmp->{$attr}) ? self::xmlValue($tmp->{$attr}) : "";
    }

    /**
    * Get contents from a url
    *
    * @param mixed $url
    * @return string
    */
    static function getURLContent($url){
        $dir = CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp";
        $file = "{$dir}/import.".md5($url);
        if(!file_exists($file) || filemtime($file) < time() - 300){
            $data = self::fileGetContents($url);
            if(!$data){
                RDR_Event::log(RDR_Event::TYPE_FEED_URLERROR, array("text" => $url));
                return;
            }
            file_put_contents($file, $data);
        }else{
            $data = self::fileGetContents($file);
        }
        return $data;
    }

    /**
    * File get contents with correct headers
    *
    * @param mixed $url
    * @return string
    */
    static private function fileGetContents($url){
        if(!preg_match("~^http|https~i", $url) && file_exists($url)) return file_get_contents($url);
        $context = stream_context_create(array(
            'http'=>array(
                'method'=>"GET",
                'header'=>
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
                    "Accept-Language: en-US,en;q=0.8\r\n".
                    "Keep-Alive: timeout=3, max=10\r\n",
                    "Connection: keep-alive",
                'user_agent'=>"User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.66 Safari/535.11",
                "ignore_errors" => true
            )
        ));
        $data = @file_get_contents($url, false, $context);
        return $data;
    }

    /**
    * Get Simple xml from a feed url
    *
    * @param mixed $url
    * @return SimpleXMLElement | bool
    */
    static private function getSimpleXMLFromUrl($url){
        $data = self::getURLContent($url);
        if(!$data) return false;
        $xml = @simplexml_load_string($data, NULL, LIBXML_NOCDATA);
        if(!$xml){
            RDR_Event::log(RDR_Event::TYPE_SIMPLEXML_ERROR, array("text" => $url));
            return false;
        }
        return $xml;
    }

    /**
    * Create a entry for given item
    *
    * @param SimpleXMLElement $item
    * @param RDR_Feed $feed
    * @return RDR_Entry | NULL
    */
    static private function createEntryForItem($item, RDR_Feed $feed){
        $guid = self::xmlValue($item->guid);
        if(!$guid) $guid = self::xmlValue($item->id);
        if(!$guid) $guid = self::xmlValue($item->link);

        $entry = RDR_Entry::get($feed, $guid);

        $now = dt("now");

        $entry->title = NULL;
        $entry->title = cut(self::xmlValue($item->title), 252, "...");

        $entry->text = NULL;
        $entry->text = self::xmlValue($item->description);
        if(!$entry->text) $entry->text = self::xmlValue($item->content);
        if(!$entry->text) $entry->text = self::xmlValue($item->summary);

        $entry->datetime = NULL;
        $entry->datetime = dt(self::xmlValue($item->pubDate));
        if(!$entry->datetime->valid) $entry->datetime = dt(self::xmlValue($item->published));
        if(!$entry->datetime->valid) $entry->datetime = dt(self::xmlValue($item->updated));
        if($entry->datetime->valid && $entry->datetime->getUnixtime() > $now->getUnixtime()) $entry->datetime = $now;

        $count = count($item->link);
        foreach($item->link as $link){
            if($count > 1){
                if(self::xmlAttr($link, "type") == "text/html") $entry->link = self::xmlAttr($link, "href");
            }else{
                $entry->link = self::xmlValue($link);
            }
        }

        if(!$entry->link) $entry->link = self::xmlValue($item->link);
        if(!$entry->link) $entry->link = self::xmlAttr($item->link, "href");

        $entry->image = NULL;
        $enclosures = $item->xpath("enclosure");
        if($enclosures){
            foreach($enclosures as $encl){
                if($entry->image) break;
                if(self::xmlAttr($encl, "type")){
                    if(strpos(self::xmlAttr($encl, "type"), "image") !== false){
                        $entry->image = self::xmlAttr($encl, "url");
                    }
                }elseif(self::xmlAttr($encl, "url")){
                    if(preg_match("~jpg|jpeg|gif|png~i", self::xmlAttr($encl, "url"))){
                        $entry->image = self::xmlAttr($encl, "url");
                    }
                }
            }
        }

        $namespaces = $item->getNameSpaces(true);
        if($namespaces){
            foreach($namespaces as $ns){
                $tmp = $item->children($ns);
                foreach($tmp as $key => $value){
                    if(!$entry->datetime->valid && strpos($key, "date") !== false){
                        $entry->datetime = dt(self::xmlValue($value));
                    }
                    if(!$entry->title && strpos($key, "title") !== false){
                        $entry->title = cut(self::xmlValue($value), 252, "...");
                    }
                    if(strpos($key, "description") !== false ||  strpos($key, "content") !== false ||  strpos($key, "encoded") !== false){
                        $text = self::xmlValue($value);
                        if($text && (!$entry->text || mb_strlen($text) > $entry->text)){
                            $entry->text = $text;
                        }
                        if(!$entry->image){
                            $attr = $value->attributes();
                            $url = isset($attr->url) ? self::xmlValue($attr->url) : NULL;
                            $type = isset($attr->type) ? self::xmlValue($attr->type) : NULL;
                            if($url && (preg_match("~\.(jpg|jpeg|png|gif)~i", $url) || preg_match("~image\/~i", $type))){
                                $entry->image = $url;
                            }
                        }
                    }
                }
            }
        }


        # if datetime is older than maxlifetime than don't store
        if(!$entry->datetime->valid) $entry->datetime = $now;
        $time = RDR_Setting::get("maxentrylifetime")->value;
        if($time && $entry->datetime->getUnixtime() < dt("now $time")->getUnixtime()) return;

        $entry->store();
        return $entry;
    }
}