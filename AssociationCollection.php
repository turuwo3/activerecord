<?php
namespace TRW\ActiveRecord;

use TRW\ActiveRecord\Association\BelongsTo;
use TRW\ActiveRecord\Association\HasMany;
use TRW\ActiveRecord\Association\HasOne;
use TRW\ActiveRecord\Association\BelongsToMany;

/**
* このクラスはレコードクラスの関連情報の保持と関連するレコードオブジェクトの保存を行う.
*
* このクラスは\TRW\ActiveRecord\BaseRecordが<br>
* 内部で利用するためだけのクラスのため<br>
* 一般開発者はこのクラスを使用してはならない
*
* @access private
*/
class AssociationCollection {

/**
* レコードクラス毎のアソシエーション情報を保持している.
*
*その構造は以下のようになっている<br>
* $assoiations = <br>
* [
*   'User' => [ <br>
*      'type' => [ <br>
*	    new HasOne('App\Model\User', 'App\Model\Profile'),<br>
*		new HasMany('App\Model\User', 'App\Model\Comment') <br>
*	  ]<br>
*   ],<br>
*	'Profile' => [<br>
*	  'type' => [<br>
*	    new BelongsTo('App\Model\Profile', 'App\Model\User')<br>
*	  ]<br>
*	],<br>
*	'Comment' => [ <br>
*	  'type' => [ <br>
*	    new BelongsTo('App\Model\Comment', 'App\Model\User')<br>
*	  ]<br>
*	]<br>
* ]<br>
*
*
* @var array レコードクラス毎のアソシエーション情報
*/
	private static $associations;

	private function __construct(){}

/**
* 指定したレコードクラスののアソシエーション情報をクリアする.
*
* @param string $name レコードクラス名
* @return void
*/	
	public static function removeAssotiation($name){
		self::$associations[$name] = null;
	}

/**
* アソシエーション情報を全てクリアする
*
* @return void
*/
	public static function clearAll(){
		self::$associations = [];
	}

/**
* BelongsToオブジェクトを生成する.
*
* @param string $own 関連を定義したクラス名
* @param string $target 関連で指定されたクラ名ス
* @return \TRW\ActiveRecord\Association\BelongsTo
*/
	private static function BelongsTo($own, $target, $option){
		return new BelongsTo($own, $target, $option);
	}

/**
* HasOneオブジェクトを生成する.
*
* @param string $own 関連を定義したクラス名
* @param string $target 関連で指定されたクラ名ス
* @return \TRW\ActiveRecord\Association\HasOne
*/
	private static function HasOne($own, $target, $option){
		return new HasOne($own, $target, $option);
	}

/**
* HasManyオブジェクトを生成する.
*
* @param string $own 関連を定義したクラス名
* @param string $target 関連で指定されたクラ名ス
* @return \TRW\ActiveRecord\Association\HasMany
*/
	private static function HasMany($own, $target, $option){
		return new HasMany($own, $target, $option);
	}

/**
* BelongsToManyオブジェクトを生成する.
*
* @param string $own 関連を定義したクラス名
* @param string $target 関連で指定されたクラ名ス
* @return \TRW\ActiveRecord\Association\BelongsToMany
*/
	private static function BelongsToMany($own, $target, $option){
		return new BelongsToMany($own, $target, $option);
	}

/**
* レコードクラスのアソシエーション情報から適切なアソシエーションクラスを生成する.
*
* 名前空間が動的インスタンスの生成の影響を受けないため、<br>
* メソッド内で直接生成した
*
* @param string $own アソシエーションを定義したクラス
* @param array $associationMap　アソシエーション情報
* @return array アソシエーション情報
*/
	public static function associations($own = null, $associationMap = null){

		if($associationMap === null || $own === null){
			return self::$associations;
		}

		if(!empty(self::$associations[$own])){
			return self::$associations[$own];
		}

		foreach($associationMap as $type => $targets){
				foreach($targets as $target => $option){
					self::$associations[$own][$type][] = self::$type($own, $target, $option);
				}
		}

		return self::$associations[$own];
	}

/**
* 関連するレコードオブジェクトを取得する.
*
* @param \TRW\ActiveRecord\BaseRecord $record レコードオブジェクト
* @param array $associationMap 関連レコード情報
* @throws \Exception @param $record @param $associationMapがnullの時
*/	
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
	
/**
* 対象のレコードクラスの関連レコードクラス名を取得する.
*
* @param string $tableName レコードクラス名
* @return array 関連するレコードクラス名リスト
*/
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
	
/**
* レコードクラスの関連情報を取得する.
*
* @param 関連を取得したいレコードクラス名
* @return boolean|array 関連データがあればarray　なければfalse 
*/
	public static function get($tableName){
		if(empty(self::$associations[$tableName])){
					return false;
		}
		return self::$associations[$tableName];
	}

/**
* レコードオブジェクト保持している関連レコードオブジェクトを保存する.
*
* @access private
* @param \TRW\ActiveRecord\BaseRecord $record レコードオブジェクト
* $param array $associations レコードオブジェクトのアソシエーション情報
* @param boolean $owningSide 自身で保持しているか保持されているか
* @return boolean 保存に成功すればtrue 失敗すればfalse
*/
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

/**
* レコードオブジェクトが属している親を保存する.
*
* ここでいう親とは継承関係の親クラスを指すものではなく<br>
* アソシエーション上の親の事
*
* @param \TRW/ActiveRecord\BaseRecord $record　レコードオブジェクト
* @return boolean レコードの保存に成功すれば true 失敗すればfalse
*/
	public static function saveParents($record){
		if(empty(self::$associations[get_class($record)])){
			return true;
		}
		$associations = self::get(get_class($record));
		return self::saveAssociations($record, $associations,false);
	}


/**
* レコードオブジェクトが保持している子を保存する.
*
* ここでいう子とは継承関係の子クラスを指すものではなく<br>
* アソシエーション上の子の事
*
* @param \TRW/ActiveRecord\BaseRecord $record　レコードオブジェクト
* @return boolean レコードの保存に成功すれば true 失敗すればfalse
*/
	public static function saveChilds($record){
		if(empty(self::$associations[get_class($record)])){
			return true;
		}
		$associations = self::get(get_class($record));
		return self::saveAssociations($record, $associations,true);
	}





}






	
