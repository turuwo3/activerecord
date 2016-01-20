<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Inheritancedependent extends BaseRecord {

	protected static $associations = [
		'BelongsTo' => ['Child']
	];

}
