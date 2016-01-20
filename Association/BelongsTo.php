<?php
namespace TRW\ActiveRecord\Association;

use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\Association;

class BelongsTo extends Association {

	public function find($record){
		$primaryKey = $record->primaryKey();
		$foreignKey = $this->createForeignKey($this->target());
		$foreignKeyId = $record->$foreignKey;

		$conditions = [
			'where' => [
				'field' => $primaryKey,
				'comparision' => '=',
				'value' => $foreignKeyId
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
			$belongsTo = null;
		}else{
			$belongsTo = array_shift($results);
		}
	    return $belongsTo;	
	}

	public function isOwningSide($table){
		$tableName = get_class($table);
		return 	$tableName === $this->recordNamespace($this->target());
	}

	public function foreignKey(){
		if(empty($this->target())){
			throw new Exception('connection is not keyed');
		}
		return lcfirst($this->target()) . '_id';
	}

	public function save($record){
		$foreignKey = $this->createForeignKey($this->target());
		$property = $this->target();
		$associated = $record->$property;

		if(!empty($associated)){
			if($associated->save()){
				$parentId = $associated->id();
				$record->$foreignKey = $parentId;
				return true;
			}
			return false;
		}
		return true;
	}





}
