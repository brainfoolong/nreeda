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
* The update
*/
class RDR_Admin_Update extends CHOQ_View{

    /**
    * Get valid hash for maintenance mode
    *
    * @return string
    */
    static function getValidHash(){
        return saltedHash("md5", __FILE__);
    }

    /**
    * Get GIT Json data
    *
    * @param mixed $url
    * @return array | false
    */
    static function getGitJSON($url){
        $data = RDR_Import::getURLContent($url);
        $data = json_decode($data, true);
        return $data;
    }

    /**
    * Load the View
    */
    public function onLoad(){
        if(req()->isAjax() && get("code") == self::getValidHash()){
            switch(get("action")){
                case "start":
                    if(RDR_Cron::isRunning()){
                        $data = array("message" => 'Cronjob is currently running... Please try again later...', "event" => "error",  "next" => "");
                    }else{
                        RDR_Maintenance::enableMaintenanceMode();
                        $data = array("message" => '<b>Maintenance Mode enabled</b><br/><br/>If you have any troubles with the update and you stuck in maintenance mode than run the following url to manually disable maintenance mode<br/><u>'.url()->getByAlias("root", l("RDR_Maintenance")).'?disable-maintenance='.RDR_Maintenance::getValidHash().'</u><br/><br/>You can also disable maintenance mode by removing the line <u>RDR::$maintenanceMode = true</u> from your _RDR.local.php file<br/><br/>Contacting GIT for available updates...', "event" => "success",  "next" => "check");
                    }
                break;
                case "check":
                    try{
                        # checking all files and directories for write access
                        $files = CHOQ_FileManager::getFiles(CHOQ_ROOT_DIRECTORY, true, true);
                        $count = 0;
                        $files[] = CHOQ_ROOT_DIRECTORY;
                        foreach($files as $file){
                            if(substr($file, 0, 1) == ".") continue;
                            if(!is_writable($file)){
                                $count++;
                            }
                        }
                        if($count) error("$count files/directories are not writeable by PHP. Make sure that you have set correct CHMOD");

                        $data = array("message" => 'Failed getting updates from GIT Hub', "event" => "error",  "next" => "");
                        $branches = self::getGitJSON("https://api.github.com/repos/brainfoolong/nreeda/branches");
                        if($branches){
                            $newest = NULL;
                            foreach($branches as $branch){
                                if($branch["name"] == "master") continue;
                                if(!$newest || version_compare($branch["name"], $newest, ">")){
                                    $newest = $branch["name"];
                                }
                            }
                            if(version_compare($newest, RDR_VERSION, ">") || 1){
                                $data = array("message" => "New Version '$newest' found... Fetching files...", "event" => "success",  "next" => "prepare", "params" => array("version" => $newest));
                            }else{
                                $data = array("message" => "You are already up2date. No update needed. Congratulations.", "event" => "success");
                            }
                        }
                    }catch(Exception $e){
                        $data = array("message" => 'Error: '.$e->getMessage(), "event" => "error",  "next" => "");
                    }
                break;
                case "prepare":
                    try{
                        # downloading zip file
                        $data = RDR_Import::getURLContent("https://github.com/brainfoolong/nreeda/archive/".get("version").".zip");
                        if(!$data) error("Could not fetch newest update file from GIT");

                        $tmpZip = CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp/update.zip";
                        file_put_contents($tmpZip, $data);

                        $updateDir = CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp/update";
                        if(!is_dir($updateDir)) mkdir($updateDir);

                        # removing all old files
                        $files = CHOQ_FileManager::getFiles($updateDir, true, true);
                        foreach($files as $file){
                            if(!is_dir($file)){
                                unlink($file);
                            }
                        }
                        foreach($files as $file){
                            if(is_dir($file)){
                                rmdir($file);
                            }
                        }

                        # extract zip file to tmp folder
                        $zip = new ZipArchive();
                        $zip->open($tmpZip);
                        $zip->extractTo($updateDir);
                        $zip->close();

                        $folder = $updateDir."/nreeda-".get("version");

                        $data = array(
                            "message" => 'Files successfully downloaded, start updating the files...',
                            "event" => "success",
                            "next" => "update",
                            "params" => array(
                                "updatefolder" => $folder,
                                "rootfolder" => CHOQ_ROOT_DIRECTORY,
                                "updateurl" => url()->getByAlias("base", "RDR/tmp/update/nreeda-".get("version")."/update.php")
                            )
                        );
                    }catch(Exception $e){
                        $data = array("message" => 'Error: '.$e->getMessage(), "event" => "error",  "next" => "");
                    }
                break;
                case "db":
                    try{
                        # updating database
                        $generator = CHOQ_DB_Generator::create(db());
                        $generator->addModule("RDR");
                        $generator->updateDatabase();

                        $data = array("message" => "Database Update successful. Cleaning up...", "event" => "success", "next" => "cleanup");
                    }catch(Exception $e){
                        $data = array("message" => 'Error: '.$e->getMessage(), "event" => "error",  "next" => "");
                    }
                break;
                case "cleanup":
                    try{
                        # deleting all update files
                        $updateDir = CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp/update";
                        if(is_dir($updateDir)) {
                            $files = CHOQ_FileManager::getFiles($updateDir, true, true);
                            foreach($files as $file){
                                if(!is_dir($file)){
                                    unlink($file);
                                }
                            }
                            foreach($files as $file){
                                if(is_dir($file)){
                                    rmdir($file);
                                }
                            }
                        }
                        if(is_dir($updateDir)) rmdir($updateDir);
                        $updateFile = CHOQ_ROOT_DIRECTORY."/update.php";
                        if(file_exists($updateFile)) unlink($updateFile);

                        $data = array("message" => "Cleanup done", "event" => "success");
                    }catch(Exception $e){
                        $data = array("message" => 'Error: '.$e->getMessage(), "event" => "error",  "next" => "");
                    }
                    RDR_Maintenance::disableMaintenanceMode();
                break;
            }
            echo json_encode($data);
            return;
        }
        needRole(RDR_User::ROLE_ADMIN, true);
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        ?>

        <?php
        headline(t("sidebar.26"));
        ?>
        <div class="indent">
            Use this one click updater to update nReeda to the newest stable version.<br/>
            Don't forget to backup your database and nreeda directory before doing this step, in the case of a problem with the update.<br/>

            <div class="spacer"></div>
            <?php if(class_exists("ZipArchive")){?>
                <input type="button" class="btn update-btn" value="Run update now"/>
            <?php }else{?>
                <span style="color:red">You must enable the 'zip' extension in your PHP config to use the auto updater</span>
            <?php }?>
            <div id="result" style="padding:10px;"></div>
        </div>
        <script type="text/javascript">
        (function(){
            var req = function(action, params){
                if(!params) params = {};
                params.action = action;
                params.code = '<?php echo self::getValidHash()?>';
                var url = "<?php echo url()->getModifiedUri(false)?>";
                if(action == "update") url = params.updateurl;
                $.getJSON(url, params, function(data){
                     $("#result").append('<div class="type '+data.event+'">'+data.message+'</div>');
                     if(data.next && data.next.length){
                         req(data.next, data.params);
                     }else{
                         $("#result").append('<div class="type '+data.event+'">Update finished. Maintenance Mode disabled</div>');
                     }
                });
            }
            $(".btn.update-btn").one("click", function(){
                this.value = 'Update in progress... Please wait...';
                req("start");
            });
        })();
        </script>
        <?php
    }
}