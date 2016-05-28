<?php
namespace App\Model;

class Child extends Inheritance {

	protected static $associations = [
		'HasMany' => [
			'Inheritancedependent' => []
		]
	];

	protected static $useColumn = [
		'child' => 'text',
		'inheritancedependent_id' => 'int'
	];


}
