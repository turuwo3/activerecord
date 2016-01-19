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
		$connection->query("INSERT INTO mocks(id, name, age) values(1, 'foo', 20)");

		$connection->query("DELETE FROM mockdependents");
		$connection->query("INSERT INTO mockdependents (id, name, mock_id) values(1, 'bar', 1)");
		IdentityMap::clearAll();
	}


	public function testHasOneSideValidate(){
	
		$mock = Mock::read(1);

		$mock->name = 'modified';
		$mock->Mockdependent->name = '';
/*
* Mockdependent->nameはバリデーションエラー
* saveAtomic()はsave()に失敗するとrollbackを行う
*/
		$mock->saveAtomic();

		$subject = BaseRecord::whereAll('mocks',
			[
				'where'=>[
					'field'=>'id',
					'comparision'=>'=',
					'value'=>1
				]
			]
		);

		$dependent = BaseRecord::whereAll('mockdependents',
			[
				'where'=>[
					'field'=>'id',
					'comparision'=>'=',
					'value'=>1
				]
			]
		);

/*
* validationエラーが起こった場合
*/
		$this->assertEquals('foo', $subject->fetch()['name']);
		$this->assertEquals('bar', $dependent->fetch()['name']);
		$this->assertEquals('name error', Mock::flashError());







		$mock->name = 'modified';
		$mock->Mockdependent->name = 'modified';

		$mock->saveAtomic();

		$subject = BaseRecord::whereAll('mocks',
			[
				'where'=>[
					'field'=>'id',
					'comparision'=>'=',
					'value'=>1
				]
			]
		);

		$dependent = BaseRecord::whereAll('mockdependents',
			[
				'where'=>[
					'field'=>'id',
					'comparision'=>'=',
					'value'=>1
				]
			]
		);
/*
* validationエラーが起こらなかった場合
*/
		$this->assertEquals('modified', $subject->fetch()['name']);
		$this->assertEquals('modified', $dependent->fetch()['name']);
		$this->assertEquals(null, Mock::flashError());
	}




	public function testBelongsToSideValidate(){	

		$mockdependent = Mockdependent::read(1);

		$mockdependent->name = 'modified';
		$mockdependent->Mock->name = '';

		$mockdependent->saveAtomic();

		$subject = BaseRecord::whereAll('mocks',
			[
				'where'=>[
					'field'=>'id',
					'comparision'=>'=',
					'value'=>1
				]
			]
		);

		$dependent = BaseRecord::whereAll('mockdependents',
			[
				'where'=>[
					'field'=>'id',
					'comparision'=>'=',
					'value'=>1
				]
			]
		);
/*
* validationエラーが起こった場合
*/
		$this->assertEquals('foo', $subject->fetch()['name']);
		$this->assertEquals('bar', $dependent->fetch()['name']);
		$this->assertEquals('name error', Mock::flashError());








		$mockdependent->name = 'modified';
		$mockdependent->Mock->name = 'modified';

		$mockdependent->saveAtomic();

		$subject = BaseRecord::whereAll('mocks',
			[
				'where'=>[
					'field'=>'id',
					'comparision'=>'=',
					'value'=>1
				]
			]
		);

		$dependent = BaseRecord::whereAll('mockdependents',
			[
				'where'=>[
					'field'=>'id',
					'comparision'=>'=',
					'value'=>1
				]
			]
		);
/*
* validationエラーが起こらなかった場合
*/
		$this->assertEquals('modified', $subject->fetch()['name']);
		$this->assertEquals('modified', $dependent->fetch()['name']);
	}







}






