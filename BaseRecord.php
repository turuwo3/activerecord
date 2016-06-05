<?php

namespace TRW\ActiveRecord;

use PDO;
use DateTime;
use Exception;
use TRW\ActiveRecord\Database\Driver;
use TRW\ActiveRecord\SchemaCollection;
use TRW\ActiveRecord\Schema;
use TRW\ActiveRecord\IdentityMap;
use TRW\ActiveRecord\Util;
use TRW\ActiveRecord\AssociatoinCollection;
use TRW\ActiveRecord\RecordOperator;
use TRW\ActiveRecord\RecordProperty;

/**
* レコードクラスの基底となるクラス.
*
* このクラスはデータベーステーブルの行をオブジェクトでラッピングし、<br>
* データの挿入、保存などを行うアクティブレコードを模している。
*
* 開発者はこのクラスを継承することでアクティブレコードの機能を利用することができます
*
* このクラスを継承するクラスは必要に応じてドメインロジックを実装してください
*
* 継承するクラスは、このクラスの「オーバーライド可能なフィールド」もしくは<br> 「オーバーライド可能なメソッド」のコメントが無い物はオーバーライドしてはいけません
*
* またpublic、protectedなフィールド、メソッドであっても<br>
* 「使用禁止」のコメントがあるものは使用してはいけません
*
*/
class BaseRecord {

/**
* データベースへ接続するためのドライバクラス.
*
* @var Driver 
* 
*/
	private static $connection;

/**
* レコードクラスの関連情報.
*
* オーバーライド可能なフィールド
*
* レコードクラスの関連を定義したい場合は継承先で次の様にオーバーライドする<br>
* $associations = <br>
*  [<br>
*    'HasOne' => [ <br>
*      'Profile' =[ <br>
*        'limit' => 5, <br>
*        'offset' => 2 <br>
*      ],<br>
*      'Skill' => [ <br>
*        'order' => 'id DESC' <br>
*      ]<br>
*    ],<br>
*    'HasMany' => [ <br>
*	   'Comment' => [] <br> 
*	 ]
*  ];
*
* 関係を表すキーワードは HasOne HasMany BelongsTo BelongsToManyが使用できる
*
* @var array　 
*/
	protected static $associations;

/**
* テーブル継承タイプ.
*
* オーバーライド可能なフィールド<br>
*
* テーブル継承時は必ずオーバーライトしてください<br>
* 現在サポートしている継承タイプはSTIのみとなっております<br>
* なのでオーバーライドするときはstaic protected $inheritance = 'STI'<br>
* としてください
* @var string
*/
	protected static $inheritance = false;

/**
* レコードオブジェクトが使用できるカラムを指定する.
*
* オーバーライド可能なフィールド 
*
* 使用するカラムを指定したい時は継承先に次の様にオーバーライドする<br>
* $useColumn = <br>
*  [ <br>
*  //field => type <br>
*    'id' => 'integer', <br>
*    'name' => 'string', <br>
*    'age' => 'integer' <br>
*  ]; <br>
* カラムの型はstring, integer, double, datetime<br>
* の内いづれかの文字列を使う事ができる 
*
* @var array
*/
	protected static $useColumn;

/**
* データベーステーブルのプライマリーキー.
*
* オーバーライド可能なフィールド
*
* @var string
*/
	protected static $primaryKey = 'id';

/**
* バリデーションエラー時に設定できる任意の文字列.
*
* @var string
*/
	private static $error;

/**
* レコードオブジェクトのフィールドデータ.
*
* データベーステーブルの行と対応している<br>
* 次の構造をしている<br>
* $data = <br>
*  [ <br>
*   //fieldName => value <br>
*    'name' => 'foo', <br>
*    'age' => 20 <br>
*  ]
*
* @var array
*/
	private $data;

/**
* フィールドデータが変更された時変更をキャッシュする.
* 
* フィールドデータとは$this->dataの事
*
* 次の構造をしている<br>
* $dirty =  <br>
*  [<br>
*   //fieldName => value <br>
*    'name' => 'modified'<br>
*  ]<br>
*  
* @var array
*/
	private $dirty;


