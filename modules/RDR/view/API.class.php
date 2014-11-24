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
* The API
*/
class RDR_API extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        if(!needRole()) return;

        $jsonData = NULL;
        switch(post("action")){
            case "delete-feed-user":
                $feed = RDR_Feed::getById(post("data[fid]"));
                if($feed){
                    $cats = user()->getCategories();
                    foreach($cats as $category){
                        $feeds = $category->feeds;
                        if($feeds){
                            foreach($feeds as $key => $catFeed){
                                if(compare($catFeed, $feed)) {
                                    unset($feeds[$key]);
                                }
                            }
                            if(count($feeds) != count($category->feeds)){
                                $category->feeds = $feeds;
                                $category->store();
                            }
                        }
                    }
                }
            break;
            case "delete-feed-admin":
                if(needRole(RDR_User::ROLE_ADMIN)){
                    $feed = RDR_Feed::getById(post("data[fid]"));
                    if($feed){
                        $feed->delete();
                    }
                }
            break;
            case "add-feed":
                $event = RDR_Import::addFeed(post("data[url]"), RDR_Category::get(post("data[category]")));
                if($event->feed) RDR_Import::importFeedEntries($event->feed);
            break;
            case "mark-all-as-readed":
                $cache = session("entry.ids.original");
                if($cache) {
                    $ids = array_keys($cache);
                    user()->loadReadedFlags(array_keys($ids));
                    $insertIds = array();
                    foreach($ids as $id){
                        if(!isset(user()->_cacheReaded[$id])) $insertIds[$id] = $id;
                    }
                    if($insertIds){
                        $query = "INSERT IGNORE INTO RDR_User_readed (o,k,v) VALUES ";
                        foreach($insertIds as $id) $query .= " (".user()->getId().", $id, 1), ";
                        $query = substr($query, 0, -2);
                        db()->query($query);
                        user()->updateReadedCount();
                    }
                }
            break;
            case "update-setting-user":
                user()->setting(post("data[key]"), post("data[value]"));
                user()->store();
            break;
            case "update-newscache":
                user()->updateNewsCache();
                $jsonData = user()->getAjaxData();
            break;
            case "set-entries-readed":
                if(post("data[ids]")){
                    $entries = RDR_Entry::getByIds(post("data[ids]"));
                    if($entries) {
                        user()->loadReadedFlags(array_keys($entries));
                        $insertIds = $deleteIds = array();
                        foreach($entries as $entry){
                            $id = $entry->getId();
                            if($id < user()->setting("init.entry")) continue;
                            if(isset(user()->_cacheReaded[$id])){
                                $deleteIds[$id] = $id;
                            }else{
                                $insertIds[$id] = $id;
                            }
                        }
                        if($insertIds){
                            $query = "INSERT IGNORE INTO RDR_User_readed (o,k,v) VALUES ";
                            foreach($insertIds as $id) $query .= " (".user()->getId().", $id, 1), ";
                            $query = substr($query, 0, -2);
                            db()->query($query);
                        }
                        if($deleteIds){
                            $query = "DELETE FROM RDR_User_readed WHERE o = ".user()->getId()." && k IN ".db()->toDb($deleteIds);
                        }
                        user()->updateReadedCount();
                    }
                }
            break;
            case "set-entries-saved":
                if(post("data[ids]")){
                    $entry = RDR_Entry::getById(post("data[ids][0]"));
                    if($entry) {
                        if(user()->getByKey("saved", $entry->getId())){
                            user()->remove("saved", $entry->getId());
                        }else{
                            user()->add("saved", 1, $entry->getId());
                        }
                        user()->store();
                    }
                }
            break;
            case "set-feed-property":
                if(needRole(RDR_User::ROLE_ADMIN)){
                    $feed = RDR_Feed::getById(post("data[feed]"));
                    if($feed){
                        $feed->{post("data[field]")} = post("data[value]");
                        $feed->store();
                    }
                }
            break;
        }
        echo json_encode($jsonData, JSON_FORCE_OBJECT);
    }
}