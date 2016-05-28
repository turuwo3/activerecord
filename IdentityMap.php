<?php
namespace TRW\ActiveRecord;

/**
* レコードオブジェクトをキャッシュするクラス.
*
* このクラスは\TRW\ActiveRecord\BaseRecordが内部で使用しているクラス。一般の開発者への使用を前提としていない
*
* @accsess private
*/
class IdentityMap {

/**
* レコードクラスのキャッシュ
* 次の構造をしている
* $map = 
* [
*   'App\Model\User' =>[
*     App\Model\User Object,
*      :
*      :
*   ],
*   'App\Model\Profile' => [
*     App\Model\Profile Object,
*      :
*      :
*   ],
*    :
*    :
* ]
*
*/
	private static $map;

/**
* 保持しているキャッシュを全て返す.
*
* @return array 保持しているキャッシュ
*/
	public static function map(){
		return self::$map;
	}

/**
* 保持しているキャッシュを全てクリアする.
*
* @return void
*/
	public static function clearAll(){
		self::$map = [];
	}

/**
* レコード毎に保持しているキャッシュ全てを返す.
*
* @return array|false レコードがキャッシュを保持していなければfalse
*/
	public static function getAllRecord($recordName){
		if(!empty(self::$map[$recordName])){
			return self::$map[$recordName];
		}else{
			return false;
		}
	}

/**
* キャッシュをセットする.
*
* @param string $recordName レコードクラス名
* @param int $id レコードオブジェクトのid
* @param \TRW\ActiveRecord\BaseRecord $record　レコードオブジェクト
*/
	public static function set($recordName, $id, $record){
		$records = [];
		if(!empty(self::$map[$recordName])){
			$records = self::$map[$recordName];
		}
		$resultRecords = [$id => $record] + $records;

		self::$map[$recordName] = $resultRecords;

	}

/**
* レコード毎に保持しているキャッシュを一件返す.
*
* @param string $recordName レコードクラス名
* @param int $id 取得したいキャッシュのid
* @return \TRW_ActiveRecord\BaseRecord|false レコードのキャッシュ　見つからなければfalse
*/
	public static function get($recordName, $id){
		if(!empty(self::$map[$recordName])){
			if(!empty(self::$map[$recordName][$id])){
				return self::$map[$recordName][$id];
			}else{
				return false;
			}
		}
		return false;
	}


}
