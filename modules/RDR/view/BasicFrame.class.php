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
* The basic frame for the layout
*/
class RDR_BasicFrame extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        # load the view
        $view = $this->getParam("view");
        ob_start();
        if(!RDR::$isInstalled && !$view instanceof RDR_Install){
            redirect(l("RDR_Install"), 302);
        }else{
            if(!user() && RDR::$isInstalled && !$view instanceof RDR_Login){
                $url = url()->getUri();
                redirect(l("RDR_Login")."?redirect=".urlencode($url), 302);
            }else{
                $view->getContent();
            }
        }
        $content = ob_get_contents();
        ob_end_clean();

        # some html preparations
        $bodyClasses = array("page-".slugify(strtolower(get_class($view))));
        $jsVars = array();

        $dir = CHOQ_ACTIVE_MODULE_DIRECTORY."/view";
        $files = array(
            "css" => array(CHOQ_ROOT_DIRECTORY."/modules/Form/view/_css/form.css", "{$dir}/_css/default.css"),
            "js" => array("{$dir}/_js/jquery/jquery.js", "{$dir}/_js/default.js", CHOQ_ROOT_DIRECTORY."/modules/Form/view/_js/form.js")
        );
        $name = "default";
        html()->addFileGroupToHead($name, "css", CHOQ_ACTIVE_MODULE_DIRECTORY."/public/static", "static", $files["css"]);
        html()->addFileGroupToHead($name, "js", CHOQ_ACTIVE_MODULE_DIRECTORY."/public/static", "static", $files["js"]);

        $jsVars["message"] = v("message");
        if(RDR::$isInstalled) {
            $jsVars["proxyUrl"] = l("RDR_Proxy");
            $jsVars["apiUrl"] = l("RDR_API");
        }
        $userData = array();
        if(user()){
            $jsVars["ajaxUrl"] = l("RDR_Ajax");
            user()->updateNewsCache();
            $userData = user()->getAjaxData();
        }

        if(isMobile()){
            $dir = CHOQ_ACTIVE_MODULE_DIRECTORY."/view";
            $files = array(
                "css" => array("{$dir}/_css/mobile.css"),
                "js" => array()
            );
            $name = "mobile";
            html()->addFileGroupToHead($name, "css", CHOQ_ACTIVE_MODULE_DIRECTORY."/public/static", "static", $files["css"]);
            html()->addFileGroupToHead($name, "js", CHOQ_ACTIVE_MODULE_DIRECTORY."/public/static", "static", $files["js"]);
            $bodyClasses[] = "mobile";
        }
        ?><!DOCTYPE html>
        <?php html()->getOpeningHTMLTag()?>
        <head>
        <?php html()->getHeadTag();?>
        <link rel="shortcut icon" href="<?php echo url()->getByAlias("public" ,"favicon.ico")?>" type="image/icon" />
        <meta name="generator" content="Choqled PHP Framework" />
        <base href="<?php echo url()->getByAlias("base")?>"/>
        <script type="text/javascript">
        Global.vars = <?php echo json_encode($jsVars, JSON_FORCE_OBJECT)?>;
        </script>
        <!--[if lte IE 8]>
        <script type="text/javascript">
        $(document).ready(function(){
            Global.message("<?php echo t("iewarning")?>")
        });
        </script>
        <![endif]-->
        </head>
        <body class="<?php echo implode(" ", $bodyClasses)?>">
            <div id="top-message"></div>
            <div class="container a">
                <?php view("RDR_Sidebar", array("content" => 1))?>
            </div><div class="container b">
                <div class="content" id="content">
                    <div class="padd">
                        <?php view("RDR_Sidebar", array("icons" => 1))?>
                        <?php echo $content?>
                    </div>
                </div>
                <div class="padd" id="content"></div>
            </div><div class="container c"></div>
            <script type="text/javascript">Global.init(<?php echo json_encode($userData)?>);</script>
        </body>
        </html>
        <?php
    }
}