<?php
namespace TRW\ActiveRecord;

use PDO;
use Exception;
use TRW\ActiveRecord\BaseRecord;

/**
* このクラスはレコードクラスが使用しているテーブルのスキーマを表すクラス
*
* @access private
*/
class Schema {

/**
* スキーマの型を定義している.
*/
	const
		INTEGER = 'integer',
		DOUBLE = 'double',
		FLOAT = 'double',
		STRING = 'string',
		DATETIME = 'datetime';

/**
* データベーステーブル名.
*
* @var string 
*/
	private $table;

/**
* テーブルのスキーマ.
*
* 次の構造をしている
*	$schema = [
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
*	@var array
*/
	private $schema;

/**
* カラムの名前と値.
* 次の構造をしている
* $columns =
* [
*  'id' => 1,
*  'name' => 'foo'
*  'age' => 20,
* ]
*
* @var array;
*/
	private $columns;

/**
* カラムの名前とデフォルト値.
*
* @var array
*/
	private $defaults;

	public function __construct($table){
		$this->table = $table;
	}

/**
* テーブル名を返す.
*
* @param string $table 
* @return string @param
*/
	public function table($table = null){
		if($table !== null){
			return $this->table = $table;
		}
		return $this->table;
	}

/**
* データベーステーブルのスキーマを返す.
*
* @return array データベーステーブルのスキーマ
*/
	public function schema(){
		if(empty($this->schema)){
			 $this->schema = BaseRecord::schema($this->table);
		}
		return $this->schema;
	}

/**
* データベーステーブルのカラムとその情報を返す.
*
*
* @return array カラムとその情報
*/
	public function columns(){
		if(empty($this->columns)){

			if(empty($this->schema)){

				$this->schema();

				if(empty($this->schema)){
						throw new Exception('missing table from ' . $this->table);
				}
			}
			
			foreach($this->schema as $row){
				$type = $row['Type'];
				$field = $row['Field'];
				if(preg_match('/int\(.*\)/',$type) 
						|| preg_match('/bigint\(.*\)/', $type)
						|| preg_match('/tinyint\(.*\)/', $type) ){
					$result[$field] = self::INTEGER;
				}else if($type === 'float' || $type === 'double'){
					$result[$field] = self::DOUBLE;
				}else if(preg_match('/char\(.*\)/', $type) ||
					 preg_match('/[tiny|midium|long]text/', $type) || $type === 'text' ){
					$result[$field] = self::STRING;
				}else if($type === 'timestamp'){
					$result[$field] = self::DATETIME;
				}
			}

			$this->columns = $result;
		}
					
		return $this->columns;
	}

/**
* データベーステーブルのカラムとその初期値を返す.
*
* @return array データベーステーブルのカラムとその初期値
*/
	public function defaults(){

		if(empty($this->defaults)){

			if(empty($this->schema)){
				$this->schema();		
				if(empty($this->schema)){
						throw new Exception('missing table from ' . $this->table);
				}
			}
			
			foreach($this->schema as $row){
				$this->defaults[$row['Field']] = $row['Default'];
			}

		}
		return $this->defaults;
	}



}



















