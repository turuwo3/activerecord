<?php
namespace TRW\ActiveRecord\Database;

use PDO;

abstract class Driver {

	private $defaultConfig = [

	];

	public function __construct($config){
		$config = array_merge($this->defaultConfig, $config);
		$this->connection($config);
	}

	abstract protected function connection($config);

	abstract protected function connect();

	abstract public function tableExists($tableName);

	abstract public function schema($tableName);

	public function query($sql){
		return $this->connect()->query($sql);
	}

	protected function prepare($query){
		$sql = $query['sql'];
		$statement = $this->connect()->prepare($sql);
		$bindValue = $query['bindValue'];
		foreach($bindValue as $k => $v){
			$statement->bindValue($k, $v);
		}
		return $statement;
	}

	protected function execute($query){
		$statement = $this->prepare($query);
		if($statement->execute()){
			return $statement;
		}
		return false;
	}
/*
*  [
*	 'where'=[
*	 	'field'=>'id',
*		'comparision'=>'=',
*		'value'=>$id
* 	 ],
* 	 'order'='id desc',
* 	 'limit'='1',
* 	 'offset'='3'
*  ]
*/

	public function read($tableName, array $fields, $conditions = []){
		$query = $this->buildSelectQuery($tableName, $fields, $conditions);
		$result = $this->execute($query);

		if($result !== false){
			$result->setFetchMode(PDO::FETCH_ASSOC);	
		}

		return $result;
	}


	public function buildSelectQuery($tableName,  $fields, $conditions = []){
		$columns = implode(',', $fields);
		$makeConditions = $this->conditions($conditions);		

		$sql =
			 "SELECT {$columns} FROM {$tableName} {$makeConditions['string']}";

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $makeConditions['bindValue']; 

		return $queryObject;
	}

	protected function conditions($conditions){
		extract($conditions);
		$bindValue = [];
		$whereString = '';
		if(!empty($where)){
			$whereString = 'WHERE ' . $where['field'] . $where['comparision'] .':'. $where['field'];
			$bindValue[':' . $where['field']] = $where['value'];
		}
		$andString = '';
		if(!empty($and)){
			$andString = 'AND ' . $and['field'] . $and['comparision'] . ':' . $and['field'];
			$bindValue[':' . $and['field']]  = $and['value'];
		}
		$orderString = '';
		if(!empty($order)){
			$orderString = 'ORDER BY ' .  $order;
		}
		$limitString = '';
		if(!empty($limit)){
			$limitString = 'LIMIT ' . $limit;
		}
		$offsetString = '';
		if(!empty($offset)){
			$offsetString = 'OFFSET ' . $offset;
		}
		
		$result['string'] ="{$whereString} {$andString} {$orderString} {$limitString} {$offsetString}";
		$result['bindValue'] = $bindValue;

		return $result;
	}

	public function insert($tableName, $values){
		$query = $this->buildInsertQuery($tableName, $values);
		if($this->execute($query) !== false){
			return true;
		}
		return false;
	}

	public function buildInsertQuery($tableName, $values){
		$columns = implode(',', array_keys($values));
		foreach($values as $k => $v){
			$bindValue[':' . $k] = $v;
		}
		$bindKey = implode(',', array_keys($bindValue));

		$sql = "INSERT INTO {$tableName} ({$columns}) VALUES({$bindKey})";	

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $bindValue;

		return $queryObject;

	}

	public function update($tableName, $values, $conditions = []){
		$query = $this->buildUpdateQuery($tableName, $values, $conditions);
		if($this->execute($query) !== false){
			return true;		
		}
		return false;
	}

	public function buildUpdateQuery($tableName, $values, $conditions = []){
		$columns = implode(',', array_keys($values));
		$bindValue = [];
		foreach($values as $k => $v){
			$bindKey = ':' . $k;
			$sets[] = $k . '=' . $bindKey;
			$bindValue[$bindKey] = $v;
		}
		$set = implode(',', $sets);
		$makeConditions = $this->conditions($conditions);

		$sql = "UPDATE {$tableName} SET {$set} {$makeConditions['string']}";

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $makeConditions['bindValue'] + $bindValue;

		return $queryObject;
	}

	public function delete($tableName, $conditions = []){
		$query = $this->buildDeleteQuery($tableName, $conditions);
		
		if($this->execute($query) !== false){
			return true;		
		}
		return false;
	}

	public function buildDeleteQuery($tableName, $conditions = []){
		$makeConditions = $this->conditions($conditions);

		$sql = "DELETE FROM {$tableName} {$makeConditions['string']}";

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $makeConditions['bindValue'];

		return $queryObject;
	}

	public function rowCount($table, $column = null){
		$name = '*';
		if($column !== null){
			$name = $column;
		}

		$statement = $this->connect()->query("SELECT COUNT({$name}) FROM {$table}");

		$rowCount = $statement->fetchColumn();
		
		return $rowCount;
	}

	abstract public function begin();

	abstract public function commit();

	abstract public function rollback();

	abstract public function lastInsertId();


}
