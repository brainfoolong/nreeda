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
* Request Manager
*/
class CHOQ_RequestManager{

    /**
     * Instance
     *
     * @var self
     */
    static $instance;

    /**
    * Cookie Parameters for setcookie and session cookies
    *
    * @var array
    */
    static $cookieParams = array(
        "path" => "/",
        "domain" => NULL,
        "secure" => false,
        "httponly" => true
    );

    /**
    * Received headers
    *
    * @var array
    */
    private $headers;

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
    * Set a Cookie Parameter
    *
    * @param string $key Available Keys: path, domain, secure, httponly
    * @param mixed $value
    */
    static function setCookieParameters($key, $value){
        if(!isset(self::$cookieParams[$key])) error("Cookie Parameter '$key' is not available");
        self::$cookieParams[$key] = $value;
        session_set_cookie_params(0, self::$cookieParams["path"], self::$cookieParams["domain"], self::$cookieParams["secure"], self::$cookieParams["httponly"]);
    }

    /**
    * Get users ip adress
    *
    * @return string
    */
    public function getIp(){
        return arrayValue($_SERVER, "REMOTE_ADDR");
    }

    /**
    * Get server ip adress
    *
    * @return string
    */
    public function getServerIp(){
        return arrayValue($_SERVER, "SERVER_ADDR");
    }

    /**
    * Get host
    *
    * @return string
    */
    public function getHost(){
        return arrayValue($_SERVER, "HTTP_HOST");
    }

    /**
    * Get referrer
    *
    * @return string
    */
    public function getReferrer(){
        return arrayValue($_SERVER, "HTTP_REFERER");
    }

    /**
    * Get current request method, lowercase
    *
    * @return string
    */
    public function getRequestMethod(){
        return isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';
    }

    /**
    * Get received headers from $_SERVER with prefix HTTP_
    *
    * @return string[]
    */
    public function getHeaders(){
        if(!$this->headers){
            foreach($_SERVER as $key => $value){
                if(substr(strtoupper($key), 0, 5) == "HTTP_"){
                    $this->headers[strtolower(substr($key, 5))] = strtolower($value);
                }
            }
        }
        return $this->headers;
    }

    /**
    * Check if current request is a ajax request
    *
    * @return bool
    */
    public function isAjax(){
        $headers = $this->getHeaders();
        if(!$headers) return false;
        if(array_search(strtolower("XMLHttpRequest"), $headers) !== false){
            return true;
        }
        return false;
    }

    /**
    * return true if this request is a post request
    *
    * @return bool
    */
    public function isPost(){
        return req()->getRequestMethod() == "post";
    }

    /**
    * return true if this request is a get request
    *
    * @return bool
    */
    public function isGet(){
        return req()->getRequestMethod() == "get";
    }

    /**
    * Return true if it is a https request
    *
    * @return bool
    */
    public function isHttps(){
        return arrayValue($_SERVER, "SERVER_PORT") == 443;
    }
}