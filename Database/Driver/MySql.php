<?php
namespace TRW\ActiveRecord\Database\Driver;

use PDO;
use TRW\ActiveRecord\Database\Driver;

class MySql extends Driver {

	private $connection;

	private $transactionCounter = 0;

	protected function connection($config){
		$dsn = $config['dns'];
		$user = $config['user'];
		$password = $config['password'];
		$this->connection = new PDO($dsn, $user, $password);
		$this->connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}

	protected function connect(){
		return $this->connection;
	}

	public function tableExists($tableName){
		$query = "SHOW TABLES LIKE '{$tableName}'";	
		$statement = $this->connection->prepare($query);
		$statement->execute();

		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		if(count($result) !== 0){
			return true;
		}else{
			return false;
		}
	}

	public function schema($tableName){
		$sql = "SHOW COLUMNS FROM {$tableName}";
		$stmt = $this->connection->prepare($sql);
		$stmt->execute();
		if($stmt !== false){
			return $stmt->fetchAll();
		}
		return false;
	}

	public function getTransactionCounter(){
		return $this->transactionCounter;
	}

	public function begin(){
		if(!$this->transactionCounter++){
			return $this->connection->beginTransaction();
		}
		return $this->transactionCounter >= 0;
	}

	public function commit(){
		if(!--$this->transactionCounter){
			return $this->connection->commit();
		}
		return $this->transactionCounter >= 0;
	}

	public function rollback(){
		if($this->transactionCounter >= 0){
			$this->transactionCounter = 0;
			return $this->connection->rollback();
		}
		$this->transactionCounter = 0;
		return false;
	}

	public function lastInsertId(){
		return $this->connection->lastInsertId();
	}

}
