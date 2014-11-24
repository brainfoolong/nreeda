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
* The Choqled Core
*/
class CHOQ extends CHOQ_Module{

    const MODE_PROD = 1;
    const MODE_DEV = 2;

    /**
    * The encoding
    *
    * @var string
    */
    static $encoding = "UTF-8";

    /**
    * The running mode
    *
    * @var int
    */
    static $mode;

    /**
    * Set the mode
    *
    * @param int $mode
    */
    static function setMode($mode){
        self::$mode = $mode;
    }

    /**
    * Is the mode the given
    *
    * @param int $mode
    * @return bool
    */
    static function isMode($mode){
        return self::$mode === $mode;
    }

    /**
    * Fired on instantiate the module
    * At this point nothing from the module is loaded
    */
    public function onInit(){

        mb_detect_order(self::$encoding.", UTF-8, UTF-7, ISO-8859-1, ASCII, EUC-JP, SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP, Windows-1251, Windows-1252");
        mb_internal_encoding(self::$encoding);
        mb_http_input(self::$encoding);
        mb_http_output(self::$encoding);
        mb_language("uni");

        header("Content-Type: text/html; charset=".self::$encoding);

        return $this;
    }
}

/**
* Html encode a string
*
* @param mixed $string
* @param bool $nl2br Do nl2br() on the resulting string
* @return string
*/
function s($string, $nl2br = false){
    $string = convertEncoding($string);
    $string = htmlspecialchars($string, ENT_QUOTES, CHOQ::$encoding);
    if($nl2br) $string = nl2br($string);
    return $string;
}

/**
* Creates a slug out of a string.
* Replaces everything but letters and numbers with dashes.
* @see http://en.wikipedia.org/wiki/Slug_(typesetting)
* @param $string The string to slugify.
* @param bool $trimSpaces
* @return string A search-engine friendly string that is safe
*   to be used in URLs.
*
*/
function slugify($string, $trimSpaces = true) {
    $string = convertEncoding($string);
    $string = str_replace(array("Ö", "Ü", "Ä", "ö", "ü", "ä", "ß"), array("Oe", "Ue", "Ae", "oe", "ue", "ae", "ss"), $string);
    if(!$trimSpaces){
        preg_match_all('/[\pL\pN ]+/u', $string, $stringParts);
    }else{
        preg_match_all('/[\pL\pN]+/u', $string, $stringParts);
    }
    return implode('-', $stringParts[0]);
}

/**
* Throw Exception, if a Exception class for the current module exist, than throw it instead of CHOQ_Exception
*
* @param mixed $message
* $param int $code The error HTTP code
* @throws Exception
*/
function error($message, $code = 500){
    $class = CHOQ_ACTIVE_MODULE."_Exception";
    if(class_exists($class)) throw new $class($message, $code);
    throw new CHOQ_Exception($message, $code);
}

/**
* Get a array with the property values from another array
*
* @param array $array
* @param string $property If NULL and the value is a array the first value returned
* @return array
*/
function arrayMapProperty(&$array, $property){
    if(!$array || !is_array($array)) return array();
    $return = array();
    foreach($array as $key => $value){
        if(is_object($value)){
            if(method_exists($value, $property)){
                $return[$key] = $value->{$property}();
            }else{
                $return[$key] = $value->{$property};
            }
        }elseif(is_array($value)){
            if($property === NULL){
                $return[$key] = array_shift($value);
            }else{
                $return[$key] = $value[$property];
            }
        }
    }
    return $return;
}

/**
* Sort a array by the given property and sort direction
*
* @param array $array
* @param string $property
* @param int $sort
*/
function arraySortProperty(&$array, $property, $sort = SORT_ASC){
    if(!$array || !is_array($array)) return array();
    $map = arrayMapProperty($array, $property);
    array_multisort($map, $sort, $array);
}

/**
* Call from a array of objects a given method with optional parameters
* Return a array of return values for each object
*
* @param array $array The array of objects
* @param string $method The method to call
* @param mixed $parameters Parameters to pass to the method
* @return array
*/
function arrayCallMethod(&$array, $method, $parameters = NULL){
    if(!$array || !is_array($array)) return array();
    $return = array();
    foreach($array as $value){
        if(is_object($value) && method_exists($value, $method)){
            $return[] = $value->{$method}($parameters);
        }
    }
    return $return;
}

