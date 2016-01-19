<?php
namespace TRW\ActiveRecord;

class IdentityMap {

	private static $map;

	public static function map(){
		return self::$map;
	}

	public static function clearAll(){
		self::$map = [];
	}

	public static function getAllRecord($recordName){
		if(!empty(self::$map[$recordName])){
			return self::$map[$recordName];
		}else{
			return false;
		}
	}

	public static function set($recordName, $id, $record){
		$records = [];
		if(!empty(self::$map[$recordName])){
			$records = self::$map[$recordName];
		}
		$resultRecords = [$id => $record] + $records;

		self::$map[$recordName] = $resultRecords;

	}

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
