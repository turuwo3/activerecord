<?php
namespace TRW\ActiveRecord\Strategy;

use TRW\ActiveRecord\Strategy\AbstractStrategy;

class BasicStrategy extends AbstractStrategy {

	public function find($recordClass, $conditions = []){
		$statement = $this->operator->find(
			$recordClass,
			$conditions
		);
/*		
		$resultSet = [];
		foreach($statement as $rowData){
			$record = $this->operator->hydrate($recordClass, $rowData);
			$this->operator->attach($record, $recordClass::associations());
			$resultSet[] = $record;
		}
		
		return $resultSet;
*/
		return $statement->fetchAll();
	}

		
	public function save($record){
		if($record->isNew()){
			return $this->operator->insert(get_class($record), $record);
		}else{
			return $this->operator->update(get_Class($record), $record);
		}
	}

	public function newRecord($recordClass, $fields = []){
		$tableName = $recordClass::tableName();
		$defaults = $recordClass::useColumn();
		$fields = $fields + $defaults;
		$fields = $this->operator->filterData($this->operator->useColumn($recordClass), $fields);

		return $fields;
	}	


}
