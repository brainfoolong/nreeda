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
        $data = RDR_FileContents::get($url);
        $data = json_decode($data, true);
        return $data;
    }

    /**
    * Load the View
    */
    public function onLoad(){
        if(req()->isAjax() && get("code") == self::getValidHash()){
            $data = NULL;
            switch(get("action")){
                case "update-check":
                    # fetching branches from GIT
                    $data = array("error" => t("update.4"));
                    $branches = self::getGitJSON("https://api.github.com/repos/brainfoolong/nreeda/branches");
                    if($branches){
                        $newest = NULL;
                        foreach($branches as $branch){
                            if($branch["name"] == "master") continue;
                            if(!$newest || version_compare($branch["name"], $newest, ">")){
                                $newest = $branch["name"];
                            }
                        }
                        $data = array(
                            "version" => $newest,
                            "update" => version_compare($newest, RDR_VERSION, ">")
                        );
                        RDR_Setting::set("latestversion", $newest);
                    }
                break;
                case "start":
                    if(RDR_Cron::isRunning()){
                        $data = array("message" => t("update.1"), "event" => "error");
                    }else{
                        RDR_Maintenance::enableMaintenanceMode();
                        $data = array(
                            "message" => sprintf(
                                nl2br(t("update.2"), true),
                                url()->getByAlias("root", l("RDR_Maintenance")).'?disable-maintenance='.RDR_Maintenance::getValidHash(),
                                'RDR::$maintenanceMode = true'
                            ),
                            "event" => "success",
                            "next" => "check"
                        );
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
                        if($count) error(sprintf(t("update.3"), $count));

                        $version = RDR_Setting::get("latestversion")->value;
                        $data = array("message" => sprintf(t("update.5"), $version), "event" => "success",  "next" => "prepare", "params" => array("version" => $version));
                    }catch(Exception $e){
                        $data = array("message" => sprintf(t("update.7"), $e->getMessage()), "event" => "error");
                    }
                break;
                case "prepare":
                    try{
                        # downloading zip file from GIT
                        $url = "https://github.com/brainfoolong/nreeda/archive/".get("version").".zip";
                        $data = RDR_FileContents::get($url);
                        if($data === false) return;

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
                            "message" => t("update.9"),
                            "event" => "success",
                            "next" => "update",
                            "params" => array(
                                "updatefolder" => $folder,
                                "rootfolder" => CHOQ_ROOT_DIRECTORY,
                                "updateurl" => url()->getByAlias("base", "modules/RDR/tmp/update/nreeda-".get("version")."/update.php")
                            )
                        );
                    }catch(Exception $e){
                        $data = array("message" => sprintf(t("update.7"), $e->getMessage()), "event" => "error");
                    }
                break;
                case "db":
                    try{
                        # updating database
                        $generator = CHOQ_DB_Generator::create(db());
                        $generator->addModule("RDR");
                        $generator->updateDatabase();

                        $data = array("message" => t("update.10"), "event" => "success", "next" => "cleanup");
                    }catch(Exception $e){
                        $data = array("message" => sprintf(t("update.7"), $e->getMessage()), "event" => "error");
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

                        $data = array("message" => t("update.11"), "event" => "success");
                    }catch(Exception $e){
                        $data = array("message" => sprintf(t("update.7"), $e->getMessage()), "event" => "error");
                    }
                break;
                case "disable":
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
        headline(t("sidebar.26"));
        ?>
        <div class="indent">
            <?php echo s(t("update.14"), true)?>

            <div class="spacer"></div>
            <?php if(class_exists("ZipArchive")){?>
                <div class="update-check"><?php echo t("update.19")?></div>
            <?php }else{?>
                <span style="color:red"><?php echo t("update.15")?></span>
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
                    if(data.message) $("#result").append('<div class="type '+data.event+'">'+data.message+'</div>');
                    if(data.next && data.next.length){
                        req(data.next, data.params);
                    }else if(action != "disable"){
                        req("disable");
                        $("#result").append($('<div class="type '+data.event+'">').html(<?php echo json_encode(nl2br(t("update.12"), true))?>));
                    }
                });
            }
            if($(".update-check").length){
                var params = {};
                params.action = "update-check";
                params.code = '<?php echo self::getValidHash()?>';
                try{
                    $.getJSON("<?php echo url()->getModifiedUri(false)?>", params, function(data){
                        if(data.error){
                            $(".update-check").html(data.error);
                        }else if(data.version && data.update){
                            $(".update-check").html((<?php echo json_encode(t("update.18"))?>+'<br/><br/>').replace(/\%s/, data.version)).append(
                                $('<input type="button" class="btn" value="<?php echo t("update.16")?>"/>').one("click", function(){
                                    if(confirm('<?php echo t("update.17")?>')){
                                        this.value = '<?php echo t("update.13")?>';
                                        req("start");
                                    }
                                })
                            );
                        }else if(data.version && !data.update){
                            $(".update-check").html('<?php echo t("update.6")?>');
                        }
                    });
                }catch(e){
                    $(".update-check").html(e.message);
                }
            }
        })();
        </script>
        <?php
    }
}