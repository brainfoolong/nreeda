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
* The installer
*/
class RDR_Install extends CHOQ_View{

    /**
    * On load not implemented
    */
    public function onLoad(){
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        if(RDR::$isInstalled) error(t("reader.installed"));
        if(req()->isAjax()){
            CHOQ_Exception::$customHandler = false;
            try{
                $connString = post("db")."://".post("dbUser").":".post("dbPass")."@".post("dbHost")."/".post("dbName");
                if(post("db") == "sqlite3") {
                    $connString = "sqlite3://".CHOQ_ACTIVE_MODULE_DIRECTORY."/db/reader-".md5(date("dmy").__DIR__).".sqlite";
                    $connString = str_replace(DS, "/", $connString);
                }
                if(post("check") == "final"){
                    $fileData = "<?php\nif(!defined(\"CHOQ\")) die();\n/**\n * Local Configuration\n**/\n\n";
                    $fileData .= 'CHOQ_DB::add("default", \''.$connString.'\');'."\n";
                    $fileData .= 'v("hash.salt", "'.uniqid().sha1(microtime().uniqid()).'");'."\n";
                    $tmpfile = CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp/_RDR.local.tmp.php";
                    file_put_contents($tmpfile, $fileData);
                    include($tmpfile);

                    $generator = CHOQ_DB_Generator::create(db());

                    $tables = $generator->getExistingTables();
                    foreach($tables as $table) {
                        if($table == "_choqled_metadata" || substr(strtolower($table), 0, 4) == "rdr_"){
                            db()->query("DROP TABLE ".db()->quote($table));
                        }
                    }

                    $generator->addModule("RDR");
                    $generator->updateDatabase();

                    $user = new RDR_User(db());
                    $user->username = post("username");
                    $user->setPassword(post("password"));
                    $user->role = RDR_User::ROLE_ADMIN;
                    $user->store();

                    rename($tmpfile, CHOQ_ACTIVE_MODULE_DIRECTORY."/_RDR.local.php");
                }
                if(post("check") == "db"){
                    CHOQ_DB::add("test", $connString);
                    db("test")->query("CREATE TABLE ".db("test")->quote("rdr_test")." (".db("test")->quote("id")." INT NOT NULL)");
                    db("test")->query("DROP TABLE ".db("test")->quote("rdr_test"));
                }
            }catch(Exception $e){
                echo $e->getMessage()."<br/>";
                echo s($e->getTraceAsString(), true);
            }
            ob_flush();
            die();
        }
        ?>
        <div class="center">
            <img src="<?php echo url()->getByAlias("public", "img/logo-1.png")?>" alt=""/>
        </div>
        <div data-step="1">
            <?php headline(t("install.1"))?>
            <?php echo s(t("install.2"), true)?>

            <br/><br/>
            <input class="btn" type="button" value="<?php echo t("install.next")?>" data-step="2"/>
        </div>
        <div data-step="2" style="display: none;">
            <?php headline(t("install.3"))?>

            <ul>
                <li><?php echo t("install.4")?>: <?php echo $this->check("permissions")?></li>
                <li><?php echo t("install.5")?>: <?php echo $this->check("db")?></li>
                <li><?php echo t("install.6")?>: <?php echo $this->check("xml")?></li>
            </ul>

            <br/>
            <input class="btn" type="button" value="<?php echo t("install.next")?>" data-step="3"/>
        </div>
        <div data-step="3" style="display: none;">
            <?php headline(t("install.7"))?>

            <span style="display: inline-block; width:200px"><?php echo t("install.8")?>:</span>
            <select class="db">
                <option value=""></option>
                <?php
                if(CHOQ_DB_Mysql::isAvailable()) echo '<option value="mysql">MySQL</option>';
                ?>
            </select><br/><br/>
            <div style="display: none;" data-db="mysql">
                <span style="display: inline-block; width:200px"><?php echo t("install.9")?>:</span> <input type="text" name="db-name" value="nreeda"/><br/>
                <span style="display: inline-block; width:200px"><?php echo t("install.10")?>:</span> <input type="text" name="db-host" value="localhost"/><br/>
                <span style="display: inline-block; width:200px"><?php echo t("install.11")?>:</span> <input type="text" name="db-user"/><br/>
                <span style="display: inline-block; width:200px"><?php echo t("install.12")?>:</span> <input type="password" name="db-pass"/>
                <br/><br/>
                <span style="color:red">
                    <?php echo t("install.13")?>
                </span>
            </div>
            <div class="dbcheck" style="display: none; margin-top: 10px; color:red;"></div>

            <br/><br/>
            <input class="btn" type="button" value="<?php echo t("install.next")?>" data-step="4"/>
        </div>
        <div data-step="4" style="display: none;">
            <?php headline(t("install.14"))?>

            <span style="display: inline-block; width:200px"><?php echo t("username")?>:</span> <input type="text" name="username" value="admin"/><br/>
            <span style="display: inline-block; width:200px"><?php echo t("password")?>:</span> <input type="password" name="password"/>

            <br/><br/>
            <input class="btn" type="button" value="<?php echo t("install.next")?>" data-step="5"/>
        </div>
        <div data-step="5" style="display: none;">
            <?php headline(t("install.17"))?>

            <?php echo s(t("install.18"), true)?>

            <div class="finalcheck loading" style="display: none;  margin-top: 10px; color:red;"></div>
            <br/><br/>
            <input class="btn" type="button" value="<?php echo t("install.19")?>" data-step="6"/>
        </div>
        <script type="text/javascript">
        function getConfigData(){
            var d = {};
            d.db = $("select.db").val();
            d.dbName = $("input[name='db-name']").val();
            d.dbUser = $("input[name='db-user']").val();
            d.dbPass = $("input[name='db-pass']").val();
            d.dbHost = $("input[name='db-host']").val();
            d.username = $("input[name='username']").val();
            d.password = $("input[name='password']").val();
            return d;
        }
        $("input[data-step]").on("click", function(){
            var s = $(this).attr("data-step");
            if(s == "4"){
                var d = getConfigData();
                d.check = "db";
                $.post('<?php echo url()->getModifiedUri(false)?>', d, function(data){
                    $("div.dbcheck").show().html(data);
                    if(data == ""){
                        $("div[data-step]").hide();
                        $("div[data-step='"+s+"']").show();
                    }
                });
            }else if(s == "6"){
                var d = getConfigData();
                d.check = "final";
                Global.message("<?php echo t("install.20")?>", true);
                $.post('<?php echo url()->getModifiedUri(false)?>', d, function(data){
                    $("div.finalcheck").show().html(data);
                    if(data == "") {
                        Global.message("<?php echo t("install.21")?>");
                        window.location.reload();
                    }else{
                        Global.message("<?php echo t("install.22")?>");
                    }
                });
            }else{
                $("div[data-step]").hide();
                $("div[data-step='"+s+"']").show();
            }
        });
        $("select.db").on("change init", function(){
            var v = this.value;
            $("div[data-db]").hide().filter("[data-db='"+v+"']").show();
        }).trigger("init");
        </script>
        <?php
    }

