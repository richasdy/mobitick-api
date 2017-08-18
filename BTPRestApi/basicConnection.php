<?php
namespace BTPRestAPI;

class Connection{
	private static $INSTANCE = null;

	// Create connection
	
	public function __construct(){
		$db_host		= "localhost";
		$username 		= "root";
		$password 		= "";
		$db_name		= "newDB";
		self::$INSTANCE	= mysqli_connect($db_host,$username,$password,$db_name);

		// Check connection
		if (mysqli_connect_errno()) {
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
	}
	
	public function getInstance()
	{
		return self::$INSTANCE;
	}
}
?>