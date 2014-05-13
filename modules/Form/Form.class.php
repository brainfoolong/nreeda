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
* Form
*/
class Form extends CHOQ_Module{

    /**
    * Fired when initialise the module
    */
    public function onInit(){
        # add this lines to your config to enable form support
        # html()->addFileGroupToHead("form", "css", "YOURDIR", "YOURALIAS", array(CHOQ_ROOT_DIRECTORY."/modules/Form/view/_css/form.css"));
    }
}

