<?php
namespace TRW\ActiveRecord\Database;

use PDO;

/**
* このクラスはデータベースの基底クラス.
*
* 各データベースはこのクラスを継承しなければならない
*
*/
abstract class Driver {

	private $defaultConfig = [

	];

/**
* 
*
* @param array $config データベースの接続情報など
*/
	public function __construct($config){
		$config = array_merge($this->defaultConfig, $config);
		$this->connection($config);
	}

/**
* コネクションを設定する.
* 
* コネクションはPDOクラスでなければならない
* 
* @param array $config データベース接続、設定に必要な情報
* @return void
*/
	abstract protected function connection($config);

/**
* コネクションを返す
*
* @return \PDO
*/
	abstract protected function connect();

/**
* テーブルの有無を検査する.
*
* @param string $tableName テーブル名
: @return boolean テーブルがあればtrue なければfalseを返す
*/
	abstract public function tableExists($tableName);

/**
* テーブルのスキーマを返す.
*
* スキーマは次の構造で返さなければならない
* 
*	$schema = [
*		$filed => [
*			'type' => type,
*			'null' => boolean,
*			'key' => boolean,
*			'default' => '',
*			'extra' => ''
*		],
*      :
*      :
*      : 
*	];	
*
* (例
*	$example = [
*		'name' => [
*			'type' => text,
*			'null' => true,
*			'key' => false,
*			'default' => 'anonymous',
*			'extra' => ''
*		],
*      :
*      :
*      : 
*	];	
*
*/
	abstract public function schema($tableName);

	public function query($sql){
		return $this->connect()->query($sql);
	}

	protected function prepare($query){
		$sql = $query['sql'];
		$statement = $this->connect()->prepare($sql);
		$bindValue = $query['bindValue'];
		foreach($bindValue as $k => $v){
			$statement->bindValue($k, $v);
		}
		return $statement;
	}

	protected function execute($query){
		$statement = $this->prepare($query);
		if($statement->execute()){
			return $statement;
		}
		return false;
	}
/**
* データベーステーブルからレコードを取得する.
*
* @param string $tableName 取得したいテーブル名 ユーザーからの値を渡してはならない
* @param array 取得対象のカラム名　ユーザーからの値を渡してはならない
* 次の構造で渡さなければならない
* $fields = 
*  [
*    'id',
*    'name',
*	 'age'
*  ];
* @param array $conditions
  以下のキーを使うことができる
* $conditions =
*  [
*	 'where'=>[
*      'field'=>'name',
*      'comparision'=>'=',
*      'value'=>'foo'
* 	 ],
*    'and' => [
*      'field' => 'age',
*      'comparition' => '>',
*      'value' => 20
*    ],
* 	 'order' => 'id DESC',
* 	 'limit' => 1,
* 	 'offset' => 3
*  ];
*
* @return PDOStatement|false executeに失敗したらfalseを返す
*/

