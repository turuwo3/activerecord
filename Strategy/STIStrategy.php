<?php
namespace TRW\ActiveRecord\Strategy;

use TRW\ActiveRecord\Strategy\STIStrategy;
use TRW\ActiveRecord\AssociationCollection;
use TRW\ActiveRecord\IdentityMap;

class STIStrategy implements BaseRecordStrategy {

	private $recordClass;

	private $connection;

	public function __construct($recordClass, $connection){
		$this->recordClass = $recordClass;
		$this->connection = $connection;
	}

/**
* シングルテーブル継承されたテーブルのレコードを取得.
*
*/
	public function find($conditions = []){
		$recordClass = $this->recordClass;
		$from = $recordClass::tableName();
		$columns = array_keys($recordClass::useColumn());
		$statement = $this->connection->read(
			$from,
			$columns,
			$conditions
		);
		
		$resultSet = [];
		foreach($statement as $rowData){
			$resultSet[] = $this->loadParent($rowData);
		}
	
		return $resultSet;
	}

/**
* レコードクラスの名前空間を取得する.
*
* @return string レコードクラスの名前空間
*/
	public function getNamespace(){
		$recordClass = $this->recordClass;
		$fullName = $recordClass::className();
		$split = explode('\\', $fullName);
		array_pop($split);
		$namespace = implode('\\', $split);

		return $namespace;
	}

/**
* 自身と親クラスで定義されたカラムの行データのリザルトを返す.
*
* @param array $rowData データベーステーブルの行データ
* $return array 自身と親クラスのカラムのリザルト
*/
	public function loadParent($rowData){
		$namespace = $this->getNamespace();

		$STI = $namespace . '\\' .$rowData['type'];
		$result = [];
		while($STI !== false){
			if($STI !== 'TRW\ActiveRecord\BaseRecord'){
				$result = $result +  $this->load($STI, $rowData['id']) ->fetch();
			}
			$STI = get_parent_class($STI);
		}	
		return $result;
	}

/**
* データベーステーブルからプライマリーキーにマッチした行を返す
* @access private
* @param int $id 読み込みたいテーブルのid
* @return PDOstatement|false 読み込みに失敗した場合false
*/
	private function load($recordClass, $id){
		$rowData = $this->connection->read(
			$recordClass::tableName(),
			array_keys($recordClass::useColumn()),
			[
				'where'=>[
					'field'=>$recordClass::primaryKey(),
					'comparision'=>'=',
					'value'=>$id
				]
			]
		);

		return $rowData;
	}

	public function insert(){}

	public function update(){}

	public function newRecord($filds = []){}

}


















