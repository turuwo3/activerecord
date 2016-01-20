<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Skill extends BaseRecord {

	protected static $associations = [
		'BelongsToMany'=>['User'=>[]],
	];

}
