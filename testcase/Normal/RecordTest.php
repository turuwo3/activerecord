<?php

require '../../vendor/autoload.php';
require 'Mock.php';

use TRW\ActiveRecord\Database\Driver\MySql;
use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\IdentityMap;
use App\Model\Mock;

class RecordTest extends PHPUnit_Framework_TestCase {

	protected static $connection;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		self::$connection = new MySql($config['Database']['MySql']);
		BaseRecord::setConnection(self::$connection);
	}

	protected function setUp(){
		$connection = self::$connection;
		$connection->query("DELETE FROM mocks");
		$connection->query("INSERT INTO mocks(name) values('bar')");
		IdentityMap::clearAll();
	}

	
	public function testRead(){

		$this->assertEquals(null, Mock::read(0));
		
		$record = Mock::create(['name'=>'foo']);

		$this->assertEquals('foo', Mock::read($record->id)->name);
	}

	public function testWhereAll(){
		$record = BaseRecord::whereAll('mocks',
			[
				'where' => [
					'field' => 'name',
					'comparision' => '=',
					'value' => 'bar',
				]
			],
			'\App\Model\Mock');

		$this->assertEquals('bar', $record[0]->name);
	}


	public function testCreate(){
		$record1 = Mock::create();
		$this->assertEquals($record1, Mock::read($record1->id));

		$record2 = Mock::create(['akuinoaruData'=>'!!!!!!']);
		$record2->crack = 'crack!!!';

		$this->assertEquals(null, $record2->akuinoaruData);
		$this->assertEquals(null, $record2->crack);

		$this->assertEquals($record2, Mock::read($record2->id));

	}


	public function testSave(){
		$record = Mock::create(['name'=>'create']);
		$record->name = 'modified';

		$record->save();
		$this->assertEquals($record->name, Mock::read($record->id)->name);
	}


	public function testUpdateAttr(){
		$record = Mock::create(['name'=>'ceate']);
		$record->updateAttr(['name'=>'modified']);

		$this->assertEquals($record->name, Mock::read($record->id)->name);
	}


	public function testDelete(){
		$record = Mock::create();

		$this->assertEquals(true, $record->delete());
	}


	public function testDeleteAll(){
		$record = Mock::create();	
		
		$this->assertEquals(true,
			BaseRecord::deleteAll(Mock::tableName(), 'id', '=', $record->id));
	}


	public function testInsertAll(){
		$this->assertEquals(true,
			BaseRecord::insertAll(Mock::tableName(), ['name'=>'new']));
	}


	public function testUpdateAll(){
		$record = Mock::create(['name'=>'new']);
	
		$this->assertEquals(true,
			BaseRecord::updateAll(Mock::tableName(), ['name'=>'modified'], $record->id));
/*
* BaseRecord::updateAllはオブジェクトをIdentityMapに登録しないのでNotEqual
*/
		$this->assertNotEquals('modified', Mock::read($record->id)->name);
	}

}




















