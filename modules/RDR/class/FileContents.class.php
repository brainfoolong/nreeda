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
* Get file contents of a url or a local file
*/
class RDR_FileContents{

    /**
    * Max cache time for a url
    */
    const CACHETIME = 300;

    /**
    * Get contents from a url or a local file - Cache urls for given cachetime
    *
    * @param mixed $path The URL or file path
    * @return string | false
    */
    static function get($path){
        # is local file
        if(!preg_match("~^(http|https)~i", $path)){
            if(file_exists($path)) return file_get_contents($path);
            return false;
        }
        # is url
        $dir = CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp";
        $file = "{$dir}/filecontents.".md5($path);
        if(!file_exists($file) || filemtime($file) < time() - self::CACHETIME){
            $data = self::loadUrl($path);
            if($data === false){
                RDR_Event::log(RDR_Event::TYPE_FEED_URLERROR, array("text" => $path));
                return;
            }
             # writing to cache
            file_put_contents($file, $data);
        }else{
            # return cached file
            return file_get_contents($file);
        }
        return $data;
    }

    /**
    * Cleanup all tmp files generated by this class
    */
    static function cleanupTmpFiles(){
        $files = CHOQ_FileManager::getFiles(CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp");
        foreach($files as $file){
            if(preg_match("~filecontents\.|import\.~i", basename($file))){
                if(filemtime($file) < time() - self::CACHETIME) unlink($file);
            }
        }
    }

    /**
    * Get URL contents with correct browser headers
    *
    * @param mixed $url
    * @return string | false
    */
    static private function loadUrl($url){
        $context = stream_context_create(array(
            'http' => array(
                'method' => "GET",
                'header' =>
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
                    "Accept-Language: en-US,en;q=0.8\r\n".
                    "Keep-Alive: timeout=3, max=10\r\n",
                    "Connection: keep-alive",
                'user_agent' => "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.66 Safari/535.11",
                "ignore_errors" => true,
                "timeout" => 3
            )
        ));
        return @file_get_contents($url, false, $context);
    }
}