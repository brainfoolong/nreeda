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
* The maintenance mode
*/
class RDR_Maintenance extends CHOQ_View{

    /**
    * Get valid hash for maintenance mode
    *
    * @return string
    */
    static function getValidHash(){
        return saltedHash("md5", __FILE__);
    }

    /**
    * Enable the maintenance mode
    */
    static function enableMaintenanceMode(){
        self::disableMaintenanceMode();
        file_put_contents(CHOQ_ACTIVE_MODULE_DIRECTORY."/_RDR.local.php", "\nRDR::\$maintenanceMode = true;", FILE_APPEND);
    }

    /**
    * Disable the maintenance mode
    */
    static function disableMaintenanceMode(){
        $file = CHOQ_ACTIVE_MODULE_DIRECTORY."/_RDR.local.php";
        $data = file_get_contents($file);
        # if by any mistake a closing php tag is added to the local file
        $data = str_replace("?>", "", $data);
        $data = preg_replace("~.*?".preg_quote("RDR::\$maintenanceMode").".*~i", "", $data);
        $data = trim($data);
        file_put_contents($file, $data);
    }

    /**
    * On load
    */
    public function onLoad(){
        if(inNormalMode()) redirect(l("RDR_Home"), 302);
        if(get("disable-maintenance") == self::getValidHash()){
            self::disableMaintenanceMode();
            echo "Maintenance Mode manually disabled";
            return;
        }
        echo "nReeda is in maintenance mode";
    }
}