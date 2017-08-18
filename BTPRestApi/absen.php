<?php
namespace BTPRestAPI;
use \DateTime;

class Absen {
	public function postMasuk($data){        
		return $this->_postMasuk($data['authID'],$data['location']);
	}
	
	public function postPulang($data){        
		return $this->_postPulang($data['sessionID'],$data['kategori'],$data['laporan']);
	}

    public function _postMasuk($authID,$location){
		$longitude 	= $location['longitude'];
		$latitude	=	$location['latitude'];
		$connection	= new Connection();
		//
		$result;
		$instance 	= $connection->getInstance();
		$sql 		= "select `idSession` from `user` where `id` = ?";
		$statement 	= $instance->stmt_init();
		$statement->prepare($sql);
		$statement->bind_param("i", $authID);
		$statement->execute();
		$statement->bind_result($result);
		$statement->close();
		
		if($result == "")
		{
			//create session
			$date 		= new DateTime();
			$timestamp 	= $date->getTimestamp();
			$session	= hash('sha256',$timestamp.$authID);
			$sql 		= "UPDATE `user` set `idSession` = ? WHERE `id` = ?";
			$statement 	= $instance->stmt_init();
			$statement->prepare($sql);
			$statement->bind_param("si", $session,$authID);
			$statement->execute();
			$statement->close();
			//insert data to absen
			$sql 		= "INSERT INTO `absen`(`idUser`, `jam_masuk`, `latitude`, `longitude`) VALUES (?,now(),?,?)";
			$statement 	= $instance->stmt_init();
			$statement->prepare($sql);
			$statement->bind_param("idd", $authID,$longitude,$latitude);
			$statement->execute();
			$statement->close();
			
			return array('sessionID' => $session);
		}
		else
		{
			//error, session has created
			return array('error' => 'logged in at another device');
		}
    }
	
	public function _postPulang($sessionID,$kategori,$laporan){
        $connection	= new Connection();
		//check session ID
		$result;
		$instance 	= $connection->getInstance();
		$sql 		= "SELECT `id` FROM `user` WHERE `idSession` = ?";
		$statement 	= $instance->stmt_init();
		$statement->prepare($sql);
		$statement->bind_param("s", $sessionID);
		$statement->execute();
		$statement->bind_result($result);
		$statement->close();
		
		if($result == NULL || $result == "")
		{
			var_dump($sessionID);
			var_dump($sql);
			return array('error','user not sign-in');
		}
		else
		{
			//update data
			$sql 		= "UPDATE `absen` SET `jam_pulang`=now(),`kategori`=?,`laporan`=? WHERE `idUser` = ?";
			$statement 	= $instance->stmt_init();
			$statement->prepare($sql);
			$statement->bind_param("ssi", $kategori,$laporan,$result);
			$success = $statement->execute();
			$statement->close();
			if($success)
			{
				//logout
				$sql 		= "UPDATE `user` SET `idSession`='' WHERE `id` = ?";
				$statement 	= $instance->stmt_init();
				$statement->prepare($sql);
				$statement->bind_param("i",$result);
				$success = $statement->execute();
				$statement->close();
				return array('success'=>'success, session deleted');
			}
			else
			{
				return array('error'=>$statement->error());
			}
		}
    }
}
?>