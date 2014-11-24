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

/**
* nReeda File Updater
*/

if(!isset($_GET["rootfolder"], $_GET["updatefolder"]) || !is_dir($_GET["rootfolder"]) || !is_dir($_GET["updatefolder"])) die("Missing parameters");

/**
* Path fix for the path strings
*
* @param mixed $path
* @return string
*/
function pathFix($path){
    return str_replace(array(DS,"/"), "/", $path);
}

define("CHOQ", true);
define("DS", DIRECTORY_SEPARATOR);
include(__DIR__."/modules/CHOQ/class/FileManager.class.php");

$updateFolder = pathFix($_GET["updatefolder"]);
$rootFolder = pathFix($_GET["rootfolder"]);
$files = CHOQ_FileManager::getFiles($updateFolder, true, true);

# updating all files
$count = 0;
foreach($files as $file){
    $file = pathFix($file);
    $srcFile = $file;
    $targetFile = str_replace($updateFolder, $rootFolder, $file);
    if(is_dir($srcFile)){
        if(!is_dir($targetFile)){
            mkdir($targetFile, 0777, true);
            $count++;
        }
        continue;
    }
    if(!file_exists($targetFile) || md5_file($targetFile) != md5_file($srcFile)){
        rename($srcFile, $targetFile);
        $count++;
    }
}

echo json_encode(array("message" => "Updated $count files/directories... Updating database...", "event" => "success", "next" => "db"));
