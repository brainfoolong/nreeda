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

/**
* User
*
* @property string $username
* @property string $salt
* @property string $password
* @property int $role
* @property mixed[] $settings
* @property int[] $saved
* @property int[] $readed
*/
class RDR_User extends CHOQ_DB_Object{

    /**
    * Role admin
    */
    const ROLE_ADMIN = 1;

    /**
    * Role user
    */
    const ROLE_USER = 2;

    /**
    * Maximal entry limit
    */
    const MAX_ENTRY_LIMIT = 5000;

    /**
    * Cache
    *
    * @var array
    */
    static private $_cache;

    /**
    * The local user cache
    *
    * @var self
    */
    static $user;

    /**
    * Readed cache
    *
    * @var array
    */
    public $_cacheReaded;

    /**
    * Register the Object itself as a Type.
    * Define all required Database properties here.
    */
    static function onAutoload(){
        $type = new CHOQ_DB_Type(__CLASS__);
        $type->createMember("username")->setFieldType("varchar")->setLength(30)->addUniqueIndex();
        $type->createMember("salt")->setFieldType("char")->setLength(128);
        $type->createMember("password")->setFieldType("char")->setLength(128);
        $type->createMember("role")->setFieldType("tinyint")->setLength(1);
        $type->createMember("settings")->setFieldType("array", "text")->setOptional(true);
        $type->createMember("saved")->setFieldType("array", "int")->setOptional(true);
        $type->createMember("readed")->setFieldType("array", "int")->setOptional(true);
    }

    /**
    * Login with username and password of all is correct
    *
    * @param mixed $username
    * @param mixed $password
    * @param bool $remember
    * @return self | null
    */
    static function login($username, $password, $remember = false){
        $user = RDR_User::getByCondition("username = {0}", array($username));
        if($user){
            $user = reset($user);
            if($user->passwordMatch($password)){
                session("user.id", $user->getId());
                if($remember) {
                    cookie("user-id", $user->getId(), dt("now + 1 year")->getUnixtime());
                    cookie("user-id-salted", saltedHash("sha256",$user->getId()), dt("now + 1 year")->getUnixtime());
                }
                return $user;
            }
        }
    }

    /**
    * Logout user
    */
    static function logout(){
        self::$user = false;
        session("name");
        session_unset();
        cookie("user-id", 0, 0);
        cookie("user-id-salted", 0, 0);
    }

    /**
    * Load the readed flags for the given ids
    *
    * @param array $ids
    */
    public function loadReadedFlags($ids){
        if(!$ids){
            $this->_cacheReaded = null;
            return;
        }
        $this->_cacheReaded = db()->fetchColumn("SELECT v, k FROM RDR_User_readed WHERE o = ".user()->getId()." && k IN ".db()->toDb($ids), "k");
    }

    /**
    * Get global ajax data for this user
    *
    * @return array
    */
    public function getAjaxData(){
        $cache = session("entry.cache");
        $jsonData = array("all" => 0, "saved" => arrayValue($this->_dbValues, "saved", 0), "archive" => 0);
        if($cache){
            $this->loadReadedFlags(array_keys($cache));
            foreach($cache as $id => $row){
                if(isset($this->_cacheReaded[$id])) continue;
                if(!isset($jsonData[$row["feed"]])) $jsonData[$row["feed"]] = 0;
                if(!isset($jsonData[$row["category"]])) $jsonData[$row["category"]] = 0;
                $jsonData[$row["feed"]]++;
                $jsonData[$row["category"]]++;
                $jsonData["all"]++;
            }
        }
        return $jsonData;
    }

