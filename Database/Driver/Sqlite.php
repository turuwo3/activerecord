<?php
namespace TRW\ActiveRecord\Database\Driver;

use PDO;
use TRW\ActiveRecord\Database\Driver;

class Sqlite extends Driver {


	private $connection;

	protected function connection($config){
		$dsn = $config['dns'];
		$this->connection = new PDO($dsn);
	}

	protected function connect(){
		return $this->connection;
	}

	public function tableExists($tableName){
		$query = "SELECT * FROM sqlite_master WHERE type='table'";	
		$statement = $this->connection->prepare($query);
		$statement->execute();

		$result = $statement->fetch(PDO::FETCH_ASSOC);
		if(count($result) !== false){
			return true;
		}else{
			return false;
		}
	}

	public function schema($tableName){
		$sql = "SELECT * FROM sqlite_master WhERE type='table' AND name = '{$tableName}'";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute();

		$result = $stmt->fetch();
		
		preg_match('/CREATE TABLE users\((.*)\)/', $result['sql'], $matches);
		$explode = explode(',', $matches[1]);
		foreach($explode as $v){
			$set = explode(' ', trim($v));
			$trim[0] = trim($set[0]);
			$trim[1] = trim($set[1]);
			$columns[] = [$trim[0] => $trim[1]];
		}

		return $columns;
	}


	public function begin(){
		return $this->connection->beginTransaction();
	}

	public function commit(){
		return $this->connection->commitTransaction();
	}

	public function rollback(){
		return $this->connection->rollbackTransaction();
	}

	public function lastInsertId(){
		return $this->connection->lastInsertId();
	}



}
