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
* View Base Class and Manager
*/
abstract class CHOQ_View{

    /**
    * The mapping array
    *
    * @var mixed[]
    */
    static private $mapping = array();

    /**
    * Count how many different url mappings are added
    *
    * @var int
    */
    static $mapCounter = 0;

    /**
    * The parameters when url mapping is a regex
    *
    * @var array|null
    */
    public $parameters;

    /**
    * The matched priority
    *
    * @var int
    */
    public $priority;

    /**
    * Map a view to a url
    *
    * @param mixed $viewClass The View Class to map to
    * @param mixed $url The exact url to match, case sensitive
    * @param int $priority The url priority, if 2 urls match than the view with the higher priority will be taken
    */
    static function mapViewToUrl($viewClass, $url, $priority = 0){
        self::$mapping[$priority][$viewClass] = array($url, false);
    }

    /**
    * Map a view to a url regex
    * The regex can must match exactly (^REGEX$) and all regex parameters (?<param>...) are piped to entry point
    *
    * @param mixed $viewClass The View Class to map to
    * @param mixed $url The regex to match, case sensitive
    * @param int $priority The url priority, if 2 urls match than the view with the higher priority will be taken
    */
    static function mapViewToUrlRegex($viewClass, $regex, $priority = 0){
        self::$mapping[$priority][$viewClass] = array($regex, true);
    }

    /**
    * Get a link to a view, the link is taken by the url mapping
    *
    * @param string|object $viewClass The View Class
    * @param mixed $params Parameters for a regex url
    * @param int $priority The priority of the mapped url
    * @param string $language If set than you can override the language, otherwise the current language is taken
    *   Only take effect when languageInUri is active
    * @return string
    */
    static function linkToView($viewClass, $params = null, $priority = 0, $language = null){
        if(is_object($viewClass)) $viewClass = get_class($viewClass);
        if(!isset(self::$mapping[$priority][$viewClass])) error("No URL mapped to '$viewClass:$priority'");
        $data = self::$mapping[$priority][$viewClass];
        if($data[1]){
            $count = 0;
            while(preg_match("~[^\\\\](\(.*?[^\\\\]\))~", $data[0], $match)){
                preg_match("~<(.*?)>~", $match[1], $id);
                if(!$id) $id[1] = $count;
                if(!isset($params[$id[1]])) error("linkToView '$viewClass' required parameter '{$id[1]}' not given");
                $data[0] = str_replace($match[1], $params[$id[1]], $data[0]);
                $count++;
            }
        }
        $data[0] = str_replace("\\", "", $data[0]);
        $uri = "";
        if(url()->uriPrefix) $uri .= "/".url()->uriPrefix;
        if(url()->languageInUri) $uri .= "/".($language !== null ? $language : CHOQ_LanguageManager::$language);
        $uri .= "/".ltrim($data[0], " /");
        if(!$uri) $uri = "/";
        return $uri;
    }

    /**
    * Load view for current uri
    */
    static function loadViewForCurrentUri(){
        $viewClass = null;
        $params = null;
        $prefix = url()->uriPrefix;
        if($prefix){
            if(mb_substr(url()->getUri(), 1, mb_strlen($prefix)) != $prefix) error("URL does not match with the defined prefix '$prefix'");
        }
        $uri = url()->getModifiedUri(false, false, false);
        krsort(self::$mapping);
        foreach(self::$mapping as $priority => $maps){
            foreach($maps as $class => $map){
                # no regex
                if(!$map[1]){
                    if($uri == $map[0]){
                        $viewClass = $class;
                        break 2;
                    }
                # regex
                }else{
                    if(preg_match("~^{$map[0]}$~u", $uri, $params)){
                        $viewClass = $class;
                        break 2;
                    }
                }
            }
        }
        if($viewClass){
            $view = new $viewClass;
            $view->parameters = $params;
            $view->priority = $priority;
            $view->onLoad();
            return;
        }
        error("No view for current URL found", 404);
    }

    /**
    * Fired when the view is loaded
    * Override it for your case
    */
    abstract public function onLoad();

    /**
    * Get value from the url-regex parameters
    *
    * @param string $key
    * @return string|null
    */
    public function getParam($key){
        return arrayValue($this->parameters, $key);
    }

}