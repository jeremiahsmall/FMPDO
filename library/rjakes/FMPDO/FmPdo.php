<?php

/* FMPDO Library
 *
* @package FMPDO
*
* Copyright � 2013, Roger Jacques Consulting
* See enclosed MIT license

*/


/**
 * Base FMPDO Class
 *
 * @package FMPDO
 * handles db connection and spawning of command objects
 */
class FmPdo {

	public static $connection;
	private $error = '';
	private $locale = 'en';  //TODO move config setting out to their own file

	/**
	 * Initiates the connection to the database.
	 * 
	 * Acts as an adapter to the connection object to have the correct interface.
	 * @param array $sql_config  an array of settings for the db connection
	 *
	 */
	function __construct($dbConfig = array()) {

		try
		{
			if (array_key_exists('dsn', $dbConfig)) {
				$dsn = $dbConfig['dsn'];
			}
			else {
				$dsn = $dbConfig['driver'].':'.$dbConfig['host'].':port='.$dbConfig['port'].';dbname='.$dbConfig['database'];
			}
			self::$connection = new Connect($dsn,$dbConfig['username'],$dbConfig['password']);
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}
	
	public function setConnection($pdo) {
		self::$connection = $pdo;
	}


	/**
	 * 
	 * Returns the API Version.
	 * 
	 * In the form X.Y.Z, X for Major, Y for Minor (compatible APIs), Z for bug corrections.
	 * @return string the FmPdo API version
	 */
	function getAPIVersion(){

		return "0.1.0";
	}


	/**
	 * Test for whether or not a variable is an Error object.
	 *
	 * @param mixed $results
	 * @return boolean.
	 * @static
	 *
	 */
	static function isError($results)
	{
		return is_a($results, 'Error');
	}

	/**
	 * Returns the current value of $property.
	 * 
	 * This is for retro-compatibility only and we highly discourage the usage of this method in new code.
	 * 
	 * For instance, to get $this->test, call $this->getProperty('test');
	 *
	 * @param string name of the property
	 * @return mixed | null if it doesn't exist.
	 *
	 */
	function getProperty($property) {
		return isset($this->$property) ? $this->$property : null;
	}

	/**
	 * @param $property
	 * @return the static connection for this instance of FMPDO
	 */
	public static function getConnection() {
		return self::$connection;
	}


	/**
	 * Fetches a record from the database by its id column
	 *
	 * @param $table the name of the sql table
	 * @param $id  the value of the id/primary key
	 * @return Error|Record
	 */
	public function getRecordByID($table, $id) {
		$db = FMPDO::getConnection();
		$query = $db->prepare("SELECT * FROM $table WHERE id='$id' LIMIT 1;" );
		try {
			if (!$query) {
				return new Error($db->errorInfo());
			}
			$result =  $query->execute();
		} catch (Exception $e) {
			return new Error($e);
		}
		$row=$query->fetch();
		return new Record($table, $row);
	}


	/**
	 * Instantiates a new Find object
	 *
	 * @param $table the sql table that the query will be performed on
	 * @return Find
	 */
	function newFindCommand($table) {
		$findCommand = new Find($table);
		return $findCommand;
	}

	/**
	 * Instantiates a new Find object
	 * this method is for backwards compatibility
	 * @param $table the sql table that the query will be performed on
	 * @return Find
	 */
	function newFindAllCommand($table) {
		$findCommand = new Find($table);
		return $findCommand;
	}


	/**
	 * Instantiates a new Edit object
	 *
	 * @param $table  the sql table that the edit will be performed in
	 * @param $id  // the primary key of the record that will be edited
	 * @return Fmpdo Edit  Object
	 */
	function newEditCommand($table, $id) {
		$editCmd = new Edit($table, $id);
		return $editCmd;
	}

	function createRecord($table) {
		$record = new Record($table);
		return $record;
	}

}