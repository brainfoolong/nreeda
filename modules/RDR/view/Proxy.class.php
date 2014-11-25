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
* A proxy for fetching images
*/
class RDR_Proxy extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        if(!needRole()) return;

        if(get("type") == "image"){
            $url = urldecode(get("url"));
            if(substr($url, 0, 2) == "//") $url = "http:".$url;
            $exp = explode(".", preg_replace("~\?.*~i", "", $url));
            $extension = end($exp);
            if(count($exp) <= 1 || !$extension || strlen($extension) > 4 || preg_match("~[^a-z]~i", $extension)) $extension = "jpg";
            $slug = md5($url);
            $tmpPath = CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp/filecontents.$slug.$extension";
            if(!file_exists($tmpPath)){
                $data = @file_get_contents($url);
                file_put_contents($tmpPath, $data !== false ? $data : "");
            }
            header("content-type: image/$extension");
            header("content-length: ".filesize($tmpPath));
            header("Last-Modified: ".date("r", filemtime($tmpPath)));
            header("Expires: " . date("r", filemtime($tmpPath) + (86400 * 7)));
            header("Cache-Control: public, max-age= " . (86400 * 7));
            readfile($tmpPath);
        }
    }
}