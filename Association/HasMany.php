<?php

namespace TRW\ActiveRecord\Association;

use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\Association;

class HasMany extends Association{

	function find($record){
		$foreignKey = $this->createForeignKey($this->source());
		$foreignKeyId = $record->id();
		$targetTable = $this->createTableName($this->target());
		$recordName = $this->recordNamespace($this->target());

		$results = BaseRecord::whereAll(
			$targetTable,
			[
				'where'=> [
					'field' => $foreignKey,
					'comparision' => '=',
					'value' => $foreignKeyId,
				]
			],
			$recordName);

		if(count($results) === 0 || $results === false){
			$many = [];
		}else{
			$many = $results;
		}
		return $many;	
	}

	public function isOwningSide($table){
		$tableName = get_class($table);
		return $tableName === $this->source();
	}

	public function save($record){
		$target = $this->target();
		$foreignKey = $this->createForeignKey($this->source());
		$property = $this->target();
		$associated = $record->$property;

		if(empty($associated)){
			return true;
		}

		foreach($associated as $obj){
			$obj->$foreignKey = $record->id();
			if(!$obj->save()){
				return false;
			}
		}
		return true;
	}




}
