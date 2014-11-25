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
* File Manager
*/
class CHOQ_FileManager{

    /**
    * Get array of files and directories
    *
    * @param mixed $directory
    * @param mixed $recursive
    * @param bool $flat
    *   If true than the returned array is flat, all files are in the same hierarchy, easy for iterate over all files
    *   If false than the returned array contains multiple dimensions, each dimension have a :childs member that contains a array of all child files
    * @return mixed[]
    */
    static function getFiles($directory, $recursive = false, $flat = false){
        $files = array();
        if(!is_dir($directory)) error("'$directory' is not a directory");
        $tmp = scandir($directory);
        foreach($tmp as $file){
            $path = $directory.DS.$file;
            if($file == "." || $file == "..") continue;
            if(is_dir($path)){
                if($flat){
                    $files = array_merge($files, self::getFiles($path, $recursive, true));
                    $files[] = $path;
                }else{
                    $files[$file]["files"] = self::getFiles($path, $recursive, false);
                    $files[$file]["path"] = $path;
                }
            }else{
                $files[] = $path;
            }
        }
        return $files;
    }
}