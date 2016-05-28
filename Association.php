<?php
namespace TRW\ActiveRecord;

use Exception;
use TRW\ActiveRecord\Util;

/**
* このクラスはAssociationのための基底クラス.
*
* HasOne HasMany BelongsTo BelongsToManyはこのクラスを継承している 
*
* @access private
*/
abstract class Association {

/**
* レコードクラスの名前空間.
* @var string
*/
	private $recordNamespace;

/**
* 関連元のレコードクラス.
*
* @var string
*/
    private $source;

/**
* 関連先のレコードクラス.
*
* @var string
*/
	private $target;

	private $option = [];
	    
	public function __construct($source, $target, $option = []){
		$this->source = $source;
		$this->target = $target;
		$this->option = $option;
	}

/**
* 関連元のレコードクラス名を返す.
*
* @return string 関連元のレコードクラス名
*/					   
	public function source(){
		return $this->source;
	}
								   
/**
* 関連先のレコードクラス名を返す.
*
* @return string 関連先のレコードクラス名
*/					   
	public function target(){
		return $this->target;
	}
	
	protected function mergeOption($conditions){
		$conditions = array_merge($conditions, $this->option);

		return $conditions;
	}

	public function getOption($key = null){
		if($key === null){
			return $this->option;
		}
		if(empty($this->option[$key])){
			return false;
		}
		return $this->option[$key];
	}
					
/**
* 関連先のレコードオブジェクトを取得する
*
* @param \TRW\ActiveRecord\BaseRecord  $record 関連元のレコードオブジェクト
* @return \TRW\ActiveRecord\BaseRecord 関連先のレコードオブジェクト
*/						   
	abstract public function find($record);

/**
* 関連元のレコードかか関連先のレコードか判断する
*
* @param TRW\ActiveRecord\BaseRecord $tableName　レコードオブジェクト 
* @return boolean @param $tableNameが関連元ならture そうでないならfalse
*/
	abstract public function isOwningSide($tableName);

/**
* 関連先のレコードオブジェクトを保存する.
*
* @param \TRW\ActiveRecord\BaseRecord $record 関連元のレコードオブジェクト
* @return boolean 保存に成功すればtrue 失敗すればfalseを返す
*/	
	abstract public function save($record);

/**
* 与えられたクラス名に適切な名前空間名を付けて返す.
*
* @param string $name クラス名
* @return string 名前空間付きクラス名
*/
	public function recordNamespace($name){
		if($this->recordNamespace === null){
			$source = $this->source;
			$parts = explode('\\', $source);

			array_pop($parts);

			$namespace = implode('\\', $parts);

			$this->recordNamespace = $namespace;
		}

		return $this->recordNamespace . '\\' . $name;
	}

/**
* レコードクラスが使用しているテーブル名を返す.
*
* @param string レコードクラス名
* @return そのレコードクラスが使用しているテーブル名
*/
	protected function createTableName($source){
		list(, $class) = Util::namespaceSplit($source);
		return Util::plural(lcfirst($class));
	}

/**
* テーブルの外部キーを返す.
*
* @param string $name レコードクラス名
* @return そのレコードが使用しているテーブルの外部キー名
*/																 
	protected function createForeignKey($name){
		list(, $class) = Util::namespaceSplit($name);

		return lcfirst($class) . '_id';
	}

																		 

}







