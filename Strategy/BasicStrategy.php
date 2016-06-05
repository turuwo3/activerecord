<?php
namespace TRW\ActiveRecord\Strategy;

use TRW\ActiveRecord\Strategy\AbstractStrategy;

class BasicStrategy extends AbstractStrategy {

	public function find($recordClass, $conditions = []){
		$statement = $this->operator->find(
			$recordClass,
			$conditions
		);

		return $statement->fetchAll();
	}

		
	public function save($record){
		if($record->isNew()){
			return $this->operator->insert($record);
		}else{
			return $this->operator->update($record);
		}
	}

	public function newRecord($recordClass, $fields = []){
		$tableName = $recordClass::tableName();
		$defaults = $recordClass::useColumn();
		$fields = $fields + $defaults;
		$fields = $this->operator->filterData($this->operator->useColumn($recordClass), $fields);

		return $fields;
	}	

	public function delete($record){
		return $this->operator->delete($record);
	}


}
