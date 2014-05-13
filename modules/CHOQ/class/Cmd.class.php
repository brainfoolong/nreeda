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
 * Execute commands on the OS command line
 */
class CHOQ_Cmd{

    /**
    * Array of available CLI programs
    *
    * @var array
    */
    static $progs = array();

    /**
     * The added parameters
     *
     * @var array
     */
    public $params = array();

    /**
     * The output from the exec
     *
     * @var string
     */
    public $output;

    /**
     * The status from the exec
     *
     * @var int
     */
    public $status;

    /**
    * Add a exexutable CLI program
    *
    * @param string $alias The alias of the program
    * @param string $pathToApplication The path to the executable
    */
    static function addProgram($alias, $pathToApplication){
        self::$progs[$alias] = $pathToApplication;
    }

    /**
    * Execute a command manually
    *
    * @param string $cmd The command to execute, make sure that you have escaped all params correctly
    * @param string $output Pass by reference the output of the exec program
    * @param string $status Pass by reference the status of the exec program
    */
    static function executeCmd($cmd, &$output, &$status){
        # 2>&1 is required to see the error messages from the system
        exec("2>&1 ".$cmd, $output, $status);
    }

    /**
    * Create a instance and return it
    *
    * @param string $alias The alias for the program
    * @param array|NULL $parameters A array of parameters to add to the argument list
    * @return self
    */
    static function create($alias, $parameters = NULL){
        if(!isset(self::$progs[$alias])) error("Cmd Alias '$alias' not available");
        $obj = new self;
        $obj->params[] = escapeshellcmd(self::$progs[$alias]);
        if(is_array($parameters)) foreach($parameters as $value) $obj->addParameter($value, true);
        return $obj;
    }

    /**
    * Add a parameter to the command
    * This will be appended to the command execution string
    *
    * @param string $value The parameter value
    * @param bool $escape Escape the parameter
    */
    public function addParameter($value, $escape = true){
        $this->params[] = $escape ? escapeshellarg($value) : $value;
    }

    /**
    * Get the full CLI execution string with all added parameters
    *
    * @return string
    */
    public function getCmd(){
        return implode(" ", $this->params);
    }

    /**
     * Execute the command
     *
     * @return bool
     */
    public function execute(){
        self::executeCmd($this->getCmd(), $this->output, $this->status);
        $this->output = implode("\n", $this->output);
        if($this->status) return false;
        return true;
    }
}