	public function read($tableName, array $fields, $conditions = []){
		$query = $this->buildSelectQuery($tableName, $fields, $conditions);
		$result = $this->execute($query);

		if($result !== false){
			$result->setFetchMode(PDO::FETCH_ASSOC);	
		}

		return $result;
	}

/**
* セレクト文を返す
*
* @param string $tableName 取得したいテーブル名　ユーザーからの値を渡してはならない
* @param array $fileds 取得したいカラム名　ユーザーからの値を渡してはならない
* @param array $conditions 検索条件
* @return array セレクト文とバインドバリューのセット
* 次の構造をした配列を返す
* $queryObject = 
*  [
*    'sql' => "SELECT id,name,age FROM users WHERE id=:id",
*    'bindValue' => [
*      ':id' => 1
*    ]
*  ];
*
*
*/
	public function buildSelectQuery($tableName,  $fields, $conditions = []){
		$columns = implode(',', $fields);
		$makeConditions = $this->conditions($conditions);		

		$sql =
			 "SELECT {$columns} FROM {$tableName} {$makeConditions['string']}";

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $makeConditions['bindValue']; 

		return $queryObject;
	}
/**
* 条件文を返す.
*
*
* @param array $conditions
  以下のキーを使うことができる
* $conditions =
*  [
*	 'where'=>[
*      'field'=>'name',
*      'comparision'=>'=',
*      'value'=>'foo'
* 	 ],
*    'and' => [
*      'field' => 'age',
*      'comparition' => '>',
*      'value' => 20
*    ],
* 	 'order' => 'id DESC',
* 	 'limit' => 1,
* 	 'offset' => 3
*  ];
* @return array 条件文とバインドバリューのセット
* 次の構造をした配列を返す
* $queryObject = 
*  [
*    'sql' => "WHERE name=:name AND age>:age LIMIT 1 OFFSET 3 ORDER BY id DESC",
*    'bindValue' => [
*      ':name' => 'foo',
*      ':age' => 20
*    ]
*  ];
*/
	protected function conditions($conditions){
		extract($conditions);
		$bindValue = [];
		$whereString = '';
		if(!empty($where)){
			$whereString = 'WHERE ' . $where['field'] . $where['comparision'] .':'. $where['field'];
			$bindValue[':' . $where['field']] = $where['value'];
		}
		$andString = '';
		if(!empty($and)){
			$andString = 'AND ' . $and['field'] . $and['comparision'] . ':' . $and['field'];
			$bindValue[':' . $and['field']]  = $and['value'];
		}
		$orderString = '';
		if(!empty($order)){
			$orderString = 'ORDER BY ' .  $order;
		}
		$limitString = '';
		if(!empty($limit)){
			$limitString = 'LIMIT ' . $limit;
		}
		$offsetString = '';
		if(!empty($offset)){
			$offsetString = 'OFFSET ' . $offset;
		}
		
		$result['string'] ="{$whereString} {$andString} {$orderString} {$limitString} {$offsetString}";
		$result['bindValue'] = $bindValue;

		return $result;
	}

	public function insert($tableName, $values){
		$query = $this->buildInsertQuery($tableName, $values);
		if($this->execute($query) !== false){
			return true;
		}
		return false;
	}

	public function buildInsertQuery($tableName, $values){
		$columns = implode(',', array_keys($values));
		foreach($values as $k => $v){
			$bindValue[':' . $k] = $v;
		}
		$bindKey = implode(',', array_keys($bindValue));

		$sql = "INSERT INTO {$tableName} ({$columns}) VALUES({$bindKey})";	

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $bindValue;

		return $queryObject;

	}

	public function update($tableName, $values, $conditions = []){
		$query = $this->buildUpdateQuery($tableName, $values, $conditions);
		if($this->execute($query) !== false){
			return true;		
		}
		return false;
	}

	public function buildUpdateQuery($tableName, $values, $conditions = []){
		$columns = implode(',', array_keys($values));
		$bindValue = [];
		foreach($values as $k => $v){
			$bindKey = ':' . $k;
			$sets[] = $k . '=' . $bindKey;
			$bindValue[$bindKey] = $v;
		}
		$set = implode(',', $sets);
		$makeConditions = $this->conditions($conditions);

		$sql = "UPDATE {$tableName} SET {$set} {$makeConditions['string']}";

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $makeConditions['bindValue'] + $bindValue;

		return $queryObject;
	}

	public function delete($tableName, $conditions = []){
		$query = $this->buildDeleteQuery($tableName, $conditions);
		
		if($this->execute($query) !== false){
			return true;		
		}
		return false;
	}

	public function buildDeleteQuery($tableName, $conditions = []){
		$makeConditions = $this->conditions($conditions);

		$sql = "DELETE FROM {$tableName} {$makeConditions['string']}";

		$queryObject['sql'] = $sql;
		$queryObject['bindValue'] = $makeConditions['bindValue'];

		return $queryObject;
	}

	public function rowCount($table, $options = null){
		$column = '*';
		if(isset($options['column'])){
			$column = $options['column'];
		}

		$whereString = '';
		if(isset($options['where'])){
			$where = $options['where'];
			$whereString = "WHERE {$where['field']}{$where['comparision']}{$where['value']}";
		}
		$statement = $this->connect()->query("SELECT COUNT({$column}) FROM {$table} {$whereString}");

		$rowCount = $statement->fetchColumn();
		
		return $rowCount;
	}

	abstract public function begin();

	abstract public function commit();

	abstract public function rollback();

	abstract public function lastInsertId();


}
