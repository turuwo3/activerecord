<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Mock extends BaseRecord {

	protected static $associations = [
		'HasMany'=>[
			'Mockdependent'=>[
				'limit'=>2,
				'offset'=>1,
				'order'=>'id DESC'
			]
		]
	];


}
