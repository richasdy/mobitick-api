<?php
namespace BTPRestAPI;

class User {
    public function postLogin($data){
        return $this->_postLogin($data['username'], $data['password']);
    }
	
    function _postLogin($username, $password){
        //$connection
		$connection	= new Connection();
		//
		$result = array();
		$instance 	= $connection->getInstance();
		$sql 		= "select `id`,`idSession` from `user` where  `username` = ? and `password` = ?";
		$statement 	= $instance->stmt_init();
		$statement->prepare($sql);
		$statement->bind_param("ss", $username, $password);
		$statement->execute();
		$statement->bind_result($result[0],$result[1]);
		$statement->fetch();
		//var_dump($result);
		if($result == NULL || $result[0] == NULL)
		{
			return array('error'=>'invalid credential');
		}
		else
		{
			return array('username'=>$username, 'id'=>$result[0]);
		}
    }
}
?>