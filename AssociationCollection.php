<?php
namespace TRW\ActiveRecord;

use TRW\ActiveRecord\Association\BelongsTo;
use TRW\ActiveRecord\Association\HasMany;
use TRW\ActiveRecord\Association\HasOne;
use TRW\ActiveRecord\Association\BelongsToMany;

class AssociationCollection {

	private static $associations;

	private function __construct(){}

	
	public static function removeAssotiation($name){
		self::$associations[$name] = null;
	}

	public static function clearAll(){
		self::$associations = [];
	}

	private static function BelongsTo($own, $target){
		return new BelongsTo($own, $target);
	}

	private static function HasOne($own, $target){
		return new HasOne($own, $target);
	}

	private static function HasMany($own, $target){
		return new HasMany($own, $target);
	}

	private static function BelongsToMany($own, $target){
		return new BelongsToMany($own, $target);
	}

/*
*	名前空間が動的インスタンスの生成の影響を受けないため、
*	メソッド内で直接生成した
*/
	public static function associations($own = null, $associationMap = null){

		if($associationMap === null || $own === null){
			return self::$associations;
		}

		if(!empty(self::$associations[$own])){
			return self::$associations[$own];
		}

		foreach($associationMap as $type => $targets){
				foreach($targets as $target ){
					self::$associations[$own][$type][] = self::$type($own, $target);
				}
		}

		return self::$associations[$own];
	}

	
	public static function attach($record, $associationMap){
		if($record === null){
			throw new Exception('arguments error record is null');
		}
		if($associationMap === null){
			return;
		}

		$recordClass = get_class($record);
		$associations = self::associations($recordClass, $associationMap);

		foreach($associations as $sourceTable => $assoc){
			foreach($assoc as $type){
				$target = $type->target();
				$record->$target = $type->find($record);
			}
		}
	}
	

	public static function hasAssociatedTarget($tableName){
		$associations = self::get($tableName);
		if(empty($associations)){
			return [];
		}
		
		$result = [];
		foreach($associations as $table => $assoc){
				foreach($assoc as $type){
					$result[] = $type->target();
				}
		}
		return $result;
	}
	

	public static function get($tableName){
		if(empty(self::$associations[$tableName])){
					return false;
		}
		return self::$associations[$tableName];
	}




	private static function saveAssociations($record, $associations, $owningSide){

		foreach($associations as $assocs){
				foreach($assocs as $type){
					if($type->isOwningSide($record) !== $owningSide){
						continue;
					}
					if(!$type->save($record)){
						return false;
					}
				}
		}
			

		return true;

	}


	public static function saveParents($record){
		if(empty(self::$associations[get_class($record)])){
			return true;
		}
		$associations = self::get(get_class($record));
		return self::saveAssociations($record, $associations,false);
	}


	public static function saveChilds($record){
		if(empty(self::$associations[get_class($record)])){
			return true;
		}
		$associations = self::get(get_class($record));
		return self::saveAssociations($record, $associations,true);
	}





}






	
