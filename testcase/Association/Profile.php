<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Profile extends BaseRecord {

	protected static $associations = [
		'BelongsTo' => ['User'],	
	];

}
