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
* Debug
*/
class CHOQ_Debug{

    /**
    * Get the full framework footprint and all debug information
    * Include all global variables, memory usage, included files, and many more
    */
    static function getFootprint(){
        $data = array();
        $data["parsetime"] = round((microtime(true) - CHOQ_STARTTIME) * 1000 , 3)." ms";
        $data["memory_get_peak_usage"] = round(memory_get_peak_usage(true) / 1024, 2). "kB";
        $data["memory_get_usage"] = round(memory_get_usage(true) / 1024, 2). "kB";
        $data["memory_limit"] = ini_get("memory_limit");
        if(CHOQ_DB::$queryLog){
            if(CHOQ_DB::$queries){
                $data["querylog"] = "";
                foreach(CHOQ_DB::$queries as $dbId => $queries){
                    foreach($queries as $query){
                        $data["querylog"] .= "[{$dbId}] $query\n";
                    }
                }
            }else{
                $data["querylog"] = "No Queries";
            }
        }
        $data["files"] = 0;
        $data["filesize-all"] = 0;
        $data["filelist"] = "\n";
        $files = get_included_files();
        foreach($files as $file){
            $data["files"]++;
            $data["filesize-all"] += filesize($file);
            $data["filelist"] .= $file."\n";
        }
        $data["filesize-all"] = round($data["filesize-all"] / 1024, 2)." kB";
        $data["isajax"] = req()->isAjax();
        $data["get"] = $_GET ? var_export($_GET, true) : "no-get";
        $data["session"] = isset($_SESSION) ? var_export($_SESSION, true) : "no-session";
        $data["cookie"] = $_COOKIE ? var_export($_COOKIE, true) : "no-cookie";
        $data["post"] = $_POST ? var_export($_POST, true) : "no-post";
        $data["server"] = var_export($_SERVER, true);

        echo '<div style="text-shadow:none; border-left:5px solid #ffc376; margin:5px; padding:5px; font-size:12px; line-height:15px; font-family:courier; background:white; color:black; text-align:left;">'."\n";
        foreach($data as $key => $value){
            echo '    <div style="border-bottom:1px solid #dddddd;">'."\n";
            echo "        <b>$key</b>: ".nl2br(str_replace(" ", "&nbsp;", rtrim($value)))."\n";
            echo '    </div>'."\n\n";
        }
        echo '</div>';
    }
}