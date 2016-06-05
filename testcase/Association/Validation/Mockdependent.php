<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Mockdependent extends BaseRecord {

	protected static $associations = [
		'BelongsTo'=>[
			'Mock'=>[]
		]
	];

	public function validate(){
		if(strlen($this->name) < 1){
			static::setError('name error');
			return false;
		}

		return true;
	}

}
