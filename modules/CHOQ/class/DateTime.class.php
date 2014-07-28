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
* Date Time class
*/

class CHOQ_DateTime{

    /**
    * The default timezone used for output
    *
    * @var string
    */
    static $timezone = "UTC";

    /**
    * The timezone used for database
    *
    * @var string
    */
    static $dbTimezone = "UTC";

    /**
    * The return value if the internal time is invalid
    *
    * @var mixed
    */
    static $invalidReturn = NULL;

    /**
    * Flag if date is valid or not
    *
    * @var bool
    */
    public $valid;

    /**
    * The internal unix timestamp
    *
    * @var int
    */
    public $unixtime;

    /**
    * Tmp timezone
    *
    * @var string
    */
    private $tmpTZ;

    /**
    * Constructor
    *
    * @param CHOQ_DateTime|string|int $time
    * @return CHOQ_DateTime
    */
    public function __construct($time){
        $this->valid = true;
        if(is_object($time)) $time = $time->__toString();
        if(is_string($time)){
            $this->overrideTZ(NULL);
            $time = strtotime($time);
            $this->restoreTZ();
        }
        if($time === false) $this->valid = false;
        $this->unixtime = $time;
    }

    /**
    * Get string representation
    *
    * @return string
    */
    public function __toString(){
        return $this->valid ? $this->format("c") : "INVALID DATE";
    }

    /**
    * Return the date in the given format
    * For a list of valid formats see PHP date() function
    *
    * @param string $format
    * @param NULL|string $timezone Override the default self::$timezone
    * @param NULL|string $invalidReturn Override the default self::$invalidReturn
    * @return string|NULL
    */
    public function format($format, $timezone = NULL, $invalidReturn = NULL){
        $invalidReturn = $invalidReturn === NULL ? self::$invalidReturn : $invalidReturn;
        if(!$this->valid) return $invalidReturn;
        $this->overrideTZ($timezone);
        $date = date($format, $this->unixtime);
        $this->restoreTZ();
        return $date;
    }

    /**
     * Get SQL Date Time format in db timezone
     *
     * @return string|NULL
     */
    public function getSQLDateTime(){
        return $this->format("Y-m-d H:i:s", self::$dbTimezone);
    }

    /**
     * Get SQL Date format in db timezone
     *
     * @return string|NULL
     */
    public function getSQLDate(){
        return $this->format("Y-m-d", self::$dbTimezone);
    }

    /**
     * Get SQL Time format in db timezone
     *
     * @return string|NULL
     */
    public function getSQLTime(){
        return $this->format("H:i:s", self::$dbTimezone);
    }

    /**
     * Get unix timestamp
     *
     * @return int
     */
    public function getUnixtime(){
        return $this->unixtime;
    }

    /**
     * Get formated date in d.m.Y in self::$timezone
     *
     * @return string
     */
    public function getFullDate(){
        return $this->format("d.m.Y");
    }

    /**
     * Get formated date in H:i in self::$timezone
     *
     * @return string
     */
    public function getTime(){
        return $this->format("H:i");
    }

    /**
     * Get formated date in d.m.Y H:i in self::$timezone
     *
     * @return string
     */
    public function getFullDateTime(){
        return $this->format("d.m.Y H:i");
    }

    /**
     * Get formated date in Y in self::$timezone
     *
     * @return string
     */
    public function getYear(){
        return $this->format("Y");
    }

    /**
     * Get formated date in n in self::$timezone
     *
     * @return string
     */
    public function getMonth(){
        return (int)$this->format("n");
    }

    /**
     * Get formated date in j in self::$timezone
     *
     * @return string
     */
    public function getDay(){
        return (int)$this->format("j");
    }

    /**
    * Get dayname in self::$timezone
    *
    * @param mixed $short Get short dayname if set to true
    * @param mixed $language Override current active language
    */
    public function getDayName($short = false, $language = NULL){
        return t("datetime.day".($short ? 'short' : '').".".$this->getDay(), $language);
    }

    /**
    * Get monthname in self::$timezone
    *
    * @param mixed $short Get short dayname if set to true
    * @param mixed $language Override current active language
    */
    public function getMonthName($short = false, $language = NULL){
        return t("datetime.month".($short ? 'short' : '').".".$this->getMonth(), $language);
    }

    /**
    * Modify the current time with the given modifier
    * Alias for strtotime("TIME_OF_INSTANCE $modifier")
    *
    * @param string $modifier
    * @param bool $clone Return a clone instead if editing itself
    * @return self
    */
    public function modify($modifier, $clone = false){
        $date = new self($this->format("c")." ".$modifier);
        if($clone) return $date;
        $this->valid = $date->valid;
        $this->unixtime = $date->unixtime;
        return $this;
    }

    /**
    * Set the current time to the given time
    *
    * @param CHOQ_DateTime|string|int $time
    * @param bool $clone Return a clone instead if editing itself
    * @return self
    */
    public function set($time, $clone = false){
        $date = new self($time);
        if($clone) return $date;
        $this->valid = $date->valid;
        $this->unixtime = $date->unixtime;
        return $this;
    }

    /**
    * Override timezone
    *
    * @param mixed $timezone
    */
    private function overrideTZ($timezone){
        $timezone = $timezone === NULL ? self::$timezone : $timezone;
        $this->tmpTZ = date_default_timezone_get();
        date_default_timezone_set($timezone);
    }

    /**
    * Restore TZ
    */
    private function restoreTZ(){
        if($this->tmpTZ) date_default_timezone_set($this->tmpTZ);
    }
}