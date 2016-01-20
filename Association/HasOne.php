<?php
namespace TRW\ActiveRecord\Association;

use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\Association;

class HasOne extends Association{

	public function find($record){

		$foreignKey = $this->createForeignKey($this->source());
		$foreignKeyId = $record->id();
		$conditions = [
			'where'=>[
				'field'=>$foreignKey,
				'comparision'=>'=',
				'value'=>$foreignKeyId
			]
		];

		$conditions = $this->mergeOption($conditions);

		$targetTable = $this->createTableName($this->target());
		$recordName = $this->recordNamespace($this->target());
	
		$results = BaseRecord::whereAll(
			$targetTable,
			$conditions,
			$recordName);

		if(count($results) === 0 || $results === false){
			$hasOne = null;
		}else{
			$hasOne = array_shift($results);
		}

	    return $hasOne;	
	}

	public function isOwningSide($table){
		$tableName = get_class($table);
		return 	$tableName === $this->source();
	}

	public function save($record){
		$foreignKey = $this->createForeignKey($this->source());
		$property = $this->target();
		$associated = $record->$property;

		if(empty($associated)){
			return true;
		}
			
		$associated->$foreignKey = $record->id();
	
		return $associated->save();

	}


}
