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


class BaseRecord {



	private static $connection;

	protected static $associations;

	protected static $useColumn;

	protected static $primaryKey = 'id';

	private static $error;

	private $data;

	private $dirty;

	protected $id;


	private function __construct($fieldData = null){
		if($fieldData !== null){
			$this->setData($fieldData);	
		}
	}

	private function setData($fieldData){
		foreach($fieldData as $prop => $value){
			if($prop === static::$primaryKey){
				continue;
			}
			$this->data[$prop] = $value;
		}
	}

	public function &__get($name){
		return $this->get($name);
	}


	public function &get($name){
		$value = null;
		
		if($name === static::$primaryKey){
			$value = $this->id;
		}else if(isset($this->data[$name])){
			$value =& $this->data[$name];
	}

		return $value;
	}


	public function __set($name, $value){
		$this->set($name, $value);
	}
	
	protected function set($name, $value){
		if($name === static::primaryKey()){
			return;
		}

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

	public static function tableExists($tableName){
		return self::$connection->tableExists($tableName);
	}

	public static function schema($tableName){
		return self::$connection->schema($tableName);
	}

/*
*	protected static function  tableName();
*	使用するテーブル名がクラス名の複数形でない時はオーバーライドする
*	ＳＴＩする時は必ずルートとなるクラスでオーバーライドする
*/

/*
*	プロパティではなくメソッドにした理由はget_called_class()が使えるから
*	プロパティでは継承先で常にテーブル名を定義しなくてはいけなくなる
*/
	public static function tableName(){
		$fullName = get_called_class();
		$split = explode('\\', $fullName);
		$name = array_pop($split);
		return lcfirst(Util::plural($name));
	}


	public static function className(){
		return get_called_class();
	}


	public static function primaryKey(){
		return static::$primaryKey;
	}

	public static function setConnection(Driver $connection){
		self::$connection = $connection;
	}

	public function id(){
		return $this->id ?: false;
	}

	public function isNew(){
		return empty($this->id);
	}


	public function isDirty(){
		return !empty($this->dirty) ? true : false;
	}

	public function getData(){
		return $this->data;
	}


	private static function filterData($filter, $data){
		$results = [];
		foreach($data as $k => $v){
			if(array_key_exists($k, $filter)){
				$results[$k] = $v;
			}
		}
		return $results;
	}

	protected static function useColumn(){
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


	public static function newRecord($fields = null){
		if($fields === null){
			$fields = [];
		}

		if(!empty($fields['type']) ||
			get_parent_class(static::className())
			 && get_parent_class(static::className()) !== __NAMESPACE__ . '\BaseRecord'){

			$STI = !empty($fields['type']) ? $fields['type'] : static::className();
			$recordClass = $STI;
			$result = self::loadParentColumns($STI);	
			$fields = self::filterData($result, $fields);		
			$fields = $fields + $result;
		}else{
			$tableName = static::tableName();
			$defaults = schemaCollection::schema($tableName)
				->defaults();
			$fields = $fields + $defaults;
			$fields = self::filterData(self::useColumn(), $fields);
			$recordClass = static::className();
		}

		if(!class_exists($recordClass)){
			throw new Exception('missing class '. $recordClass);
		}

		$newRecord = new $recordClass($fields);
		AssociationCollection::attach($newRecord, static::$associations);

		return $newRecord;
	}


	public static function create(array $fields = []){

		$newRecord = self::newRecord($fields);
		if($newRecord->save()){
			return $newRecord;
		}
		return false;
	}


	public static function read($id){
		$record = IdentityMap::get(static::className(), $id);

		if($record !== false){
			return $record;
		}

		$rowData = self::load($id)->fetch();
		if($rowData !== false){

			$recordClass = static::className();

			if(!empty($rowData['type'])){
				$recordClass = $rowData['type'];
				$record = IdentityMap::get($recordClass, $id);
				if($record !== false){
					return $record;
				}
				$rowData = self::loadParent($rowData);
			}

			$newRecord = self::hydrate($rowData, static::className());

			return $newRecord;
		}

		return false;

	}


	private static function load($id){
		$rowData = self::$connection->read(
			static::tableName(),
			array_keys(static::useColumn()),
			[
				'where'=>[
					'field'=>static::primaryKey(),
					'comparision'=>'=',
					'value'=>$id
				]
			]
		);

		return $rowData;
	}


	private static function loadParent($rowData){
		$STI = $rowData['type'];
		$result = [];
		while($STI !== false){
			if($STI !== __NAMESPACE__ . '\BaseRecord'){
				$result = $result + $STI::load($rowData['id'])->fetch();
			}
			$STI = get_parent_class($STI);
		}	
		return $result;
	}


	public function save(){
		AssociationCollection::associations(get_class($this), static::$associations);
		
		$parentSaved = AssociationCollection::saveParents($this);

		if($parentSaved){
	
			if(array_key_exists('type', $this->data)){
				$currentSaved = $this->saveParentClass();
			}

			if($this->isNew()){
				$currentSaved = $this->insert($this->data);
			}else{
				$currentSaved = $this->update($this->dirty);
			}

			if($currentSaved){
				return AssociationCollection::saveChilds($this);
			}
			return false;	
		}

		return false;

	}

	
	public function saveAtomic(){
		self::$connection->begin();
		if(!$this->save()){
			self::$connection->rollback();
			return false;
		}
		self::$connection->commit();
		return true;
	}


	private function saveParentClass(){
		if(empty($this->data['type'])){
			$type = static::className();
			$this->data['type'] = $type;
		}

		if(!class_exists($this->data['type'])){
			throw new Exception('missing type '.$this->data['type']);
		}

		if($this->isNew()){
			$default = SchemaCollection::schema(static::tableName())
				->defaults();
			$this->insert($default);
			$success = $this->updateParentClass();
		}else{
			$success = $this->updateParentClass();
		}
	}


	private function updateParentClass(){
		$STI = static::className();
			while($STI !== false){
				if($STI !== __NAMESPACE__ . '\BaseRecord'){	
					$STIData = self::filterData($STI::useColumn(), $this->data);
					$this->update($STIData);
				}
			$STI = get_parent_class($STI);
		}	
		return true;
	}


	protected static function saveTargetColumns($data){
		$columns = SchemaCollection::schema(static::tableName())
			->columns();
		unset($columns[static::$primaryKey]);

		$results = self::filterData($columns, $data);

		return $results;
	}


	protected function validate(){
		return true;
	}

	public static function setError($error){
		self::$error = $error;
	}

	public static function flashError(){
		$error = self::$error;
		self::$error = null;
		return $error;
	}

	private function insert($data){

		if(!$this->validate()){
			return false;
		}

		$success = self::$connection->insert(static::tableName(), self::saveTargetColumns($data));

		if($success){

			$id = self::$connection->lastInsertId();
			$this->id = $id;
			IdentityMap::set(get_class($this), $this->id, $this);

			return true;
		}else{
			return false;
		}

	}


	private function update($fields = null){

		if($fields === null){
			$fields = $this->data;
		}

		if(!$this->validate()){
			return false;
		}

		$fields = self::saveTargetColumns($fields);

		if(empty($this->id)){
			throw new Exception('missing primarykey');
		}

		$success = self::$connection->update(
			static::tableName(),
			$fields,
			[
				'where'=>[
					'field'=>static::primaryKey(),
					'comparision'=>'=',
					'value'=>$this->id
				]
			]
		 );

		if($success){
			$this->setData($fields);	
			IdentityMap::set(get_class($this), $this->id, $this);

			return true;
		}else{
			return false;
		}

	}


	public function updateAttr(array $fields){
		return $this->update($fields);

	}


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


	public static function insertAll($tableName, $values){
		$success = self::$connection->insert($tableName, $values);

		if($success){
			return true;
		}else{
			return false;
		}
	}


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


	public static function findAll(){	
		$from = static::tableName();
		$hydrate = static::className();

		return  self::whereAll($from,
			 [],
			 $hydrate);
	}

	public static function findBy($name, $comparision, $value){
		$from = static::tableName();
		$hydrate = static::className();

		return self::whereAll(
			$from,
			[
				'where' => [
					'field' => $name,
					'comparision' => $comparision,
					'value' => $value
				],
			],
			$hydrate);
	}

	public static function limit($limit, $offset = null){
		$statement = self::$connection->read(
			static::tableName(),
			['*'],
			[
				'limit'=>$limit,
				'offset'=>$offset
			]
		);

		if($statement !== false){
			$resultSet = [];
			foreach($statement as $rowData){
				$resultSet[] = self::hydrate($rowData, static::className());
			}
			return $resultSet;
		}
		return false;
	}


	public static function whereAll($from, $conditions, $hydrate = ''){
		$statement = self::$connection->read(
			$from,
			['*'],
			$conditions
		);

		if($statement !== false){

			if($hydrate !== ''){
				if(!class_exists($hydrate)){
					throw new Exception('missing record ' . $hydrate );
				}
				$resultSet = [];
				foreach($statement as $rowData){
					$resultSet[] = self::hydrate($rowData, $hydrate);
				}
				return $resultSet;
			}

			return $statement;
		}

		return false;
	}


	protected static function hydrate($rowData, $recordClass){
		$pk = static::$primaryKey;

		if(class_exists($recordClass)){
			$newRecord = new $recordClass($rowData);
			$newRecord->id = $rowData[static::$primaryKey];
			
			AssociationCollection::attach($newRecord, static::$associations);

			IdentityMap::set($recordClass, $newRecord->id, $newRecord);

			return $newRecord;
		}else{
			throw new Exception('class not found ' . $recordClass);
		}

	}



}

















