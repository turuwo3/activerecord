<?php
namespace TRW\ActiveRecord;

/**
* このクラスはレコードクラス毎のスキーマオブジェクトを保持するクラス.
*
* このクラスは\TRW\ActiveRecord\BaseRecordが<br>
* 内部で使用するためだけのクラスのため<br>
* 一般開発者はこのクラスを使用してはならない
*
* @access private
*/
class SchemaCollection {

/**
* レコードクラス毎のスキーマオブジェクトを保持する.
* 
* $map = <br>
*  [<br>
*    'User' => new Shema(User::tableName()),<br>
*   'Comment' => new Schema(Comment::tableName());<>br
*      :<br>
*      :<br>
* 	  :<br>
*  ];<br>
*
*@var array
*/
	private static $map;

	private function __construct(){}

/**
* テーブルのスキーマオブジェクトを返す.
*
* スキーマオブジェクトが既にあれば、そのスキーマオブジェクトを返す<br>
* なければ保持する
* 
* @return \TRW\ActiveRecord\Schema
*/
	public static function schema($tableName){
		if(empty(self::$map[$tableName])){
			$schema = new Schema($tableName);
			self::$map[$tableName] = $schema;
			return self::$map[$tableName];
		}else{
			return self::$map[$tableName];
		}
	}

/**
* スキーマのコレクションをクリアする.
*
* @return void
*/
	public static function clear(){
		self::$map = [];
	}
						


}
