<?php
require '../vendor/autoload.php';

use TRW\ActiveRecord\Schema;
use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\Database\Driver\MySql;

class SchemaTest extends PHPUnit_Framework_TestCase{

	protected static $conn;

	static function setUpBeforeClass(){
		 $config = require 'config.php';
		 self::$conn = new MySql($config['Database']['MySql']);
		 BaseRecord::setConnection(self::$conn);
	}

	public function testGetColumns(){
		$users = new Schema('types');
		$columns = $users->columns();
		$expect = [
			'bignum'=>'integer',
			'string'=>'string',
			'tinystring'=>'string',
			'textstring'=>'string',
			'numdouble'=>'double'
		];

		$this->assertEquals($expect, $columns);
	}


}
