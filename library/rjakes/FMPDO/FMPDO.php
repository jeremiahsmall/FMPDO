<?php

/* FMPDO Library.
 *
 * @package FMPDO
    *
 * Copyright � 2013, Roger Jacques Consulting
 * See enclosed MIT license

 */


/**
 * Bring in all child and related classes
 */
require_once(__DIR__ . '/classes/FmpdoDb.php');
require_once(__DIR__ . '/classes/FmpdoRecord.php');
require_once(__DIR__ . '/classes/FmpdoResult.php');
require_once(__DIR__ . '/classes/FmpdoCommandEdit.php');
require_once(__DIR__ . '/classes/FmpdoError.php');
require_once(__DIR__ . '/classes/FmpdoCommandFind.php');


set_include_path(get_include_path().PATH_SEPARATOR.__DIR__.'/ZF2/Db/Sql');

function __autoload($class_name) {
    include $class_name . '.php';
}

//$sql = new Sql($db);

/**
 * Base FMPDO Class
 *
 * @package FMPDO
 * handles db connection and spawning of command objects
 */
class FMPDO {

    public static $sql_config = array();
    var $result = false;
    var $error = '';
    var $locale = 'en';  //TODO move config setting out to their own file

    /**
     * FMPDO Constructor
     * @param array $sql_config  an array of settings for the db connection
     *
     */
    function __construct($sql_config = array()) {

        self::$sql_config = $sql_config;
    }


    /**
     * @return string // the FMPDO API version
     */
    function getAPIVersion(){

        return "0.0.0";
    }


    /**
     * Test for whether or not a variable is an FmpdoError object.
     *
     * @param mixed $variable
     * @return boolean.
     * @static
     *
     */
    function isError($variable)
    {
        return is_a($variable, 'FmpdoError');
    }

    /**
     * Returns the current value of $property
     *
     * @param string name of the property
     * @return boolean.
     *
     */
    function getProperty($property) {
        return isset($this->$property) ? $this->$property : null;
    }


    /**
     * Fetches a record from the database by its id column
     *
     * @param $table the name of the sql table
     * @param $id  the value of the id/primary key
     * @return FmpdoError|FmpdoRecord
     */
    public function getRecordByID($table, $id) {
        $db = FmpdoDb::getConnection();
        $query = $db->prepare("SELECT *  FROM " . $table . " WHERE id="."'$id' " ."LIMIT 1" );
        try {
            if (!$query) {
                return new FmpdoError($db->errorInfo());
            }
            $result =  $query->execute();
        } catch (Exception $e) {
            return new FmpdoError($e);
        }
        $rows=$query->fetchAll();
        return new FmpdoRecord($rows[0]);
    }


    /**
     * Instantiates a new FMPDO_Find object
     *
     * @param $table the sql table that the query will be performed on
     * @return FmpdoCommandFind
     */
    function newFindCommand($table) {
        $findCommand = new FmpdoCommandFind($table);
        return $findCommand;
    }


    /**
     * Instantiates a new FMPDO_Edit object
     *
     * @param $table  the sql table that the edit will be performed in
     * @param $id  // the primary key of the record that will be edited
     * @return FmpdoCommandEdit
     */
    function newEditCommand($table, $id) {
        $editCmd = new FmpdoCommandEdit($table, $id);
        return $editCmd;
    }

    function createRecord($table) {
        $record = new FmpdoRecord($table);
        return $record;
    }

}