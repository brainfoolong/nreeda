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
* RDR
*/
class RDR extends CHOQ_Module{

    /**
    * Is RDR installed correctly or not
    *
    * @var bool
    */
    static $isInstalled = false;

    /**
    * Is RDR in maintenance mode
    *
    * @var bool
    */
    static $maintenanceMode = false;

    /**
    * Fired when initialise the module
    */
    public function onInit(){

        html()->pageTitle = "nReeda - Web-based Open Source RSS/XML/Atom Feed Reader";
        define("RDR_VERSION", "1.1.2");

        $devFile = __DIR__."/_RDR.dev.php";
        $localFile = __DIR__."/_RDR.local.php";
        if(file_exists($localFile)) self::$isInstalled = true;

        session_name("RDR");

        CHOQ::setMode(CHOQ::MODE_PROD);
        CHOQ_View::mapViewToUrlRegex("RDR_Home", "/index\.php/.*", -1);
        CHOQ_View::mapViewToUrl("RDR_Home", "/index.php");
        CHOQ_View::mapViewToUrl("RDR_Home", "/", 1);
        if(self::$isInstalled){
            CHOQ_View::mapViewToUrl("RDR_API", "/index.php/api");
            CHOQ_View::mapViewToUrl("RDR_Logout", "/index.php/logout");
            CHOQ_View::mapViewToUrlRegex("RDR_Feeds", "/index\.php/feeds-(?<param>[0-9a-z-]+)");
            CHOQ_View::mapViewToUrl("RDR_Proxy", "/index.php/proxy");
            CHOQ_View::mapViewToUrl("RDR_Ajax", "/index.php/ajax");
            CHOQ_View::mapViewToUrl("RDR_Archive", "/index.php/archive");
            CHOQ_View::mapViewToUrl("RDR_Admin_System", "/index.php/admin-system");
            CHOQ_View::mapViewToUrl("RDR_Admin_Update", "/index.php/admin-update");
            CHOQ_View::mapViewToUrl("RDR_Admin_Settings", "/index.php/admin-settings");
            CHOQ_View::mapViewToUrl("RDR_Admin_User", "/index.php/admin-user");
            CHOQ_View::mapViewToUrlRegex("RDR_Cron", "/index.php/cron-(?<param>[0-9a-z-]+)");
            CHOQ_View::mapViewToUrl("RDR_Organize", "/index.php/organize");
            CHOQ_View::mapViewToUrl("RDR_Settings", "/index.php/settings");
            CHOQ_View::mapViewToUrl("RDR_Login", "/index.php/login");
            CHOQ_View::mapViewToUrl("RDR_RSS", "/index.php/rss");
            CHOQ_View::mapViewToUrl("RDR_BrowserScript", "/index.php/browser-script.js");
            CHOQ_View::mapViewToUrl("RDR_Maintenance", "/index.php/maintenance");
        }else{
            CHOQ_View::mapViewToUrl("RDR_Install", "/index.php/install");
        }

        $baseUrl = dirname(arrayValue($_SERVER, "PHP_SELF"));
        if(substr($baseUrl, -9) == "index.php") $baseUrl = dirname($baseUrl);
        url()->setUriPrefix($baseUrl);
        url()->addAlias("root", (req()->isHttps() ? "https" : "http")."://".req()->getHost());
        url()->addAlias("base", $baseUrl);


        if(self::$isInstalled) include($localFile);
        if(file_exists($devFile)) include($devFile);

        CHOQ_LanguageManager::setFallback("en");
        CHOQ_LanguageManager::addLanguage("en");
        CHOQ_LanguageManager::addLanguage("de");
        if(!CHOQ_LanguageManager::setLanguageByBrowserSettings()){
            CHOQ_LanguageManager::setLanguage("en");
        }

        CHOQ_Module::get("Form");

        url()->addAlias("public", url()->getByAlias("base", "modules/RDR/public"));
        url()->addAlias("static", url()->getByAlias("base", "modules/RDR/public/static"));
    }
}

/**
* Check if RDR running in normal mode
*
* @return bool
*/
function inNormalMode(){
    if(!RDR::$isInstalled || RDR::$maintenanceMode) return false;
    return true;
}

/**
* Get the user instance
*
* @return RDR_User | NULL
*/
function user(){
    if(!inNormalMode()) return;
    if(RDR_User::$user !== NULL) return RDR_User::$user;
    if(cookie("user-id") && cookie("user-id-salted") && cookie("user-id-salted") == saltedHash("sha256", cookie("user-id"))){
        $user = RDR_User::getById(cookie("user.id"));
        RDR_User::$user = $user;
        return $user;
    }
    if(session("user.id")){
        $user = RDR_User::getById(session("user.id"));
        RDR_User::$user = $user;
        return $user;
    }
}

/**
* Get headline HTML
*
* @param mixed $title
*/
function headline($title){
    echo '<h1><span class="before"></span>'.s($title).'</h1>';
}

/**
* Need a specific role to to such things?
* Also do initial checks for installed and maintenance mode
*
* @param mixed $role
* @param mixed $redirect
* @return bool
*/
function needRole($role = NULL, $redirect = false){
    if(RDR::$maintenanceMode){
        if($redirect) redirect(l("RDR_Maintenance"), 302);
        return false;
    }elseif(!RDR::$isInstalled){
        if($redirect) redirect(l("RDR_Install"), 302);
        return false;
    }
    $access = true;
    if(!user()) $access = false;
    if($access && $role !== NULL && $role != user()->role) $access = false;
    if(!$access && $redirect) redirect(l(!user() ? "RDR_Login" : "RDR_Home"), 302);
    return $access;
}

/**
* Cut a string on a specific length
*
* @param mixed $string
* @param mixed $length
* @param mixed $append
* @return string
*/
function cut($string, $length, $append = "..."){
    $l = mb_strlen($string);
    if($l > $length){
        $string = mb_substr($string, 0, $length).$append;
    }
    return $string;
}

/**
* Detect if device is mobile
*
* @return bool
*/
function isMobile(){
    $useragent = $_SERVER['HTTP_USER_AGENT'];
    return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));
}