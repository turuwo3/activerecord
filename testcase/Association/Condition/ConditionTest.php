<?php

require '../../../vendor/autoload.php';
require 'Mock.php';
require 'Mockdependent.php';

use TRW\ActiveRecord\Database\Driver\MySql;
use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\IdentityMap;
use App\Model\Mock;
use App\Model\Mockdependent;

class ValidateTest extends PHPUnit_Framework_TestCase {

	protected static $connection;

	public static function setUpBeforeClass(){
		$config = require '../../config.php';
		self::$connection = new MySql($config['Database']['MySql']);
		BaseRecord::setConnection(self::$connection);
	}

	protected function setUp(){
		$connection = self::$connection;
		$connection->query("DELETE FROM mocks");
		$connection->query("INSERT INTO mocks(id, name) values
			(1, 'foo'), (2, 'bar')");

		$connection->query("DELETE FROM mockdependents");
		$connection->query("INSERT INTO mockdependents (id, name, mock_id) values
			(1, 'one', 1), (2, 'two', 1), (3, 'three', 1), (4, 'four', 1), (5, 'five', 2)");
		IdentityMap::clearAll();
	}


	public function testRead(){
		$mock = Mock::read(1);

		$this->assertEquals('three', $mock->Mockdependent[0]->name);
		$this->assertEquals('two', $mock->Mockdependent[1]->name);
		$this->assertEquals(true , empty($mock->Mockdependent[2]));
		
	}



}