	private function __construct($fieldData = null){
		if($fieldData !== null){
			$this->setData($fieldData);	
		}
	}

	public static function associations(){
		return static::$associations;
	}



/**
* フィールドデータをセットする.
*
* @param array　データベーステーブルの行データ　もしくはそれにあたるデータ
* @return void 
*/
	private function setData($fieldData){
		foreach($fieldData as $prop => $value){
			$this->data[$prop] = $value;
		}
	}

/**
* レコードオブジェクトのフィールドデータにアクセスする.
*
* フィールドデータとは$this->dataの事<br>
* 参照を渡したのはフィールドデータが配列だった場合楽に変更できるから<br>
* @param string $name アクセスしたいフィールド名<br>
* @return mixid アクセスしたいフィールド<br>
*/
	public function &__get($name){
		return $this->get($name);
	}


	private function &get($name){
		$value = null;

		if(isset($this->data[$name])){
			$value =& $this->data[$name];
		}
		return $value;
	}

/**
* フィールドに値をセットする.
*
* ここでいうフィールドとは$this->dataの事
* 
* @param string $name セットしたいフィールド名
* @param mixid $value セットしたい値
*/
	public function __set($name, $value){
		$this->set($name, $value);
	}
	
/**
* フィールドの値を適切にセットするメソッド.
*
* 次のルールでセットされる
*
* １・スキーマに存在しないフィールドはセットできない<br>
* ２・値はスキーマと対応した型にキャストされる<br>
* ３・レコードクラスで関連が定義されていないフィールドはセットできない<br>
* ４・自身フィールド($this->data)の変更を記録する<br>
*
* @param string $name フィールド名
* @param mixid $value セットする値
* @return void
*/
	private function set($name, $value){

		$schema = SchemaCollection::schema(static::tableName())
			->columns();

		$associations = AssociationCollection::hasAssociatedTarget(get_class($this));

		if(!empty($schema[$name])){

			if($schema[$name] === gettype($value)){
				$this->setNormalize($name, $value);
			}else{
				$this->setCast($name, $value, $schema[$name]);
			}			

		}
		
		 if(in_array($name, $associations)){
				$this->data[$name] = $value;
		}
	}

	private function setCast($name, $value, $type){
		if(array_key_exists($name, $this->data)){
			switch($type){
				case 'string':
					$this->castString($name, $value);
					break;
				case 'integer':
					$this->castInteger($name, $value);
					break;
				case 'datetime':
					$this->castDateTime($name, $value);
					break;
				case 'double':
					$this->castDouble($name, $value);
					break;
				default:
					return false;
			}
		}
	}

	private function castString($name, $value){
		if(array_key_exists($name, $this->data)){
			if($this->data[$name] !== $value){
				$this->dirty[$name] = (string)$value;
			}
			$this->data[$name] = (string)$value;
		}
	}

	private function castInteger($name, $value){
		if(array_key_exists($name, $this->data)){
			if($this->data[$name] !== $value){
				$this->dirty[$name] = (int)$value;
			}
			$this->data[$name] = (int)$value;
		}
	}

	private function castDouble($name, $value){
		if(array_key_exists($name, $this->data)){
			if($this->data[$name] !== $value){
				$this->dirty[$name] = (double)$value;
			}
			$this->data[$name] = (double)$value;
		}
	}

	private function castDateTime($name, $value){
		if(array_key_exists($name, $this->data)){
			if($this->data[$name] !== $value){
				if($value instanceof DateTime){
					$this->dirty[$name] = $value;
				}else{
					$this->dirty[$name] = new DateTime($value);
				}
			}

			if($value instanceof DateTime){
				$this->data[$name] = $value;
			}else{
				$this->data[$name] = new DateTime($value);
			}
		}
	}


