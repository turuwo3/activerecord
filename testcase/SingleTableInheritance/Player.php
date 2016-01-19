<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Player extends BaseRecord {

	protected static $useColumn = [
		'name' => 'text'
	];

	public static function tableName(){
		return 'players';
	}

}
