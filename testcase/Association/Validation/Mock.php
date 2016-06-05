<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Mock extends BaseRecord {

	protected static $associations = [
		'HasOne'=>[
			'Mockdependent'=>[]
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
