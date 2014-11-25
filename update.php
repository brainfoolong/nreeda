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


/**
* Path fix for the path strings
*
* @param mixed $path
* @return string
*/
function pathFix($path){
    return str_replace(array(DIRECTORY_SEPARATOR,"/"), "/", $path);
}

if(!isset($_GET["rootfolder"], $_GET["updatefolder"])) die("Missing parameters");

$updateFolder = pathFix(urldecode($_GET["updatefolder"]));
$rootFolder = pathFix(urldecode($_GET["rootfolder"]));
$currentFolder = pathFix(__DIR__);

# security check if given folders are real nreeda folders
if(!preg_match("~".preg_quote($currentFolder)."~i", $updateFolder) || !preg_match("~".preg_quote($currentFolder)."~i", $rootFolder)) die("Missing parameters");
if(!is_dir($updateFolder) || !is_dir($rootFolder)) die("Missing parameters");

define("CHOQ", true);
define("DS", DIRECTORY_SEPARATOR);
include(__DIR__."/modules/CHOQ/class/FileManager.class.php");


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

echo json_encode(array("next" => "db", "count" => $count));