/**
* Reindex an array starting from index 0
*
* @param mixed $array
*/
function arrayReIndex(array &$array){
    $array = array_values($array);
}

/**
* Return a value from a array for the given key
* The key can be in string format name[foo][bar]
*
* @param array $arr
* @param string $key
* @param mixed $default The value the returned by default if a key is not set
* @return mixed
*/
function arrayValue(&$arr, $key, $default = NULL){
    if(!$arr) return $default;
    if(isset($arr[$key])){
        return $arr[$key];
    }
    $tmp = $arr;
    $levels = explode("[", $key);
    $c = count($levels);
    foreach($levels as $k => $level){
        $level = trim($level, "[]");
        if(!isset($tmp[$level])){
            return $default;
        }
        $tmp = &$tmp[$level];
    }
    return $tmp;
}

/**
* Convert the encoding of the given string into the CHOQ charset - doesn't matter which encoding is given
* Can also be a array
*
* @param string|array $string
* @return string|array
*/
function convertEncoding($param){
    if(!$param) return $param;
    if(is_array($param)){
        foreach($param as $key => $value) $param[$key] = convertEncoding($value);
    }else{
        $encoding = strtoupper(mb_detect_encoding($param));
        if($encoding != strtoupper(CHOQ::$encoding)) $param = mb_convert_encoding($param, CHOQ::$encoding, $encoding);
    }
    return $param;
}

/**
* Get a $_GET value
*
* @param string $key Can be any valid key that also is valid in arrayValue()
* @return mixed
*/
function get($key){
    return convertEncoding(arrayValue($_GET, $key));
}

/**
* Get a $_POST value
*
* @param string $key Can be any valid key that also is valid in arrayValue()
* @return mixed
*/
function post($key){
    return arrayValue($_POST, $key);
}

/**
* Get/Set a $_COOKIE value
*
* @param string $key
* @param string $value The value to set
* @param int $expire A unix timestamp how long the cookie is valid, 0 for session like behaviour
* @return mixed
*/
function cookie($key, $value = NULL, $expire = 0){
    $key = slugify($key);
    if($value === NULL) return convertEncoding(arrayValue($_COOKIE, $key));
    $value = (string)$value;
    $_COOKIE[$key] = $value;
    setcookie(
        $key, $value, $expire,
        CHOQ_RequestManager::$cookieParams["path"],
        CHOQ_RequestManager::$cookieParams["domain"],
        CHOQ_RequestManager::$cookieParams["secure"],
        CHOQ_RequestManager::$cookieParams["httponly"]
    );
}

/**
* Get/Set a $_SESSION value
* Session is hi-jack protected and protected against corrupt session ids
*
* @param string $key
* @param string $value The value to set
* @return mixed
*/
function session($key, $value = NULL){
    if(defined("CHOQ_SESSIONID_CORRUPT") && CHOQ_SESSIONID_CORRUPT) return;
    if(session_id() === "") {
        # check for session id to be correct
        # if not skip activating the session
        $sessionName = session_name();
        $sessionId = isset($_COOKIE[$sessionName]) ? $_COOKIE[$sessionName] : NULL;
        if($sessionId !== NULL && (strlen($sessionId) < 22 || strlen($sessionId) > 40 || preg_match("~[^a-zA-Z0-9,\-]~i", $sessionId))){
            define("CHOQ_SESSIONID_CORRUPT", true);
            return;
        }
        session_start();
        $currentUid = md5(
            req()->getIp()
        );
        $savedUid = arrayValue($_SESSION, "__choquid__");
        if($savedUid && $savedUid != $currentUid) $_SESSION = array();
        $_SESSION["__choquid__"] = $currentUid;
    }
    if($value === NULL) return arrayValue($_SESSION, $key);
    $_SESSION[$key] = $value;
}

/**
* Get the instance of the CHOQ_RequestManager
*
* @return CHOQ_RequestManager
*/
function req(){
    return CHOQ_RequestManager::getInstance();
}

/**
* Get the instance of the CHOQ_UrlManager
*
* @return CHOQ_UrlManager
*/
function url(){
    return CHOQ_UrlManager::getInstance();
}


