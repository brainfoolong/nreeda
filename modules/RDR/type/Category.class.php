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
* A category
*
* @property RDR_User $user
* @property string $name
* @property RDR_Feed[] $feeds
* @property mixed[] $feedsData
*/
class RDR_Category extends CHOQ_DB_Object{

    /**
    * Register the Object itself as a Type.
    * Define all required Database properties here.
    */
    static function onAutoload(){
        $type = new CHOQ_DB_Type(__CLASS__);
        $type->createMember("user")->setFieldType("RDR_User");
        $type->createMember("name")->setFieldType("varchar")->setLength(255)->addIndex();
        $type->createMember("feeds")->setFieldType("array", "RDR_Feed")->setOptional(true);
        $type->createMember("feedsData")->setFieldType("array", "text")->setOptional(true);
    }

    /**
    * Get a instance for a name
    *
    * @param string $name
    * @return self
    */
    static function get($name){
        $object = RDR_Category::getByCondition(db()->quote("user")." = {1} AND name = {0}", array($name, user()));
        if($object) return reset($object);
        $object = new self(db());
        $object->name = $name;
        $object->user = user();
        $object->store();
        return $object;
    }

    /**
    * Get link to this category
    *
    * @return string
    */
    public function getLink(){
        return l("RDR_Feeds", array("param" => "cat-".$this->getId()));
    }

    /**
    * Store
    */
    public function store(){
        parent::store();
        RDR_Cleanup::cleanupFeeds();
    }

    /**
    * Delete
    */
    public function delete(){
        parent::delete();
        RDR_Cleanup::cleanupFeeds();
    }
}
