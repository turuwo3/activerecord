<?php
namespace TRW\ActiveRecord\Strategy;

use TRW\ActiveRecord\Strategy\AbstractStrategy;

class BasicStrategy extends AbstractStrategy {

	public function find($recordClass, $conditions = []){
		$statement = $this->operator->find(
			$recordClass,
			$conditions
		);
		
		$resultSet = [];
		foreach($statement as $rowData){
			$record = $this->operator->hydrate($recordClass, $rowData);
			$this->operator->attach($record, $recordClass::associations());
			$resultSet[] = $record;
		}
		
		return $resultSet;
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
		$fields = $this->operator->filterData(self::useColumn(), $fields);

		return $fields;
	}	


}