	private function setNormalize($name, $value){
		if(array_key_exists($name, $this->data)){
			if($this->data[$name] !== $value){
				$this->dirty[$name] = $value;
			}
			$this->data[$name] = $value;
		}
	}

/**
* データベーステーブルの有無を調べる.
*
* 使用禁止
*
* @param string $tableName 有無を調べたいテーブル名
* @return boolean
*/
	public static function tableExists($tableName){
		return self::$connection->tableExists($tableName);
	}

/**
* データベーステーブルのスキーマ情報を取得.
*
* 使用禁止
*
* @param string $tableName スキーマを取得したいテーブル名
* @return array
*/
	public static function schema($tableName){
		return self::$connection->schema($tableName);
	}

/**
* レコードクラスが使用するテーブル名を返す.
*
* オーバーライド可能なメソッド
*
* 使用するテーブル名がクラス名の複数形でない時はオーバーライドする<br>
* ＳＴＩする時は必ずルートとなるクラスでオーバーライドする<br>
*
* プロパティではなくメソッドにした理由はget_called_class()が使えるから<br>
* プロパティでは継承先で常にテーブル名を定義しなくてはいけなくなる<br>
*
* @return string レコードクラスが使用するテーブル名
*/
	public static function tableName(){
		$fullName = get_called_class();
		$split = explode('\\', $fullName);
		$name = array_pop($split);
		return lcfirst(Util::plural($name));
	}

/**
* レコードクラス名を返す.
*
* @return string レコードクラス名
*/
	public static function className(){
		return get_called_class();
	}

/**
* テーブルのプライマリーキーの名前を返す.
*
* @return string プライマリーキー名
*/
	public static function primaryKey(){
		return static::$primaryKey;
	}

/**
* データベースドライバをセットする.
*
* @param Driver $connection TRW\Database\Driverクラスを継承したクラス
* @return void
*/
	public static function setConnection(Driver $connection){
		self::$connection = $connection;
	}

/**
* レコードオブジェクトのidをを返す.
*
* @return int|boolean　idが設定されていない場合はfalseを返す
*/
	public function id(){
		return $this->id ?: false;
	}

/**
* レコードオブジェクトが新たに作成されたかどうか検査する.
*
* レコードオブジェクトがデータベーステーブルに保存済みでないものは新しく<br* > 作成されたオブジェクトとみなす<br>
*
* @return boolean 新しく作成されたオブジェクトであればtrue
*/
	public function isNew(){
		return empty($this->data['id']);
	}

/**
* レコードオブジェクトのフィールドが変更されたか検査する.
*
* @return boolean 変更されていればtrue
*
*/
	public function isDirty(){
		return !empty($this->dirty) ? true : false;
	}

/**
* レコードオブジェクトのフィールドデータを返す.
*
* テストのためのメソッド 
*
* 使用禁止
*
* @return array
*/
	public function getData(){
		return $this->data;
	}

/**
* データベーステーブルのレコードの数を返す.
*
* @param string $column 数えたいテーブルのカラム名
* @return int $columnがnullだった場合すべてのレコード数を返す
*/
	public static function rowCount($column = null){
		$table = static::tableName();

		$rowCount = self::$connection->rowCount($table, $column);

		return $rowCount;
	}

/**
* 配列をフィルタリングして返す.
*
* @param array $filter 排除したいデータのキーリスト['name', 'user_id']<br>
* @param array $data　フィルタリングされるデータ<br>
* ['id'=>1, 'name'=>'foo', 'user_id'=>1, 'age'=>20]
* $return array フィルタリングされたデータ['id'=>1, 'age'=>20]
*/
	private static function filterData($filter, $data){
		$results = [];
		foreach($data as $k => $v){
			if(array_key_exists($k, $filter)){
				$results[$k] = $v;
			}
		}
		return $results;
	}

/**
* レコードクラスが使用するカラムのリスト.
*
*
* static::$useColumnをオーバーライドすると、<br>
* レコードクラスが使用できるカラムを制限することができる
*
* @return array 使用するカラムのリスト
*/
	public static function useColumn(){
		$useColumn = static::$useColumn;
		$columns = SchemaCollection::schema(static::tableName())
			->defaults();
		
		if($useColumn === null){
			return $columns;
		}
		if(array_key_exists('type', $columns)){
			$useColumn['type'] = $columns['type'];
		}

		$useColumn[static::$primaryKey] = 'int';

		$result = self::filterData($useColumn, $columns);

		return $result;
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
			if($STI !== __NAMESPACE__ . '\BaseRecord'){
				$result = $result + $STI::useColumn();
			}
			$STI = get_parent_class($STI);
		}	
		return $result;
	}

