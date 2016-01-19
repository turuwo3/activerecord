<?php

namespace TRW\ActiveRecord;

class SchemaCollection {

	private static $map;

	private function __construct(){}

	public static function schema($tableName){
		if(empty(self::$map[$tableName])){
			$schema = new Schema($tableName);
			self::$map[$tableName] = $schema;
			return self::$map[$tableName];
		}else{
			return self::$map[$tableName];
		}
	}

	public static function clear(){
		self::$map = [];
	}
						


}
