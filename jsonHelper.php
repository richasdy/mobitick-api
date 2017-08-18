<?php
	include "connection.php";
	class JSONHelper
	{		public static function getAll($resource)		{			$connectionInstance = Connection::getinstance();			$queryResult = $connectionInstance->getAll($resource);						return json_encode($queryResult);		}
		public static function get($resource)
		{
			$connectionInstance = Connection::getinstance();
			$arg = func_get_args();
			if(!isset($arg[1]))
				$queryResult = $connectionInstance->get($resource);
			else
			{
				$GETVar = $arg[1];
				$queryResult = $connectionInstance->getLocation($GETVar['lat'],$GETVar['long']);
			}
			return json_encode($queryResult);
		}
		
		public static function getID($resource,$id)
		{
			$connectionInstance = Connection::getinstance();
			$queryResult = $connectionInstance->getID($resource,$id);
			return json_encode($queryResult);
		}
		
		public static function insert($resource)
		{			$connectionInstance = Connection::getinstance();
			$queryResult = $connectionInstance->insert($resource,func_get_arg(1));
			return json_encode($queryResult);
		}	
		public static function delete($resource, $id)
		{
			$getID[$resource]->bind_param("i",$id);
			return $getID[$resource]->execute();
		}
		private static function mysql_field_array( $query ) {   
			$field = mysql_num_fields( $query );	   
			for ( $i = 0; $i < $field; $i++ ) {		   
				$names[] = mysql_field_name( $query, $i );		   
			}		   
			return $names;
		}  
    }
?>