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
* A feed entry
*
* @property string $uniqueId
* @property RDR_Feed $feed
* @property string $title
* @property string $link
* @property string $image
* @property string $text
* @property CHOQ_DateTime $datetime
*/
class RDR_Entry extends CHOQ_DB_Object{

    /**
    * Max entries per load
    */
    const ENTRIES_PER_PAGE = 10;

    /**
    * Internal cache
    *
    * @var mixed[]
    */
    static private $_cache;

    /**
    * Register the Object itself as a Type.
    * Define all required Database properties here.
    */
    static function onAutoload(){
        $type = new CHOQ_DB_Type(__CLASS__);
        $type->createMember("uniqueId")->setFieldType("varchar")->setLength(50)->addUniqueIndex();
        $type->createMember("feed")->setFieldType("RDR_Feed");
        $type->createMember("title")->setFieldType("varchar")->setLength(255)->addIndex();
        $type->createMember("text")->setFieldType("text");
        $type->createMember("link")->setFieldType("varchar")->setLength(500);
        $type->createMember("image")->setFieldType("varchar")->setLength(750)->setOptional(true);
        $type->createMember("datetime")->setFieldType("datetime");
    }

    /**
    * Get a instance for a unique id and feed
    *
    * @param RDR_Feed $feed
    * @param mixed $uniqueId
    * @return self
    */
    static function get(RDR_Feed $feed, $uniqueId){
        $uniqueId = $feed->getId()."-".md5($uniqueId);
        $object = RDR_Entry::getByCondition("uniqueId = {1}", array($feed, $uniqueId));
        if($object) return reset($object);
        $object = new self(db());
        $object->feed = $feed;
        $object->uniqueId = $uniqueId;
        return $object;
    }
}
