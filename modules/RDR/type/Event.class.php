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
* A event
*
* @property RDR_Feed $feed
* @property RDR_Entry $entry
* @property RDR_User $user
* @property int $int
* @property text $text
* @property int $type
*/
class RDR_Event extends CHOQ_DB_Object{

    const TYPE_FEED_UPDATE_START = 1;
    const TYPE_FEED_UPDATE_END = 2;
    const TYPE_MISSING_PARSER = 5;
    const TYPE_FEED_URLERROR = 6;
    const TYPE_FEED_EXIST = 8;
    const TYPE_SIMPLEXML_ERROR = 7;
    const TYPE_CRON_START = 3;
    const TYPE_CRON_END = 4;
    const TYPE_ERROR = 9;
    const TYPE_OPML_OK = 10;
    const TYPE_FEED_ADD_OK = 11;
    const TYPE_FILE_OK = 12;
    const TYPE_CLEANUP_ENTRY = 13;
    const TYPE_CLEANUP_EVENT = 14;
    const TYPE_CLEANUP_FEEDS = 15;
    const TYPE_CRON_RUNNING = 16;
    const TYPE_URL_ERROR = 17;

    /**
    * The last create event
    *
    * @var self
    */
    static $lastEvent;

    /**
    * Register the Object itself as a Type.
    * Define all required Database properties here.
    */
    static function onAutoload(){
        $type = new CHOQ_DB_Type(__CLASS__);
        $type->createMember("feed")->setFieldType("RDR_Feed")->setOptional(true);
        $type->createMember("entry")->setFieldType("RDR_Entry")->setOptional(true);
        $type->createMember("user")->setFieldType("RDR_User")->setOptional(true);
        $type->createMember("int")->setFieldType("int")->setOptional(true);
        $type->createMember("text")->setFieldType("text")->setOptional(true);
        $type->createMember("type")->setFieldType("int");
    }

    /**
    * Create a log entry
    *
    * @param int $type
    * @param array $params
    * @return self
    */
    static function log($type, $params = null){
        $object = new self(db());
        $object->type = $type;
        if(is_array($params)){
            foreach($params as $key => $value) $object->{$key} = $value;
        }
        $object->store();
        self::$lastEvent = $object;
        return $object;
    }

    /**
    * Old entries will be deleted automatically with this cleanup
    */
    static function cleanup(){
        $time = RDR_Setting::get("maxeventlifetime")->value;
        if(!$time) $time = "- 1 day";
        while(true){
            $tmp = self::getByCondition("createTime < {0}", array(dt("now $time")), null, 1000);
            if(!$tmp) break;
            db()->deleteMultiple($tmp);
        }
    }

    /**
    * Get the translated event text
    *
    * @return string
    */
    public function getText(){
        $search = array("{INT}", "{FEED}", "{ENTRY}", "{USER}", "{TEXT}");
        $params = array(
            $this->int,
            $this->feed ? '<a href="'.$this->feed->getLink().'">'.$this->feed->name.'</a>' : "",
            $this->entry ? '<a href="'.$this->entry->getLink().'">'.$this->entry->title.'</a>' : "",
            $this->user ? $this->user->username : "",
            $this->text
        );
        return str_replace($search, $params, t("event.".$this->type));
    }
}
