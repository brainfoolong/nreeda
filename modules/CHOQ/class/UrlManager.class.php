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
* URL Manager
*/
class CHOQ_UrlManager{

    /**
     * Instance
     *
     * @var self
     */
    static private $instance;

    /**
    * The uri prefix for all generated urls, if required
    *
    * @var string
    */
    public $uriPrefix;

    /**
    * Prepend the language in the uri
    *
    * @var nool
    */
    public $languageInUri = false;

    /**
    * Defined url aliases
    *
    * @var string[]
    */
    private $aliases = array();

    /**
     * Get instance of itself
     *
     * @return self
     */
    static function getInstance(){
        if(!self::$instance) self::$instance = new self;
        return self::$instance;
    }

    /**
    * Set the uri prefix
    *
    * @param mixed $prefix
    */
    public function setUriPrefix($prefix){
        if(!$prefix){
            $this->uriPrefix = NULL;
        }else{
            $this->uriPrefix = trim($prefix, " /");
        }
    }

    /**
    * Set language in uri is prepended
    *
    * @param bool $bool
    */
    public function setLanguageInUri($bool){
        $this->languageInUri = $bool;
    }

    /**
    * Add a short alias for a url
    *
    * @param mixed $alias
    * @param mixed $url
    */
    public function addAlias($alias, $url){
        $this->aliases[$alias] = $url;
    }

    /**
    * Get alias mapped url
    *
    * @param mixed $alias
    * @param string $append Append URI to output
    * @return string|NULL
    */
    public function getByAlias($alias, $append = NULL){
        $url = arrayValue($this->aliases, $alias);
        if($append) $url = rtrim($url, " /")."/".ltrim($append, " /");
        return $url;
    }

    /**
    * Get current uri as is
    *
    * @param bool $secure If true than cleaned up from some unsecure html characters
    * @return string
    */
    public function getUri($secure = true){
        $uri = urldecode(arrayValue($_SERVER, 'REQUEST_URI'));
        $uri = str_replace(array('"', "'", "<", ">"), "", $uri);
        return $uri;
    }

    /**
    * Get clean uri = Uri without GET parameters
    *
    * @param bool $secure If true than cleaned up from some unsecure html characters
    * @return string
    */
    public function getCleanUri($secure = true){
        $uri = $this->getUri($secure);
        $pos = strpos($uri, "?");
        if($pos !== false) $uri = mb_substr($uri, 0, $pos);
        return $uri;
    }

    /**
    * Get current uri with modifications
    *
    * @param mixed $params
    *   If is NULL than no changed will be made for the $_GET params
    *   If is FALSE than all $_GET parameters will be removed,
    *   If is a array than all key/value pairs will be replaced, if array value is FALSE than this single parameter will be removed
    * @param mixed $includePrefix If false than remove the $this->uriPrefix from the uri
    * @param mixed $includeLanguage
    *    If false than remove the prepended language (if exist) from uri,
    *    If bool/true than include the current language
    *    If string than include the given language
    * @param bool $secure If true than cleaned up from some unsecure html characters
    * @return string
    */
    public function getModifiedUri($params = NULL, $includePrefix = true, $includeLanguage = true, $secure = true){
        $uri = $this->getUri($secure);
        if(!$includePrefix && $this->uriPrefix) $uri = preg_replace("~^/".$this->uriPrefix."/~", "/", $uri, 1);
        if($includeLanguage === false || is_string($includeLanguage)){
            $uri = $this->getModifiedUri(NULL, false, true, $secure);
            foreach(CHOQ_LanguageManager::$languages as $lang){
                $count = 0;
                $uri = preg_replace("~^/".$lang."(/|$)~", "/", $uri, 1, $count);
                if($count) break;
            }
            $uri = ($includePrefix ? "/".$this->uriPrefix : "").(is_string($includeLanguage) ? "/".$includeLanguage : "").$uri;
        }
        if($params === false){
            $pos = strpos($uri, "?");
            if($pos !== false) $uri = mb_substr($uri, 0, $pos);
        }elseif(is_array($params)){
            $uriParams = parse_url($uri, PHP_URL_QUERY);
            if(!$uriParams){
                $uriParams = array();
            }else{
                parse_str($uriParams, $uriParams);
            }
            $uriParams = array_merge($uriParams, $params);
            foreach($uriParams as $key => $value){
                if(isset($params[$key])){
                    $uriParams[$key] = $value;
                    if($params[$key] === false){
                        unset($uriParams[$key]);
                    }
                }
            }
            $uri = $this->getModifiedUri(false, $includePrefix, $includeLanguage, $secure);
            if($uriParams) $uri .= "?".http_build_query($uriParams, NULL, "&");
        }
        return $uri;
    }
}