<?php
namespace TRW\ActiveRecord;

/**
* このクラスはレコードクラス毎のスキーマオブジェクトを保持するクラス.
*
* このクラスは\TRW\ActiveRecord\BaseRecordがないぶで使用するためだけのクラスのため一般開発者はこのクラスを使用してはならない
*
* @access private
*/
class SchemaCollection {

/**
* レコードクラス毎のスキーマオブジェクトを保持する
* 
* $map = 
* [
*  'User' => new Shema(User::tableName()),
*  'Comment' => new Schema(Comment::tableName());
*     :
*     :
*	  :
* ];
*
*@var array
*/
	private static $map;

	private function __construct(){}

/**
* テーブルのスキーマオブジェクトを返す.
*
* スキーマオブジェクトが既にあれば、そのスキーマオブジェクトを返す
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
