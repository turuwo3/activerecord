<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Mockdependent extends BaseRecord {

	protected static $associations = [
		'BelongsTo'=>['Mock']
	];


}
