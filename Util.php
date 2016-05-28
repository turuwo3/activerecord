<?php
namespace TRW\ActiveRecord;

/**
* このクラスは文字列変換を行う.
*
*
*/
class Util {

	private static $dictionary = [
    	'child'      => 'children',
	    'crux'       => 'cruces',
		'foot'       => 'feet',
		'knife'      => 'knives',
		'leaf'       => 'leaves',
		'louse'      => 'lice',
		'man'        => 'men',
		'medium'     => 'media',
		'mouse'      => 'mice',
		'oasis'      => 'oases',
		'person'     => 'people',
		'phenomenon' => 'phenomena',
		'seaman'     => 'seamen',
		'snowman'    => 'snowmen',
		'tooth'      => 'teeth',
		'woman'      => 'women',
	];

/**
* 単数形を複数形に変換する.
*
* @param string $singular 単数形の文字列
* @return string　複数形の文字列
*/
    public static  function plural($singular) {
		$plural = "";
		if (array_key_exists($singular, self::$dictionary)) {
			$plural = self::$dictionary[$singular];
		} elseif (preg_match('/(s|x|sh|ch|o)$/', $singular)) {
			$plural = preg_replace('/(s|x|sh|ch|o)$/', '$1es', $singular);
		} elseif (preg_match('/y$/', $singular)) {
			$plural = preg_replace('/y$/', 'ies', $singular);
		} else {
			$plural = $singular . "s";
		}
		return $plural;
	}

/**
* 複数形を単数形に変換する.
*
* @param string $plural 複数形の文字列
* @return string 単数形の文字列
*/
	public static function singular($plural){
		$singular = '';
		if(array_search($plural, self::$dictionary)){
			$singular = array_search($plural, self::$dictionary);
		}else if(preg_match('/(s|x|sh|ch|o)es$/', $plural)){
			$singular = preg_replace('/es$/', '', $plural);
		}else if(preg_match('/ies$/', $plural)){
			$singular = preg_replace('/ies$/', 'y', $plural);
		}else {
			$singular = preg_replace('/s$/', '', $plural);
		}
		return $singular;
	}

/**
* 名前空間をクラス名から分裂する.
*
* @param string クラス名
* @return array 名前空間とクラス名
* 名前空間ありの場合 = 
*  [
*    'App\Model',
*    'User'
*  ];
*
* 名前空間無しの場合 =
*  [
*    '',
*    'User'
*  ];
*/
	public static function namespaceSplit($class){
		$pos = strrpos($class, '\\');
		if($pos === false){
			return ['', $class];
		}
		return [substr($class, 0, $pos), substr($class, $pos + 1)];
	}

}
