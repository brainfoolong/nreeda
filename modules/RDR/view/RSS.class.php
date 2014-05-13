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
* RSS
*/
class RDR_RSS extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        if(RDR::$isInstalled && get("token")){
            $token = explode(".", get("token"));
            if(count($token) == 2 && saltedHash("sha256", $token[0]) == $token[1]){
                session("user.id", $token[0]);
                if(user()){
                    $max = min(array(200, (int)get("max")));
                    $catmax = get("catmax") ? min(array($max, (int)get("catmax"))) : $max;
                    $feedmax = get("feedmax") ? min(array($max, (int)get("feedmax"))) : $max;
                    $feedIds = explode(",", get("f"));

                    $rss = new SimpleXMLElement('<'.'?xml version="1.0" encoding="utf-8"?><rss></rss>');
                    $rss->addAttribute("version", "2.0");

                    $channel = $rss->addChild("channel");
                    $channel->addChild("title", get("title"));
                    $channel->addChild("description", get("desc"));
                    $channel->addChild("pubDate", dt("now")->format("r"));

                    $feeds = user()->getFeeds();
                    $catCount = array();
                    $count = 0;
                    $allEntries = array();
                    foreach($feeds as $feed){
                        $feedCount = 0;
                        if(in_array($feed->getId(), $feedIds)){
                            $category = user()->getCategoryToFeed($feed);
                            $catId = $category->getId();
                            if(!isset($catCount[$category->getId()])) $catCount[$catId] = 0;
                            $entries = db()->getByCondition("RDR_Entry", "feed = {0}", array($feed), "-datetime", min(array($feedmax, $max - $count, $catmax - $catCount[$catId])));
                            user()->loadReadedFlags(array_keys($entries));
                            foreach($entries as $entry){
                                if(isset(user()->_cacheReaded[$entry->getId()])) continue;
                                if($count >= $max) break 2;
                                if($feedCount >= $feedmax) continue 2;
                                if($catCount[$catId] >= $catmax) continue 2;

                                $entry->category = $category;
                                $entry->time = $entry->datetime->getUnixtime();
                                $allEntries[] = $entry;

                                $feedCount++;
                                $count++;
                                $catCount[$catId]++;
                            }
                        }
                    }

                    arraySortProperty($allEntries, "time", SORT_DESC);
                    foreach($allEntries as $entry){
                        $feed = $entry->feed;
                        $category = $entry->category;

                        $item = $channel->addChild("item");
                        $this->addCData($item->addChild("title"), $entry->title);
                        $this->addCData($item->addChild("link"), $entry->link);
                        $this->addCData($item->addChild("description"), $entry->text);
                        $this->addCData($item->addChild("category"), $category->name);
                        $item->addChild("guid", $entry->getId());
                        $item->addChild("pubDate", $entry->datetime->format("r"));
                    }

                    $data = $rss->asXML();
                    CHOQ_OutputManager::cleanAllBuffers();
                    header("Content-type: application/rss+xml");
                    echo $data;
                    die();
                }
            }
        }
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        headline(t("rss.1"));
        echo t("rss.2").'<div class="spacer"></div>';

        headline(t("rss.3"));

        $categories = user()->getCategories();
        $i = 1;
        foreach($categories as $category){
            $feeds = $category->feeds;
            ?>
            <div class="category" style="margin-bottom: 10px;">
                <div class="inner">
                    <div class="title" data-category="<?php echo $category->getId()?>" data-feed="0">
                        <input type="checkbox" class="cat" checked="checked"/> <b><?php echo s($category->name)?></b>
                    </div>
                    <?php if($feeds){
                        foreach($feeds as $feed){
                            ?>
                            <div class="feed" data-category="<?php echo $category->getId()?>" data-feed="<?php echo $feed->getId()?>" style="margin-left: 10px; font-size: 12px;">
                                <input type="checkbox" class="feed" checked="checked" value="<?php echo $feed->getId()?>"/> <?php echo s($feed->getCustomName($category))?>
                            </div>
                            <?php
                        }
                    }?>
                </div>
            </div>
            <?php
        }

        $table = new Form_Table($this->getForm());
        $table->addButton(t("rss.9"), array("onclick" => "checkSubmit()"));
        echo $table->getHtml();
        ?>
        <script type="text/javascript">
        function checkSubmit(){
            var formtable = $("#form-rss").parent().data("formtable");
            if(formtable.validateAllFields()){
                var ids = [];
                $("input.feed:checked").each(function(){
                    ids.push(this.value);
                });
                window.open('<?php echo url()->getModifiedUri(false)?>?f='+ids.join(",")+"&token=<?php echo user()->getId().".".saltedHash("sha256", user()->getId())?>&title="+encodeURIComponent($("input[name='title']").val())+"&desc="+encodeURIComponent($("input[name='desc']").val())+"&max="+encodeURIComponent($("input[name='max']").val())+"&catmax="+encodeURIComponent($("input[name='catmax']").val())+"&feedmax="+encodeURIComponent($("input[name='feedmax']").val()));
            }

        }
        $("input.cat").on("click", function(){
            $(this).parent().parent().find("input.feed").prop("checked", this.checked);
        });
        </script>
        <?php

    }

    /**
    * Add cdata text to node
    *
    * @param mixed $node
    * @param mixed $text
    */
    private function addCData(SimpleXMLElement $node, $text){
        $node = dom_import_simplexml($node);
        $no   = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($text));
    }



    /**
    * Get form
    *
    * @return Form_Form
    */
    public function getForm(){
        $form = new Form_Form("rss");
        $form->attributes->add("method", "get");

        $validatorNumber = new Form_Validator_Regex();
        $validatorNumber->setErrorMessage(t("rss.10"));
        $validatorNumber->setRegex("^[0-9]+$", "");

        $field = new Form_Field_Text("title", t("rss.4"));
        $field->setDefaultValue("nReeda RSS");
        $field->attributes->add("style", "width:95%;");
        $form->addField($field);
        $validator = new Form_Validator_Required();
        $validator->setErrorMessage(t("form.validator.required"));
        $field->addValidator($validator);

        $field = new Form_Field_Text("desc", t("rss.5"));
        $field->attributes->add("style", "width:95%;");
        $form->addField($field);

        $field = new Form_Field_Text("max", t("rss.6"));
        $field->setDefaultValue("50");
        $field->attributes->add("size", 6);
        $field->addValidator($validatorNumber);
        $form->addField($field);

        $field = new Form_Field_Text("catmax", t("rss.7"));
        $field->setDefaultValue("0");
        $field->attributes->add("size", 6);
        $field->addValidator($validatorNumber);
        $form->addField($field);

        $field = new Form_Field_Text("feedmax", t("rss.8"));
        $field->setDefaultValue("0");
        $field->attributes->add("size", 6);
        $field->addValidator($validatorNumber);
        $form->addField($field);

        return $form;
    }
}