/**
* Do a pretty HTML styled dump instead of the unstyled var_dump
*
* @param mixed $var,...
*/
function dump(){
    echo '<pre style="overflow:auto;">';
    $params = func_get_args();
    foreach($params as $param){
        echo '<div style="text-shadow:none; border-left:5px solid #757a7e; margin:5px; padding:5px; font-size:12px; line-height:15px; font-family:courier; background:white; color:black; text-align:left;">';
        ob_start();
        var_dump($param);
        $out = ob_get_contents();
        ob_end_clean();
        echo s($out);
        echo '</div>';
    }
    echo '</pre>';
}

/**
* Inject a View and call only CHOQ_View::onLoad
*
* @param string $viewClass
* @param mixed $parameters The parameters to pass to the view
* @param bool $return If set to true than the output will be returned instead of direct write to output buffer
* @return string|void
*/
function view($viewClass, $parameters = NULL, $return = false){
    if($parameters !== NULL && !is_array($parameters)) error("view() \$parameters is not from type array");
    $instance = new $viewClass;
    $instance->parameters = $parameters;
    if($return) ob_start();
    $instance->onLoad();
    if($return){
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }
}

/**
* Get the instance of the CHOQ_HtmlManager
*
* @return CHOQ_HtmlManager
*/
function html(){
    return CHOQ_HtmlManager::getInstance();
}

/**
* Get/Set values
*
* @param mixed $key
* @param mixed $value
* @return mixed
*/
function v($key, $value = NULL){
    if($value === NULL) return CHOQ_Var::get($key);
    CHOQ_Var::add($key, $value);
}

/**
* Redirect and die() after setting the header
*
* @param mixed $url
* @param mixed $code Default is moved temporarily
*/
function redirect($url, $code = 302){
    header('HTTP/1.1 '.$code.' See you later, alligator');
    header("location: $url");
    die();
}

/**
* Get translation for a specified key
*
* @param string $key
* @param string|NULL $lang If not specified the current language will be taken
* @param bool $returnKeyIfNot If false than NULL will be returned
* @return string|NULL
*/
function t($key, $lang = NULL, $returnKeyIfNot = true){
    return CHOQ_LanguageManager::getTranslation($key, $lang, $returnKeyIfNot);
}


/**
* Get a link to a view, the link is taken by the url mapping
*
* @param string|object $viewClass The View Class
* @param mixed $params Parameters for a regex url
* @param int $priority The priority of the mapped url
* @param string $language If set than you can override the language, otherwise the current language is taken
* @return string
*/
function l($viewClass, $params = NULL, $priority = 0, $language = NULL){
    return CHOQ_View::linkToView($viewClass, $params, $priority, $language);
}

/**
* Generate a salted hash
* Same as hash() function but add the defined salt in v('hash.salt')
* See hash_algos() for all available algos
* crc32b is the fastest for small strings (length < 100)
*
* @param string $algo md2, md4, md5, sha256,...
* @param string $data The data to hash
* @return string
*/
function saltedHash($algo, $data){
    if(!v("hash.salt")) error("No salt added for saltedHash()");
    return hash($algo, v("hash.salt").$data, false);
}

/**
* Get a CHOQ_DateTime
*
* @param CHOQ_DateTime|string|int $time
* @return CHOQ_DateTime
*/
function dt($time){
    return new CHOQ_DateTime($time);
}

/**
* Get a cache instance
*
* @param mixed $id The instance id
* @return CHOQ_Cache
*/
function cache($id = NULL){
    return CHOQ_Cache::getInstance($id);
}

/**
* Get a DB instance
*
* @param mixed $id The instance id
* @return CHOQ_DB
*/
function db($id = NULL){
    return CHOQ_DB::get($id);
}

/**
* Compare any kind of parameters that you pass to this function
* This first parameter is the value to check
* All others must match to this first parameters
* CHOQ_DB_Object transformed to its ids and
* to speed up comparsion and prevent nesting dependencies errors
* Comparsion is done in strict mode
*
* @param mixed $var,...
* @return bool
*/
function compare(){
    $args = func_get_args();
    if(count($args) < 2) error("Two or more parameters required for compare()");
    foreach($args as $key => $value){
        if(is_object($value)){
            $args[$key] = (string)$value;
        }
    }
    if(count($args) == 2) return $args[0] === $args[1];
    $firstArg = array_shift($args);
    foreach($args as $arg){
        if($arg !== $firstArg){
            return false;
        }
    }
    return true;
}