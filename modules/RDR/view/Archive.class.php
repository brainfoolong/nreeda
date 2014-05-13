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
* The archive
*/
class RDR_Archive extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        headline(t("archive.title"));
        ?>
        <table width="100%" cellpadding="5" cellspacing="0">
        <tr>
            <td width="50%" align="right"><?php echo t("archive.1")?></td>
            <td><input type="text" name="from" value="<?php echo dt("now - 1 week")->getFullDate()?>" size="10"/></td>
        </tr>
        <tr>
            <td width="50%" align="right"><?php echo t("archive.2")?></td>
            <td><input type="text" name="to" value="<?php echo dt("now")->getFullDate()?>" size="10"/></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="button" class="btn" value="<?php echo t("archive.3")?>" onclick="submit()"/></td>
        </tr>
        </table>
        <script type="text/javascript">
        function submit(){
            window.location.href = '<?php echo l("RDR_Feeds", array("param" => "archive"))?>?from='+$("input[name='from']").val()+"&to="+$("input[name='to']").val();
        }
        </script>
        <?php
    }
}