    /**
    * Update the news cache for current user
    */
    public function updateNewsCache(){
        $feeds = $this->getFeeds();
        $categories = $this->getCategories();
        session("entry.cache", false);
        if(!$feeds) return;

        $lowestId = db()->fetchOne("
            SELECT t1.id
            FROM RDR_Feed as t0
            JOIN RDR_Entry as t1 ON t1.feed = t0.id
            WHERE t0.id IN ".db()->toDb($feeds)."
            ORDER BY datetime DESC
            LIMIT 1
            OFFSET ".self::MAX_ENTRY_LIMIT."
        ");
        $this->updateInitEntry($lowestId);
        $query = "
            SELECT t0.id, t0.feed as feed, t1.o as category
            FROM RDR_Entry as t0
            JOIN RDR_Category_feeds as t1 ON t1.o IN ".db()->toDb($categories)." AND  t1.k = t0.feed
            WHERE t0.id > ".(int)$this->setting("init.entry")."
            ORDER BY datetime DESC, id DESC
        ";
        session("entry.cache", db()->fetchAsAssoc($query, "id"));
    }

    /**
    * Update init entry
    *
    * @param mixed $id
    * @return bool If updated or not
    */
    public function updateInitEntry($id){
        if($id && $id > $this->setting("init.entry")){
            $this->setting("init.entry", $id);
            $readed = $this->readed;
            if($readed){
                foreach($readed as $k => $v){
                    if($v < $id) unset($readed[$k]);
                }
                $this->readed = $readed;
            }
            $this->store();
            return true;
        }
        return false;
    }

    /**
    * Get all categories to this user
    *
    * @return RDR_Category[]
    */
    public function getCategories(){
        if(self::$_cache === null){
            self::$_cache = RDR_Category::getByCondition(db()->quote("user")." = {0}", array($this), "+name");
        }
        return self::$_cache;
    }

    /**
    * Get all available feeds for this user
    *
    * @return RDR_Feed[]
    */
    public function getFeeds(){
        $categories = $this->getCategories();
        if(!$categories) return array();
        $feeds = array();
        $sort = array();
        foreach($categories as $category){
            $tmp = $category->feeds;
            if($tmp) {
                foreach($tmp as $feed){
                    $feeds[$feed->getId()] = $feed;
                    $sort[$feed->getId()] = mb_strtolower($feed->getCustomName($category));
                }
            }
        }
        array_multisort($sort, SORT_ASC, $feeds);
        $arr = array();
        foreach($feeds as $feed) $arr[$feed->getId()] = $feed;
        return $arr;
    }

    /**
    * Get the users category to the feed
    *
    * @param RDR_Feed $feed
    * @return RDR_Category | null
    */
    public function getCategoryToFeed(RDR_Feed $feed){
        $categories = $this->getCategories();
        if(!$categories) return;
        foreach($categories as $category){
            $tmp = $category->feeds;
            if($tmp) {
                foreach($tmp as $f) if($f->getId() == $feed->getId()) return $category;
            }
        }
    }


    /**
    * Get/Set user setting
    *
    * @param mixed $key
    * @param mixed $value
    * @return mixed
    */
    public function setting($key, $value = null){
        if($value === null){
            return $this->getByKey("settings", $key);
        }else{
            $this->add("settings", $value, $key);
            return $value;
        }
    }

    /**
    * Set password
    *
    * @param string $password
    */
    public function setPassword($password){
        $this->salt = saltedHash("sha512", microtime().uniqid());
        $this->password = saltedHash("sha512", $this->salt.$password);
    }

    /**
    * Check if password match with the stored one
    *
    * @param string $password
    * @return bool
    */
    public function passwordMatch($password){
        return $this->password === saltedHash("sha512", $this->salt.$password);
    }

    /**
    * Update the users readed count
    */
    public function updateReadedCount(){
        $count = db()->fetchOne("SELECT COUNT(id) FROM RDR_User_readed WHERE o = ".$this->getId());
        db()->query("UPDATE RDR_User SET readed = ".db()->toDb($count ? $count : null).", updateTime = ".db()->toDb(dt("now")->getSQLDateTime())." WHERE id = ".$this->getId());
    }

    /**
    * On new user set the newest entry id for the newest check
    */
    public function store(){
        if(!$this->getId()){
            $this->setting("init.entry", db()->fetchOne("SELECT MAX(id) FROM RDR_Entry"));
        }
        parent::store();
    }
}