/**
* 新しいレコードオブジェクトを作成する.
*
* @param array $field　レコードオブジェクトのデフォルトの値<br>
* nullの場合テーブルのデフォルト値が反映される<br>
* 次の構造で渡さなければならない<br>
* $fileds = <br>
*  [ <br>
*   //fieldName => value <br>
*    'name' => 'foo' <br>
*  ];<br>
* @return TRW\ActiveRecord\BaseRecord BaseRecordを継承したクラス
* @throws \Exception ラッピングするクラスが見つからない場合
*/
	public static function newRecord($fields = null){
		if($fields === null){
			$fields = [];
		}

		$strategy = self::inheritanceType();
		$className = static::className();
		$fields = $strategy->newRecord($className, $fields);

		if(isset($fields['type'])){
			list($namespace, $class) = Util::namespaceSplit($className);
			$className = $namespace . '\\' . $fields['type'];
		}

		if(!class_exists($className)){
			throw new Exception('missing class '. $className);
		}

		$newRecord = new $className($fields);
		AssociationCollection::attach($newRecord, static::$associations);

		return $newRecord;
	}

/**
* 新しいレコードオブジェクトの作成と、データベーステーブルへの保存を行う.
*
* @param array $fields レコードオブジェクトのデフォルトのフィールド値<br>
* 次の構造で渡さなければならない
* $fileds = <br>
*  [<br>
*   //fieldName => value<br>
*    'name' => 'foo' <br>
*  ];<br>
* @return \TRW\ActiveRecord\BaseRecord|false レコードの保存に失敗した場合false
*/
	public static function create(array $fields = []){

		$newRecord = self::newRecord($fields);
		if($newRecord->save()){
			return $newRecord;
		}
		return false;
	}

/**
* レコードオブジェクトを一件読み込む.
*
* データべーステーブルから行を一件読み込みオブジェクトで
* ラッピングするが、<br>
* すでに読み込まれていた場合IdentityMapのキャッシュから読み込む<br>
* キャッシュにない場合はIdentityMapに保存する<br>
*
* @param int $id 読み込みたいレコードのid
* @return \TRW\ActiveRecord\BaseRecord　BaseRecordを継承したクラス
*/
	public static function read($id){

		$record = static::find([
			'where'=>[
				'field'=>static::$primaryKey,
				'comparision' =>'=',
				'value'=>$id
			]
		]);
		if(count($record) === 0){
			return false;
		}

		return $record[0];

	}

/**
* レコードオブジェクトのデータをデータベースに保存する.
*
* そのオブジェクトが新たに作成されたものであればinsertする<br>
* 既に保存済みのものであればupdateする<br>
*
* オブジェクトにアソシエーションが定義されていれば<br>
* AssociationCollectionによって関連オブジェクトデータもsaveされる
* @return boolean saveに失敗するとfalse　成功するとtrue
*/
	public function save(){
		AssociationCollection::associations(get_class($this), static::$associations);
		
		$parentSaved = AssociationCollection::saveParents($this);

		if($parentSaved){
	
			$strategy = self::inheritanceType();

			$currentSaved = $strategy->save($this);

			if($currentSaved){
				IdentityMap::set(get_class($this), $this->id,$this);
				return AssociationCollection::saveChilds($this);
			}
			return false;	
		}

		return false;

	}

/**
* レコードオブジェクトのデータを保存する。失敗した場合ロールバックを行う.
*
*
* @return boolean 保存に成功すればture 失敗すればfalse
*/
	public function saveAtomic(){
		self::$connection->begin();
		if(!$this->save()){
			self::$connection->rollback();
			return false;
		}
		self::$connection->commit();
		return true;
	}
	

