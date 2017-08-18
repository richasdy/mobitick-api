<?php
	class Connection{
		/*
		private $dsn = 'mysql:dbname=db_bus_project;host=localhost';
		private $user = 'root';
		private $password = '';
		*/
		private $dsn = 'mysql:dbname=greatbtp_db_bus_project;host=localhost';
		private $user = 'greatbtp_ticket';
		private $password = 'Btp321Bos';
		private $dbh;
		private $get;
		private $getID;
		private $getByUser;
		private $post;
		private $location;
		private static $INSTANCE = null;
		//put operation disabled
		//private $put;
		private $delete;
		private static $resources = array("user","raw_track_data","harga_lokasi_trayek","lokasi","trayek","transaksi","bus","pengemudi_bus","pengemudi","tipe_akun_admin","akun_admin","server_config","t_version");
		
		private function __construct(){
			try {
				$this->dbh = new PDO($this->dsn, $this->user, $this->password);
			} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
				die();
			}
			$this->get = array();
			$this->getID = array();
			$this->getByUser = array();
			$this->post = array();
			//put operation disabled
			//$put = array();
			$this->delete = array();
			$locationQuery = "SELECT  `nama_lokasi`,abs(`latitude` - ?) + abs(`longitude` - ?) as naiveDif FROM `lokasi` ORDER BY `naiveDif` ASC limit 0,1";
			$updateRawTrackData = "UPDATE `raw_track_data` SET `date_time` = NOW( ) ,`latitude` = ?,`longitude` = ? WHERE `ID_Bus` =?;";
			$updateServerConfig = "UPDATE `server_config` SET `update_interval` = ?";
			$updateServerConfigWithType = "UPDATE `server_config` SET update_interval = ?, type = ?";
			$selectQuery = "select * from `%s`";
			$selectByUserQuery = "select * from `%s` where `ID_user` = ?";
			$selectIDQuery = "select * from `%s` where ID=?" ;
			$insertQuery = "insert into `%s` values ";
			$userValues = "(null,?,?,?,?)";
			$rawTrackDataValues = "(?,?,?,?)";
			$hargaLokasiTrayekValues = "(?,?,?,?,?)";
			$lokasiValues = "(null,?,?,?)";
			$trayekValues = "(null,?)";
			$transaksiValues = "(null,?,?,?,?,?)";
			$busValues = "(null,?)";
			$pengemudiBusValues = "(?,?,?)";
			$pengemudiValues = "(null,?)";
			$akunAdminValues = "(null,?,?,?)";
			$tipeAkunAdminValues = "(null,?,?,?,?)";
			$adminConfigValues = "(?)";
			$insertQueries = array($insertQuery.$userValues,$insertQuery.$rawTrackDataValues,$insertQuery.$hargaLokasiTrayekValues,$insertQuery.$lokasiValues,$insertQuery.$trayekValues,$insertQuery.$transaksiValues,$insertQuery.$busValues,$insertQuery.$pengemudiBusValues,$insertQuery.$pengemudiValues,$insertQuery.$akunAdminValues,$insertQuery.$tipeAkunAdminValues,$insertQuery.$adminConfigValues);
			//put operation disabled
			//$updateQuery = "UPDATE `%s` SET `Name`=[value-1],`Room_Type`=[value-2] WHERE 1";
			$deleteQuery = "DELETE FROM `%s` WHERE `ID`=?";
			$count = count(Connection::$resources);
			for($i = 0 ; $i < $count; $i++)
			{
				$tableName = Connection::$resources[$i];
				$this->get[$tableName] = $this->dbh->prepare(sprintf($selectQuery,$tableName));
				if($tableName === "lokasi" || $tableName === "trayek" || $tableName === "user" || $tableName === "transaksi" || $tableName === "bus" || $tableName === "pengemudi" )
					$this->getID[$tableName] = $this->dbh->prepare(sprintf($selectIDQuery,$tableName));
				if($tableName === "lokasi" || $tableName === "trayek" || $tableName === "transaksi" || $tableName === "bus")
					$this->getByUser[$tableName] = $this->dbh->prepare(sprintf($selectByUserQuery,$tableName));
				if($tableName != "t_version")
					$temp = sprintf($insertQueries[$i],$tableName);
				if($tableName == "raw_track_data")
					$temp = $updateRawTrackData;
				if($tableName == "server_config")
					$temp = $updateServerConfig;
				$this->post[$tableName] = $this->dbh->prepare($temp);
				//put operation disabled
				//$put[$tableName] = 
				$this->delete[$tableName] = $this->dbh->prepare(sprintf($deleteQuery,$tableName));
			}
			$this->getID["harga_lokasi_trayek"] = $this->dbh->prepare(sprintf("SELECT * FROM `%s` where `ID_trayek`=?","harga_lokasi_trayek"));
			$this->location = $this->dbh->prepare($locationQuery);
		}
		
		public static function getInstance()
		{
			if(Connection::$INSTANCE == null)
			{
				Connection::$INSTANCE = new Connection();
			}
			
			return Connection::$INSTANCE;
		}
		
		public function getAll($resource)
		{
			if(!isset($this->get[$resource]))
				return array();
			$this->get[$resource]->execute(array());
			return $this->get[$resource]->fetchAll();
		}
		
		public function getID($resource,$id)
		{
			if(!isset($this->getID[$resource]))
				return array();
			$this->getID[$resource]->execute(array($id));
			return $this->getID[$resource]->fetchAll();
		}
		
		public function getLocation($lat,$long)
		{
			$this->location->execute(array($lat,$long));
			return $this->location->fetchAll();
		}
		
		public function insert($resource, $argArray)
		{
			if(!isset($this->post[$resource]))
				return array();
			if($resource == "raw_track_data")
			{
				$temporaryArray = array();
				$temporaryArray[] = $argArray[1];
				$temporaryArray[] = $argArray[2];
				$temporaryArray[] = $argArray[0];
				$argArray = $temporaryArray;
			}
			//var_dump($argArray);
			//var_dump($this->post[$resource]);
			$retVal = $this->post[$resource]->execute(array_values($argArray));
			if($retVal)
				return array("status"=>200);
			else
			{
				return array("status"=>400);			
			}
		}
		
		public function delete($resource, $id)
		{
			if(!isset($this->delete[$resource]))
				return array();
			$getID[$resource]->bind_param("i",$id);
			return $getID[$resource]->execute();
		}
	}
?>