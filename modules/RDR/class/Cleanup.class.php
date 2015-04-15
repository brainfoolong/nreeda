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
* General Cleanups
*/
class RDR_Cleanup{

    /**
    * Cleanup all readed/saved flags
    */
    static function cleanupFlags(){
        $tables = array("readed", "saved");
        foreach($tables as $table){
            $usertable = "RDR_User_{$table}";
            $query = "DELETE FROM $usertable WHERE (SELECT RDR_Entry.id FROM RDR_Entry WHERE $usertable.k = RDR_Entry.id) IS NULL";
            db()->query($query);
            $query = "UPDATE RDR_User SET $table = (SELECT COUNT($usertable.id) FROM $usertable WHERE $usertable.o = RDR_User.id)";
            db()->query($query);
            $query = "UPDATE RDR_User SET $table = IF($table = 0, NULL, $table)";
            db()->query($query);
        }
    }

    /**
    * Old entries will be deleted automatically with this cleanup
    */
    static function cleanupEntries(){
        $time = RDR_Setting::get("maxentrylifetime")->value;
        if(!$time) return;
        $updated = false;
        while(true){
            $tmp = RDR_Entry::getByCondition("datetime < {0}", array(dt("now $time")), null, 1000);
            if(!$tmp) break;
            db()->deleteMultiple($tmp);
            $updated = true;
        }
        # after deleting some entries update readed/saved flags
        if($updated) self::cleanupFlags();
    }

    /**
    * Old entries will be deleted automatically with this cleanup
    */
    static function cleanupEvents(){
        $time = RDR_Setting::get("maxeventlifetime")->value;
        if(!$time) $time = "- 1 day";
        while(true){
            $tmp = RDR_Event::getByCondition("createTime < {0}", array(dt("now $time")), null, 1000);
            if(!$tmp) break;
            db()->deleteMultiple($tmp);
        }
    }

    /**
    * Cleanup unused feeds
    */
    static function cleanupFeeds(){
        $feeds = RDR_Feed::getByQuery("
            SELECT t0.id FROM RDR_Feed as t0
            LEFT JOIN RDR_Category_feeds as t1 ON t1.v = t0.id
            WHERE t1.id IS null
        ");
        if($feeds){
            db()->deleteMultiple($feeds);
        }
    }
}