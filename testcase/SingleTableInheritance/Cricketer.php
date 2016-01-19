<?php
namespace App\Model;

class Cricketer extends Player{

	protected static $useColumn = [
		'batting_average' => 'int'
	];

}
