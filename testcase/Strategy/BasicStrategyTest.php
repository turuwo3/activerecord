<?php
namespace App\Model;
require '../../vendor/autoload.php';

use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\Database\Driver\MySql;
use TRW\ActiveRecord\Strategy\BasicStrategy;
use TRW\ActiveRecord\IdentityMap;
use TRW\ActiveRecord\RecordOperator;
use TRW\ActiveRecord\RecordProperty;

class User extends BaseRecord {

}

class STIStrategyTest extends \PHPUnit_Framework_TestCase {

	protected static $connection;
	protected static $operator;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		self::$connection = new MySql($config['Database']['MySql']);
		self::$operator = new RecordOperator(self::$connection, new RecordProperty());
		BaseRecord::setConnection(self::$connection);
	}

	public function setUp(){
		IdentityMap::clearAll();
		$conn = self::$connection;
		$conn->query("DELETE FROM users");
		$conn->query("INSERT INTO users(id, name) VALUES
			(1, 'foo'), (2, 'bar'), (3, 'hoge')
		");
	}


	public function testFindAll(){
		$basic = new BasicStrategy(self::$operator);
		print_r($basic->find('App\Model\User'));
	}

	public function testFindBy(){
		$basic = new BasicStrategy(self::$operator);


		$record1 = $basic->find('App\Model\User',
		['where'=>[
			'field'=>'id',
			'comparision'=>'=',
			'value'=>1]
		])[0];

		$this->assertEquals(
			[
				'name'=>'foo',
				'age'=>null,
				'id'=>$record1->id
			],
			$record1->getData());

		
		$record2 = $basic->find('App\Model\User',
		['where'=>[
			'field'=>'id',
			'comparision'=>'=',
			'value'=>2]
		])[0];

		$this->assertEquals(
			[
				'name'=>'bar',
				'age'=>null,
				'id'=>$record2->id
			],
			$record2->getData());


		$record3 = $basic->find('App\Model\User',
		['where'=>[
			'field'=>'id',
			'comparision'=>'=',
			'value'=>3]
		])[0];

		$this->assertEquals(
			[
				'name'=>'hoge',
				'age'=>null,
				'id'=>$record3->id
			],
			$record3->getData());
	}


}



























