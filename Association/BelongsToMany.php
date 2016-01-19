<?php
namespace TRW\ActiveRecord\Association;

use Exception;
use TRW\ActiveRecord\Association;
use TRW\ActiveRecord\Util;
use TRW\ActiveRecord\BaseRecord;

class BelongsToMany extends Association {


	public function find($record){

		$results = $this->findTarget($record);

		if(count($results) === 0 || $results === false){
			$many = [];
		}else{
			$many = $results;
		}
		return $many;	
	}


	private function findTarget($record){
		$foreignKeyId = $record->id();

		$linkTable = $this->linkTable();
		$ownForeignKey = $this->createForeignKey($this->source());

		$links = BaseRecord::whereAll(
			$linkTable,
			[
				'where' => [
					'field' => $ownForeignKey,
					'comparision' => '=',
				 	'value' => $foreignKeyId
				]
			]);

		$targetForeignKey = $this->createForeignKey($this->target());

		$recordClass = $this->target();
		$results = [];

	    foreach($links as $target){
			$targetTable = $this->createTableName($this->target());
			$recordName = $this->recordNamespace($this->target());
			$find = BaseRecord::whereAll(
				$targetTable,
				[ 
					'where' => [
						'field' => 'id',
						'comparision' => '=',
						'value' => $target[$targetForeignKey]
					]
				],
				$recordName);

			if(count($find) !== 0 || $find !== false){
				$record = array_shift($find);
				$results[] = $record;
			}
		}

		return $results;
	}
/*
* @Override
*/
	protected function createForeignKey($name){
		return $this->createTableName($name) . '_id';
	}

	private function linkTable(){
		$ownTable = $this->createTableName($this->source());
		$targetTable = $this->createTableName($this->target());
		$linkTable = $ownTable . '_' . $targetTable;

		if(!BaseRecord::tableExists($linkTable)){
			$linkTable = $targetTable . '_' . $ownTable;
		}

		if(!BaseRecord::tableExists($linkTable)){
			throw new Exception('missing table ' . $linkTable);
		}
	
		return $linkTable;
	}

	public function isOwningSide($table){
		return 	true;
	}


	public function saveLinkTable($record){
		$linkTable = $this->linkTable();
		$ownForeignKey = $this->createForeignKey($this->source());
		$ownForeignKeyId = $record->id();

		$property = $this->target();
		$associated = $record->$property;
		$targetForeignKey = $this->createForeignKey($this->target());

		if(empty($associated)){
			return true;
		}

		foreach($associated as $obj){
			$success = BaseRecord::insertAll($linkTable,
				[
					$ownForeignKey => $ownForeignKeyId,
					$targetForeignKey => $obj->id()
				]
			);
			if(!$success){
				return false;
			}
		}

		return true;
	}

	public function deleteLinkTable($record){
		$linkTable = $this->linkTable();

		$property = $this->target();
		$associated = $record->$property;

		$targetForeignKey = $this->createForeignKey($this->target());

		if(empty($associated)){
			return true;
		}

		foreach($associated as $obj){
			$success = BaseRecord::deleteAll($linkTable,
				$obj->primaryKey(), '=', $obj->id());
			if(!$success){
				return false;
			}
		}

		return true;
	}

	public function saveTarget($record){
		$target = $this->target();
		$property = $this->target();
		$associated = $record->$property;

		if(empty($associated)){
			return true;
		}

		foreach($associated as $obj){
			if(!empty($obj)){
				if(!$obj->save()){
					return false;
				}
			}else{
				throw new Exception('missing record '. $target);
			}
		}

		return true;

	}

	public function save($record){
		if(!$this->saveTarget($record)){
			return false;
		}

		if(!$this->deleteLinkTable($record)){
			return false;
		}

		if(!$this->saveLinkTable($record)){
			return false;
		}

		return true;
	}









}
