<?php

require '../../../vendor/autoload.php';


require 'Inheritance.php';
require 'Child.php';
require 'Inheritancedependent.php';

use TRW\ActiveRecord\Database\Driver\MySql;
use TRW\ActiveRecord\BaseRecord;

use App\Model\Inheritance;

use App\Model\Child;
use App\Model\Inheritancedependent;

class SingleTableInheritanceTest extends PHPUnit_Framework_TestCase {


	protected static $connection;

	public static function setUpBeforeClass() {
		$config = require '../../config.php';
		self::$connection = new MySql($config['Database']['MySql']);
		BaseRecord::setConnection(self::$connection);
	}

	public function setUp(){
		$conn = self::$connection;
		TRW\ActiveRecord\IdentityMap::clearAll();
		
		$conn->query("DELETE FROM inheritances");
		$conn->query("INSERT INTO inheritances
			(id, type, parent, child) 
			VALUES
			(1, 'Inheritance', 'parent', null),
			(2, 'Child', null, 'child')");
	

		$conn->query("DELETE FROM inheritancedependents");
		$conn->query("INSERT INTO inheritancedependents (id, name, child_id)
			VALUES(1, 'one', 1),(2, 'two', 2)");
	}

	public function testRead(){

		$child = Inheritance::read(2);
		$this->assertEquals('two', $child->Inheritancedependent[0]->name);


	}


}