/**
* レコードオブジェクトをデータベーステーブルに保存するとき、その値を検査する.
*
* オーバーライド可能なメソッド
*
* このメソッドは必要に応じてオーバーライドする<br>
* 必ずbool値を返すように実装する<br>
* 必要に応じてsetErrorでエラーメッセージをセットする<br>
*
* @return boolean
*/
	public function validate(){
		return true;
	}

/**
* バリデーションエラー時にエラーメッセージをセット出来る.
*
* validateメソッドをオーバーライドする時必要に応じてエラーメッセージをセットする
*
* @return mixed エラーメッセージ
*/
	public static function setError($error){
		self::$error = $error;
	}

/**
* エラーメッセージを取得する.
*
* バリデーションエラーが起きたとき必要に応じてエラーメッセージを取得する.
*
* @return mixid エラーメッセージがないときにnull または取得後バリデーションエラーがない時null
*/
	public static function flashError(){
		$error = self::$error;
		self::$error = null;
		return $error;
	}


/**
* レコードオブジェクトのデータをデータベーステーブルに更新する.
*
*
* @param array $fields 更新したいデータ
* @return boolean 挿入に失敗するとfalse 成功するとture
* @throws \Exception レコードオブジェクトのidが設定されていない時
*/
	public function updateAttr(array $fields){
		foreach($fields as $k => $v){
			$this->{$k} = $v;
		}
		$strategy = self::inheritanceType();
		return $strategy->save($this);

	}

/**
* レコードオブジェクトをデータベーステーブルから削除する.
*
* @return boolean 削除に成功すればture 失敗すればfalse
*/
	public function delete(){
		$id = $this->id();
		if($id === false){
			return false;
		}

		$success = self::$connection->delete(
			static::tableName(),
			[
				'where'=>[
					'field'=>static::primaryKey(),
					'comparision'=>'=',
					'value'=>$id
				]
			]
		);

		IdentityMap::set(get_class($this), $this->id, null);

		return $success;
	}

/**
* レコードオブジェクトを取得する
*
* 条件にマッチしたレコードオブジェクトのリストを取得する
* 条件は次のように定義する<br>
* $conditions = <br>
*  [ <br>
*     'where' => [ <br>
*        'field' => 'age', <br>
*        'comparision' => '>', <br>
*        'value' => 20 <br>
*     ],<br>
*     'limit' => 5, <br>
*     'offset' => 2, <br>
*     'order' => 'id DESC'<br>
* ]; <br>
*
* レコードクラスにアソシエーションが定義されていた場合<br>
* AssociationCollectionによって関連レコードオブジェクトがアタッチされる<br>
*
* @param array $conditions 引数を省略した場合すべてのレコードオブジェクトのリストが取得される
* @return array レコードオブジェクトのリスト
*/
	public static function find($conditions = []){

		$strategy = self::inheritanceType();

		$result = $strategy->find(static::className(), $conditions);
		
		$resultSet = [];
		foreach($result as $rowData){
			$hydrate = static::className();
			if(isset($rowData['type'])){
				list($namespace, $class) = Util::namespaceSplit($hydrate);
				$hydrate = $namespace . '\\' . $rowData['type'];
			}
			$record = IdentityMap::get($hydrate, $rowData[static::$primaryKey]);
			if($record === false){
				$record = self::hydrate($rowData, $hydrate);
				IdentityMap::set($hydrate, $record->id, $record);
			}
			AssociationCollection::attach($record, $hydrate::$associations);
			$resultSet[] = $record;
		}
		return $resultSet;
	}

	private static function inheritanceType(){
		if(static::$inheritance === false){
			$strategy ='\TRW\ActiveRecord\Strategy\BasicStrategy';
		}else{
			$strategy = "\TRW\ActiveRecord\Strategy\\" . static::$inheritance . 'Strategy';
		}
		return new $strategy(
			new RecordOperator(self::$connection, new RecordProperty(static::className())));
	}

