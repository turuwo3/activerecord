<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class User extends BaseRecord {

	protected static $associations = [
		'HasOne' => ['Profile'=>[]],
		'HasMany' => ['Comment'=>[]],
		'BelongsToMany'=>['Skill'=>[]]
	];

}
