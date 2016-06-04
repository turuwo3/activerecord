<?php
namespace TRW\ActiveRecord\Strategy;

use TRW\ActiveRecord\AssociationCollection;
use TRW\ActiveRecord\SchemaCollection;
use TRW\ActiveRecord\Strategy\InheritanceStrategy;
use TRW\ActiveRecord\RecordOperator;
use TRW\ActiveRecord\Util;

class STIStrategy implements InheritanceStrategy {

	private static $instance = null;

	private $operator;

	public function __construct($operator){
		$this->operator = $operator;
	}


/**
* シングルテーブル継承されたオブジェクトでレコードをラッピングして返す.
*
* 既にオブジェクトが読み込まれているならIdentityMapからキャッシュを取得する
* <br>IdentityMapになければキャッシュする
*i
* 読み込まれたオブジェクトはアソシエーションが定義されていれば<br>
* AssociationCollectionによって関連オブジェクトがアタッチされる<br>
* @access private
* @param array $rowData データベーステーブルから取得したレコード
* @return \TRW\ActiveRecord\BaseRecord BaseRecordを継承したオブジェクト
*/
	public function find($recordClass, $conditions = []){
		$statement = $this->operator->find($recordClass, $conditions);

		$result = [];
		foreach($statement as $rowData){
			$result[] = $this->loadParent($recordClass, $rowData);
		}

		$resultSet = [];
		list($namespace, $class) = Util::namespaceSplit($recordClass);
		foreach($result as $record){
			$fullName = $namespace . '\\' . $record['type'];
			$newRecord = $this->hydrate($record, $fullName);
			$this->operator->attach($newRecord, $fullName::associations());
			$resultSet[] = $newRecord;
		}
		
		return $resultSet;
	}

/**
* 自身と親クラスで定義されたカラムの行データのリザルトを返す.
*
* @param array $rowData データベーステーブルの行データ
* $return array 自身と親クラスのカラムのリザルト
*/
	private function loadParent($className, $rowData){
		list($namespace, $class) = Util::namespaceSplit($className);

		$STI = $namespace . '\\' .$rowData['type'];
		$result = [];
		while($STI !== false){
			if($STI !== 'TRW\ActiveRecord\BaseRecord'){
				$result = $result +  $this->findFirst($STI, $rowData['id'])->fetch();
			}
			$STI = get_parent_class($STI);
		}	
		return $result;
	}

	private  function findFirst($className, $id){
		$rowData = $this->operator->find(
			$className,
			[
				'where'=>[
					'field'=>$className::primaryKey(),
					'comparision'=>'=',
					'value'=>$id
				]
			]
		);

		return $rowData;
	}

	public function newRecord($recordClass, $fields = []){
			list($namespace, $class) = Util::namespaceSplit($recordClass);
			$STI = !empty($fields['type']) ?
				 $namespace . '\\' . $fields['type'] : $recordClass;
	
			if(!class_exists($STI)){
				throw new Exception('class not found ' . $STI);
			}

			$fields['type'] = $class;

			$result = $this->loadParentColumns($STI);	
			$fields = $this->filterData($result, $fields);		
			$fields = $fields + $result;

			return $fields;
	}
/**
* 配列をフィルタリングして返す.
*
* @param array $filter 排除したいデータのキーリスト['name', 'user_id']<br>
* @param array $data　フィルタリングされるデータ<br>
* ['id'=>1, 'name'=>'foo', 'user_id'=>1, 'age'=>20]
* $return array フィルタリングされたデータ['id'=>1, 'age'=>20]
*/
	private function filterData($filter, $data){
		$results = [];
		foreach($data as $k => $v){
			if(array_key_exists($k, $filter)){
				$results[$k] = $v;
			}
		}
		return $results;
	}

/**
* シングルテーブル継承時に使用される。親クラス使用するカラムのリザルトを返す.
*
* @param string $STI 親クラス名 
* @return array 継承元も含む使用するカラムのリスト
*/
	private static function loadParentColumns($STI){
		$result = [];
		while($STI !== false){
			if(!class_exists($STI)){
				throw new Exception('missing class '. $STI);
			}
			if($STI !== 'TRW\ActiveRecord\BaseRecord'){
				$result = $result + $STI::useColumn();
			}
			$STI = get_parent_class($STI);
		}	
		return $result;
	}


	public function save($record){
		$className = get_class($record);
		list($namespace, $class) = Util::namespaceSplit($className);
		if(empty($record->type)){
			$record->type = $class;
		}
		if(!class_exists($namespace . '\\' . $record->type)){
			throw new Exception('missing type '.$type);
		}

		if($record->isNew()){
			$operator->insert($className, $record);
			$success = $this->updateParent($record);
		}else{
			$success = $this->updateParent($record);
		}
		return $success;
	}


	private function updateParent($record){
		$STI = get_class($record);
			while($STI !== false){
				if($STI !== 'TRW\ActiveRecord\BaseRecord'){	
					$this->operator->update($STI, $record);
				}
			$STI = get_parent_class($STI);
		}	
		return true;		
	}


	public  function hydrate($rowData, $recordClass){
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
