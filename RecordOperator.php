<?php
namespace TRW\ActiveRecord;

use TRW\ActiveRecord\RecordOperatorInterface;

class RecordOperator implements RecordOperatorInterface{

	private $connection;

	private $record;

	public function __construct($connection, $record){
		$this->connection = $connection;
		$this->record = $record;
	}

	public function find($className, $conditions = []){
		$from = $this->record->tableName($className);
		$columns = array_keys($this->record->useColumn($className));
		$statement = $this->connection->read(
			$from,
			$columns,
			$conditions
		);
		return $statement;
	}

/**
* 配列をフィルタリングして返す.
*
* @param array $filter 排除したいデータのキーリスト['name', 'user_id']<br>
* @param array $data　フィルタリングされるデータ<br>
* ['id'=>1, 'name'=>'foo', 'user_id'=>1, 'age'=>20]
* $return array フィルタリングされたデータ['id'=>1, 'age'=>20]
*/
	public function filterData($filter, $data){
		$results = [];
		foreach($data as $k => $v){
			if(array_key_exists($k, $filter)){
				$results[$k] = $v;
			}
		}
		return $results;
	}

/**
* レコードクラスが使用するカラムのリスト.
*
*
* static::$useColumnをオーバーライドすると、<br>
* レコードクラスが使用できるカラムを制限することができる
*
* @return array 使用するカラムのリスト
*/
	private function saveTargetColumns($className, $record){
		$useColumn = $this->record->useColumn($className);

		$result = $this->filterData($useColumn, $record->getData());

		return $result;
	}

	public function insert($recordObject){
		$className = get_class($recordObject);
		$fields = $this->saveTargetColumns($className, $recordObject);

		if(!$recordObject->validate()){
			return false;
		}

		$success = $this->connection->insert($this->record->tableName($className), $fields);

		if($success){

			$id = $this->connection->lastInsertId();
			$recordObject->id = $id;

			return true;
		}else{
			return false;
		}

	}

	public function update($recordObject){
		$className = get_class($recordObject);
		$fields = $this->saveTargetColumns($className, $recordObject);
		if(!$recordObject->validate()){
			return false;
		}

		if(!$recordObject->id()){
			throw new \Exception('missing primarykey');
		}

		$success = $this->connection->update(
			$this->record->tableName($className),
			$fields,
			[
				'where'=>[
					'field'=>$this->record->primaryKey($className),
					'comparision'=>'=',
					'value'=>$recordObject->id
				]
			]
		 );

		if($success){
			return true;
		}else{
			return false;
		}

	}


	public function delete($recordObject){
		$className = get_class($recordObject);
		$id = $recordObject->id();
		if($id === false){
			return false;
		}

		$success = $this->connection->delete(
			$this->record->tableName($className),
			[
				'where'=>[
					'field'=>$this->record->primaryKey($className),
					'comparision'=>'=',
					'value'=>$id
				]
			]
		);

		return $success;
	}

	public function useColumn($className){
		return $this->record->useColumn($className);
	}

	public function tableName($className){
		return $this->record->tableName($className);
	}

}







