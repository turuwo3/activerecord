<?php
namespace App\Model;

class Bowller extends Cricketer {

	protected static $useColumn = [
		'bowling_average' => 'int',
	];

}
