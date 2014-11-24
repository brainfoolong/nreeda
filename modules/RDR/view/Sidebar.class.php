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
* The sidebar
*/
class RDR_Sidebar extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        if(!user()) return;

        $categories = user()->getCategories();
        if($this->getParam("icons")){
            ?>
            <div id="sidebar-icons" class="sidebar-icons">
                <div id="add-toggle" class="icon-box toggle">
                    <div class="icon"></div>
                    <div class="box">
                        <h3><?php echo t("sidebar.22")?></h3>
                        <input type="text" style="width:95%; display: block;" class="focus black"/>
                        <select class="black">
                            <option value="<?php echo t("uncategorized")?>"><?php echo t("uncategorized")?></option>
                            <?php foreach($categories as $category){
                                if($category->name == t("uncategorized")) continue;
                                ?>
                                <option value="<?php echo $category->name?>"><?php echo s($category->name)?></option>
                            <?php }?>
                        </select><br/>
                        <input type="button" class="btn add-feed" value="<?php echo t("sidebar.1")?>"/>
                        <input type="button" class="btn" value="<?php echo t("sidebar.2")?>" onclick="window.location.href = '<?php echo l("RDR_Organize")?>'"/>
                        <div class="spacer">
                            <?php echo t("sidebar.24")?> <a href="<?php echo l("RDR_BrowserScript")?>"><?php echo t("sidebar.25")?></a>
                        </div>
                    </div>
                </div>
                <div id="search-toggle" class="icon-box toggle">
                    <div class="icon"></div>
                    <div class="box">
                        <form name="searchfeed" method="get" action="<?php echo l("RDR_Feeds", array("param" => "search"))?>">
                            <h3><?php echo t("sidebar.3")?></h3>
                            <input name="q" type="text" style="width:95%; display: block;" class="focus black"/>
                            <input type="submit" class="btn" value="<?php echo t("sidebar.21")?>"/>
                        </form>
                    </div>
                </div>
                <div id="settings-toggle" class="icon-box toggle">
                    <div class="icon"></div>
                    <div class="box">
                        <h3><?php echo t("sidebar.4")?></h3>
                        <?php $this->showSettingSelect("hideimages", array(0 => ucfirst(t("yes")), 1 =>  ucfirst(t("no"))), t("sidebar.5"))?>
                        <?php $this->showSettingSelect("layout", array("default" => t("sidebar.6"), "small" => t("sidebar.23"), "big" => t("sidebar.7"), "headline" => t("sidebar.8")), "Layout")?>
                        <div class="clear"></div>
                        <?php $this->showSettingSelect("noautoread", array(0 =>  ucfirst(t("yes")), 1 =>  ucfirst(t("no"))), t("sidebar.9"))?>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        <?php }

        if($this->getParam("content")){?>
            <div class="sidebar" id="sidebar"><div class="padd">
                <div class="logo">
                    <a href="<?php l("RDR_Home")?>"><img src="<?php echo url()->getByAlias("public", "img/logo-1.png")?>" alt="" width="100%"/></a>
                    <span>v<?php echo RDR_VERSION?></span>
                </div>
                <a href="<?php echo l("RDR_Home")?>" class="main"><?php echo t("dashboard")?></a>
                <div class="line"><div class="inner"></div></div>
                <a href="<?php echo l("RDR_Feeds", array("param" => "all"))?>" class="main"><?php echo t("sidebar.10")?> <span class="counter" data-id="all">0</span></a>
                <div class="line"><div class="inner"></div></div>
                <a href="<?php echo l("RDR_Feeds", array("param" => "saved"))?>" class="main"><?php echo t("sidebar.11")?> <span class="counter" data-id="saved">0</span></a>
                <div class="line"><div class="inner"></div></div>
                <a href="<?php echo l("RDR_Archive")?>" class="main"><?php echo t("archive.title")?> <span class="counter" data-id="archive">0</span></a>
                <div class="line"><div class="inner"></div></div>

                <?php foreach($categories as $category){
                    $feeds = $category->feeds;
                    ?>
                    <div class="main">
                        <span class="plus">+</span>
                        <a href="<?php echo $category->getLink()?>"><?php echo s($category->name)?></a>
                        <span class="counter" data-id="<?php echo $category->getId()?>">0</span>
                    </div>
                    <div class="sub-container">
                        <?php if($feeds){
                            foreach($feeds as $feed){
                                $favicon = $feed->getFaviconUrl();
                                ?>
                                <div>
                                    <?php if($favicon) echo '<span class="favicon" style="background-image:url('.$favicon.')"></span>'?>
                                    <a href="<?php echo $feed->getLink()?>" class="sub"><?php echo s(cut($feed->getCustomName($category), 30))?> <span class="counter" data-id="<?php echo $feed->getId()?>">0</span></a>
                                </div>
                            <?php }
                        }?>
                    </div>
                    <div class="line"><div class="inner"></div></div>
                <?php }?>

                <div class="main"><span class="plus">+</span> <?php echo t("sidebar.12")?></div>
                <div class="sub-container">
                    <div><a href="<?php echo l("RDR_Organize")?>" class="sub"><?php echo t("sidebar.13")?></a></div>
                    <div><a href="<?php echo l("RDR_Settings")?>" class="sub"><?php echo t("sidebar.14")?></a></div>
                    <div><a href="<?php echo l("RDR_RSS")?>" class="sub"><?php echo t("sidebar.15")?></a></div>
                </div>
                <div class="line"><div class="inner"></div></div>

                <?php if(needRole(RDR_User::ROLE_ADMIN)){?>
                    <div class="main"><span class="plus">+</span> <?php echo t("sidebar.16")?></div>
                    <div class="sub-container">
                        <div><a href="<?php echo l("RDR_Admin_System")?>" class="sub"><?php echo t("sidebar.17")?></a></div>
                        <div><a href="<?php echo l("RDR_Admin_Settings")?>" class="sub"><?php echo t("sidebar.18")?></a></div>
                        <div><a href="<?php echo l("RDR_Admin_User")?>" class="sub" data-noparams="1"><?php echo t("sidebar.19")?></a></div>
                        <div><a href="<?php echo l("RDR_Admin_Update")?>" class="sub"><?php echo t("sidebar.26")?></a></div>
                    </div>
                    <div class="line"><div class="inner"></div></div>
                <?php }?>
                <a href="<?php echo l("RDR_Logout")?>" class="main"><?php echo t("sidebar.20")?></a>
            </div></div>
            <?php
        }
    }

    /**
    * Show settings select
    *
    * @param mixed $name
    * @param mixed $values
    * @param string $label
    */
    private function showSettingSelect($name, $values, $label){
        ?>
        <div class="setting">
            <?php echo $label?><br/>
            <select data-setting="<?php echo $name?>" class="black">
                <?php foreach($values as $key => $value){?>
                    <option value="<?php echo $key?>" <?php echo user()->setting($name) == $key ? 'selected="selected"' : NULL?>><?php echo $value?></option>
                <?php }?>
            </select>
        </div>
        <?php
    }
}