/**
* レコードオブジェクトを全件取得する.
*
* このメソッドはfind($conditions = [])を内部で使用している
* 
* @return array レコードオブジェクトのリスト
*/
	public static function findAll(){	
		$resultSet = self::find([]);
		return $resultSet;
	}

/**
* レコードオブジェクトのリストを取得する.
*
* このメソッドはfind($condtions = [])を内部で使用している
*
* @param string $name 検索したいフィールド名
* @param string $comparistion 比較演算子
* @param mixid $value 検索したい値
* @return array レコードオブジェクトのリスト 
*/
	public static function findBy($name, $comparision, $value){
		$resultSet = self::find(
			[
				'where'=>[
					'field'=>$name,
					'comparision'=>$comparision,
					'value'=>$value
				]
			]
		);
		
		return $resultSet;
	}

/**
* レコードオブジェクトのリストを取得する
*
* このメソッドは内部でfind($conditions = [])を使用している
*
* @param int $limit 取得したいレコードのリミット値
* @param int $offset 取得したいレコードのオフセット値
* @return array レコードオブジェクトのリスト
*/
	public static function limit($limit, $offset = null){	
		$resultSet = self::find(
			[
				'limit'=>$limit,
				'offset'=>$offset	
			]
		);
		
		return $resultSet;
	}

/**
* 中間テーブルに関連情報を挿入するためのメソッド.
* 
* 使用禁止
*
* @param string $tableName 中間テーブルの名前
* @param array 挿入したいデータ
* @access private 
* @return boolean 挿入に成功すればture　失敗すればfalse
*/
	public static function insertAll($tableName, $values){
		$success = self::$connection->insert($tableName, $values);

		if($success){
			return true;
		}else{
			return false;
		}
	}


/**
* 中間テーブルの関連情報を更新するためのメソッド.
* 
* 使用禁止
*
* @param string $tableName 中間テーブルの名前
* @param array $fields 更新したいデータ
* @param int $whereId 更新したいレコードのid
* @access private 
* @return boolean 更新に成功すればture　失敗すればfalse
*/
	public static function updateAll($tableName, $fields, $whereId){
		$success = self::$connection->update(
			$tableName,
			$fields,
			[
				'where'=>[
					'field'=>static::primaryKey(),
					'comparision'=>'=',
					'value'=>$whereId
				]
			]
		 );

		return $success;
	}


/**
* 中間テーブルに関連情報を削除するためのメソッド.
* 
* 使用禁止
*
* @param string $tableName 中間テーブルの名前
* @param sting $whereName 削除するために検索するフィールド名
* @param string $comparision 比較演算子
* @access private 
* @return boolean 削除に成功すればture　失敗すればfalse
*/
	public static function deleteAll($tableName, $whereName, $comparision, $value){
		$success = self::$connection->delete(
			$tableName,
			[
				'where'=>[
					'field'=>$whereName,
					'comparision'=>$comparision,
					'value'=>$value
				]
			]
		);

		return $success;
	}

/**
* 使用禁止
*
*
*
*/
	public static function whereAll($from, $conditions, $hydrate = ''){
		$statement = self::$connection->read(
			$from,
			['*'],
			$conditions
		);

		if($statement !== false){

			if($hydrate !== ''){

				$resultSet = [];
				foreach($statement as $rowData){
					$record = self::hydrate($rowData, $hydrate);
					$resultSet[] = $record;
				}
				return $resultSet;
			}

			return $statement;
		}

		return false;
	}

/**
* 使用禁止
*
*
*
*/
	private static function hydrate($rowData, $recordClass){
		$pk = static::$primaryKey;

		if(class_exists($recordClass)){
			$record = IdentityMap::get($recordClass, $rowData[$pk]);

			if($record !== false){
				return $record;
			}

			$newRecord = new $recordClass($rowData);
//			$newRecord->id = $rowData[$pk];

			IdentityMap::set($recordClass, $newRecord->id, $newRecord);

			return $newRecord;
		}else{
			throw new Exception('class not found ' . $recordClass);
		}

	}



}

















