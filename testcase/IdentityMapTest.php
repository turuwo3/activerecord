<?php

require '../vendor/autoload.php';
require 'Normal/Mock.php';

use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\IdentityMap;
use TRW\ActiveRecord\Database\Driver\MySql;

use App\Model\Mock;

class IdentityMapTest extends PHPUnit_Framework_TestCase {

	protected static $conn; 

	public static function setUpBeforeClass(){
		$config = require 'config.php';
		self::$conn = new MySql($config['Database']['MySql']);
		BaseRecord::setConnection(self::$conn);
	}

	public function setUp(){
		self::$conn->query("DELETE FROM mocks");
		self::$conn->query("INSERT INTO mocks (id, name) VALUES(1, 'foo'), (2, 'bar'), (3, 'hoge')");
	}

	public function testSetAndGet(){
		$record1 = Mock::read(1);
		$record2 = Mock::read(2);
		$record3 = Mock::read(3);
		IdentityMap::set(get_class($record1), $record1->id, $record1);
		IdentityMap::set(get_class($record2), $record2->id, $record2);
		IdentityMap::set(get_class($record3), $record3->id, $record3);

		$this->assertEquals($record1, IdentityMap::get(get_class($record1), $record1->id));
		$this->assertEquals($record2, IdentityMap::get(get_class($record2), $record2->id));
		$this->assertEquals($record3, IdentityMap::get(get_class($record3), $record3->id));
	}



}
