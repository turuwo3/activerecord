<?php
namespace App\Model;

use TRW\ActiveRecord\BaseRecord;

class Comment extends BaseRecord {

	protected static $associations = [
		'BelongsTo' => ['User'],
	];

}
