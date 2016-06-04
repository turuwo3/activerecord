<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Inheritance extends BaseRecord {

	protected static $inheritance = 'STI';

	protected static $useColumn = [
		'parent' => 'text'	
	];


	public static function tableName(){
		return 'inheritances';		
	}

}
