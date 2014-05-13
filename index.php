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

if(version_compare(PHP_VERSION, '5.3.0', '<')) die("You must have at least PHP 5.3.0 installed, your version is ".PHP_VERSION);
if(!function_exists("mb_strlen")) die("You must enable the 'mbstring' extension in your PHP config");

try{
    define("CHOQ_ACTIVE_MODULE", "RDR");
    require(__DIR__."/modules/CHOQ/class/Core.class.php");
    CHOQ_Core::initialise();
    CHOQ::get(CHOQ_ACTIVE_MODULE);
    CHOQ_View::loadViewForCurrentUri();
}catch(Exeption $e){
     CHOQ_Exception::exceptionHandler($e);
}