    /**
    * Check permissions
    *
    * @param mixed $type
    */
    private function check($type){
        $flag = true;
        switch($type){
            case "permissions":
                $writeableFolders = array(
                    CHOQ_ACTIVE_MODULE_DIRECTORY,
                    CHOQ_ACTIVE_MODULE_DIRECTORY."/public/static",
                    CHOQ_ACTIVE_MODULE_DIRECTORY."/public/img/favicons",
                    CHOQ_ACTIVE_MODULE_DIRECTORY."/tmp",
                    CHOQ_ACTIVE_MODULE_DIRECTORY."/db"
                );
                foreach($writeableFolders as $folder){
                    if(!is_dir($folder)){
                        $flag = sprintf(t("install.23"), $folder);
                        break;
                    }
                    if(!is_writable($folder)){
                        $flag = sprintf(t("install.24"), $folder);
                        break;
                    }
                }
            break;
            case "ini":
                if(!ini_get("allow_url_fopen")) $flag = t("install.25");
                if(!function_exists("fsockopen")) $flag = t("install.26");
            break;
            case "xml":
                if(!function_exists("simplexml_load_file")){
                    $flag = t("install.27");
                }
            break;
            case "db":
                if(!CHOQ_DB_Mysql::isAvailable() && !CHOQ_DB_Sqlite3::isAvailable()){
                    $flag = t("install.28");
                }
            break;
        }
        echo '<span style="color:'.(is_bool($flag) ? "green" : "red").'">';
        if(!is_bool($flag)){
            echo $flag;
        }else{
            echo t("ok");
        }
        echo "</span>";
    }
}