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
* A feed
*
* @property string $name
* @property string $url
* @property CHOQ_DateTime $lastImport
* @property string $contentJS
*/
class RDR_Feed extends CHOQ_DB_Object{

    /**
    * Register the Object itself as a Type.
    * Define all required Database properties here.
    */
    static function onAutoload(){
        $type = new CHOQ_DB_Type(__CLASS__);
        $type->createMember("name")->setFieldType("varchar")->setLength(255);
        $type->createMember("url")->setFieldType("varchar")->setLength(255)->addUniqueIndex();
        $type->createMember("lastImport")->setFieldType("datetime")->setOptional(true);
        $type->createMember("contentJS")->setFieldType("text")->setOptional(true);
    }

    /**
    * Get a instance for a url
    *
    * @param string $url
    * @return self
    */
    static function get($url){
        $object = db()->getByCondition("RDR_Feed", "url = {0}", array($url));
        if($object) return reset($object);
        $object = new self(db());
        $object->url = $url;
        return $object;
    }

    /**
    * Delete feeds that not have a reference anymore
    */
    static function deleteUnusedFeeds(){
        $feeds = db()->getByQuery("RDR_Feed", "
            SELECT t0.id FROM RDR_Feed as t0
            LEFT JOIN RDR_Category_feeds as t1 ON t1.v = t0.id
            WHERE t1.id IS NULL
        ");
        db()->deleteMultiple($feeds);
    }

    /**
    * Get url to locally stored favicon
    *
    * @return string | NULL
    */
    public function getFaviconUrl(){
        $fileTypes = array("ico", "gif", "jpg", "png");
        $host = $this->getSluggedHostFromUrl();
        foreach($fileTypes as $type){
            $filename = CHOQ_ACTIVE_MODULE_DIRECTORY."/public/img/favicons/{$host}.{$type}";
            if(file_exists($filename)){
                return url()->getByAlias("public", "img/favicons/{$host}.{$type}");
            }
        }
    }

    /**
    * Get slugged host from url
    *
    * @return string
    */
    public function getSluggedHostFromUrl(){
        return slugify(parse_url($this->url, PHP_URL_HOST));
    }

    /**
    * Get link to this feed
    *
    * @return string
    */
    public function getLink(){
        return l("RDR_Feeds", array("param" => "feed-".$this->getId()));
    }

    /**
    * Get custom name for a specific category
    *
    * @param RDR_Category $category
    * @return string
    */
    public function getCustomName(RDR_Category $category){
        $key = $this->getId()."-name";
        if($category->getByKey("feedsData", $key)) return $category->getByKey("feedsData", $key);
        return $this->name;
    }

    /**
    * Delete the feed
    */
    public function delete(){
        if(!$this->getId()) return;
        db()->deleteMultiple(db()->getByCondition("RDR_Entry", "feed = {0}", array($this)));
        $cats = db()->getByQuery("RDR_Category", "
            SELECT o FROM RDR_Category_feeds
            WHERE k = ".$this->getId()."
        ");
        foreach($cats as $category){
            $feeds = $category->feeds;
            if($feeds){
                foreach($feeds as $key => $feed){
                    if(compare($this, $feed)) unset($feeds[$key]);
                    if(count($feeds) != count($category->feeds)){
                        $category->store();
                    }
                }
            }
        }
        parent::delete();
    }
}
