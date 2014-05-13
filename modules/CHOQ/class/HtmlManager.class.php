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
* HTML Manager
*/
class CHOQ_HtmlManager{

     /**
     * Instance
     *
     * @var self
     */
    static $instance;

    /**
    * The page title
    *
    * @var string
    */
    public $pageTitle = "Choqled";

    /**
    * The file groups
    *
    * @var array[]
    */
    private $fileGroups = array();

    /**
    * The head strings
    *
    * @var string[]
    */
    private $headStrings = array();

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
    * Get the opening <html> tag, including the current language if set
    */
    public function getOpeningHTMLTag(){
        echo '<html '.(CHOQ_LanguageManager::$language ? 'lang="'.CHOQ_LanguageManager::$language.'"' : "").'>';
    }

    /**
    * Get the meta content type tag with the current encoding
    */
    public function getMetaContentTypeTag(){
        echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHOQ::$encoding.'"/>';
    }

    /**
    * Get the title tag with the current title
    */
    public function getTitleTag(){
        echo '<title>'.s($this->pageTitle).'</title>';
    }

    /**
    * Get script tags for concated JS files
    */
    public function getScriptTags(){
        return $this->getFileTags("js");
    }

    /**
    * Get link css tags for concated CSS files
    */
    public function getLinkCSSTags(){
        return $this->getFileTags("css");
    }

    /**
    * Get specific tags (<link>, <script>) for the set of files
    *
    * @param mixed $type "css" or "js"
    */
    public function getFileTags($type){
        foreach($this->fileGroups as $group => $data){
            if($data[1] != $type) continue;
            $groupContent = "";
            $filename = $group.".".$data[1];
            $filePath = $data[0].DS.$filename;
            $newestTimestamp = NULL;
            foreach($data[3] as $file){
                if(!file_exists($file)) error("File '$file' doesn't exist");
                $newestTimestamp = max(array($newestTimestamp, filemtime($file)));
            }
            if(!file_exists($filePath) || filemtime($filePath) < $newestTimestamp){
                foreach($data[3] as $file){
                    ob_start();
                    include($file);
                    echo "\n";
                    $groupContent .= ob_get_contents();
                    ob_end_clean();
                }
                $timestamp = time();
                file_put_contents($data[0].DS.$filename, $groupContent);
            }else{
                $timestamp = $newestTimestamp;
            }
            $fileUrl = url()->getByAlias($data[2]).'/'.$filename.'?t='.$timestamp;
            if($data[1] == "css"){
                echo '<link rel="stylesheet" href="'.$fileUrl.'" type="text/css"/>';
            }elseif($data[1] == "js"){
                echo '<script type="text/javascript" src="'.$fileUrl.'"></script>';
            }
        }
    }

    /**
    * Print the whole <head> tag, without enclosed <head></head> tags ;)
    */
    public function getHeadTag(){
        $this->getMetaContentTypeTag();
        $this->getTitleTag();
        echo arrayValue($this->headStrings, "afterTitle");
        echo $this->getLinkCSSTags();
        echo $this->getScriptTags();
        echo arrayValue($this->headStrings, "afterFiles");
    }

    /**
    * Add a group of files to the <head> element
    * Used in $this->getHeadTag()
    *
    * @param string $group The group name
    * @param string $type css or js
    * @param string $directory The directory to store the concated file in
    * @param string $alias The urlAlias to the directory to link to the concated file
    * @param string[] $files Array of files
    */
    public function addFileGroupToHead($group, $type, $directory, $urlAlias, array $files){
        if(!is_dir($directory) || !is_writable($directory)) error("Directory '$directory' is not writable");
        $this->fileGroups[$group."-".$type] = array($directory, $type, $urlAlias, $files);
    }

    /**
    * Add any string to the given position in the <head> element
    *
    * @param string $position afterTitle, afterFiles
    * @param string $string Any string
    */
    public function addStringToHead($position, $string){
        if(!isset($this->headStrings[$position])) $this->headStrings[$position] = "";
        $this->headStrings[$position] .= $string;
    }
}