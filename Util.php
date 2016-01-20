<?php

namespace TRW\ActiveRecord;

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

	public static function namespaceSplit($class){
		$pos = strrpos($class, '\\');
		if($pos === false){
			return ['', $class];
		}
		return [substr($class, 0, $pos), substr($class, $pos + 1)];
	}

}
