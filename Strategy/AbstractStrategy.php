<?php
namespace TRW\ActiveRecord\Strategy;

use TRW\ActiveRecord\Strategy\InheritanceStrategy;

abstract class AbstractStrategy implements InheritanceStrategy {

	protected $operator;

	public function __construct($operator){
		$this->operator = $operator;
	}

/**
* 配列をフィルタリングして返す.
*
* @param array $filter 排除したいデータのキーリスト['name', 'user_id']<br>
* @param array $data　フィルタリングされるデータ<br>
* ['id'=>1, 'name'=>'foo', 'user_id'=>1, 'age'=>20]
* $return array フィルタリングされたデータ['id'=>1, 'age'=>20]
*/
	protected function filterData($filter, $data){
		$results = [];
		foreach($data as $k => $v){
			if(array_key_exists($k, $filter)){
				$results[$k] = $v;
			}
		}
		return $results;
	}


	protected  function hydrate($rowData, $recordClass){
		$pk = $recordClass::primaryKey();

		if(class_exists($recordClass)){
			$record = $this->operator->getCache($recordClass, $rowData[$pk]);

			if($record !== false){
				return $record;
			}

			$newRecord = $recordClass::newRecord($rowData);
			$newRecord->id = $rowData[$pk];

			$this->operator->setCache($newRecord);

			return $newRecord;
		}else{
			throw new Exception('class not found ' . $recordClass);
		}
	}


}
