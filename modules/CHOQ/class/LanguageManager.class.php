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
* Language Manager
*/
class CHOQ_LanguageManager{

    /**
    * Array of all registered languages
    *
    * @var string[]
    */
    static $languages = array();

    /**
    * Active language
    *
    * @var string
    */
    static $language;

    /**
    * Set a language fallback for translations
    * If you have for example a DE and EN translation but in DE a translation key doesn't exist, than automatically the EN translation will be taken
    * Set to FALSE for no fallback
    *
    * @var mixed
    */
    static $languageFallback;

    /**
    * Stored translations
    *
    * @var array[]
    */
    static $translations = array();

    /**
    * Add a available language to the system
    *
    * @param mixed $language Any language, common usage 2 char codes (EN, DE), but also sublanguage is possible (EN-US, DE-AT)
    */
    static function addLanguage($language){
        self::$languages[strtolower($language)] = $language;
    }

    /**
    * Set current language, default is "en"
    * Also set the content-language header if supposed to
    *
    * @param string $language
    * @param bool $setHeader
    */
    static function setLanguage($language, $setHeader = true){
        if(!isset(self::$languages[$language])) error("Language '$language' is not available for setLanguage");
        self::$language = $language;
        if($setHeader) header("Content-Language: $language");
    }

    /**
    * Set a language fallback for translations
    *
    * @param string $language null or the langcode for the fallback
    */
    static function setFallback($language){
        self::$languageFallback = $language;
    }

    /**
    * Set language by users browser settings
    *
    * @return string|null The determined Language
    */
    static function setLanguageByBrowserSettings(){
        $userLanguage = arrayValue($_SERVER, "HTTP_ACCEPT_LANGUAGE");
        if($userLanguage){
            $userLanguage = preg_replace("~[^a-z]~i", " ", strtolower($userLanguage));
            $userLanguages = explode(" ", $userLanguage);
            foreach($userLanguages as $lang){
                if(!$lang) continue;
                if(isset(self::$languages[$lang])){
                    self::$language = $lang;
                    return $lang;
                }
            }
        }
    }

    /**
    * Set language from current uri
    * This only happens if url()->languageInUri is true
    *
    * @return string|null The determined Language
    */
    static function setLanguageByUri(){
        if(url()->languageInUri){
            $uri = url()->getModifiedUri(false, false, true);
            foreach(self::$languages as $lang){
                if(preg_match("~^/$lang(/|$)~", $uri)){
                    self::$language = $lang;
                    return $lang;
                }
            }
        }else{
            error("Could not ".__CLASS__."::setLanguageByUri because languageInUri is not set in UrlManager");
        }
    }

    /**
    * Get translation for a specified key
    * Of no translation was found try the fallback
    *
    * @param string $key
    * @param string|null $lang If not specified the current language will be taken
    * @param bool $returnKeyIfNot If false than null will be returned
    * @return string|null
    */
    static function getTranslation($key, $lang = null, $returnKeyIfNot = true){
        if($lang === null) {
            if(!self::$language) error("No language activated - use ".__CLASS__."::setLanguage");
            $lang = self::$language;
        }
        if(!isset(self::$translations[$lang])){
            self::$translations[$lang] = array();
            foreach(CHOQ_Module::$instances as $module){
                $path = $module->directory.DS."lang".DS.$lang.".php";
                $translations = null;
                if(file_exists($path)){
                    include($path);
                    if($translations) foreach($translations as $k => $value) self::$translations[$lang][$k] = $value;
                }
            }
        }
        if(isset(self::$translations[$lang][$key])) return self::$translations[$lang][$key];
        if(self::$languageFallback && $lang != self::$languageFallback) return self::getTranslation($key, self::$languageFallback, $returnKeyIfNot);
        if($returnKeyIfNot) return $key;
    }
}