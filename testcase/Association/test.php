<?php

$arr = [
	'HasOne'=>[
		'User'=>[
			'imit'=>1,
			'offset'=>2
		]
	],
	'HasMany'=>[
		'Profile'=>[],
		'Skill'=>[]
	]

];


foreach($arr as $k => $v){
		print_r($v);
}
