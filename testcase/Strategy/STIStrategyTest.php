<?php
namespace App\Model;
require '../../vendor/autoload.php';

use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\Database\Driver\MySql;
use TRW\ActiveRecord\Strategy\STIStrategy;
use TRW\ActiveRecord\IdentityMap;
use TRW\ActiveRecord\RecordOperator;
use TRW\ActiveRecord\RecordProperty;

class Player extends BaseRecord{

	protected static $inheritance = 'STI';

	protected static $useColumn = [
		'name' => 'string'
	];

	public static function tableName(){
		return 'players';
	}
}

class Cricketer extends Player {
	protected static $useColumn = [
		'batting_average' => 'int'
	];
}

class Bowller extends Cricketer {
	protected static $useColumn = [
		'bowling_average' => 'int'
	];
}

class FootBaller extends Player {
	protected static $useColumn = [
		'club' => 'string'
	];		
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
		$conn->query("DELETE FROM players");
		$conn->query("INSERT INTO players(id, type ,name, club, batting_average, bowling_average) 
			VALUES
			(1, 'Player', 'foo', null, null, null),
			(2, 'Cricketer', 'hoge', null, 100, null),
			(3, 'Bowller', 'bar', null, 20, 10),

			(4, 'FootBaller','fuga', 'A club', null, null)");
	}

	public function testFindAll(){
		$sti = new STIStrategy(self::$operator);
		$result = $sti->find('App\Model\Bowller');
		print_r($result);
	}

/*
	public function testFindAll(){
		$sti = new STIStrategy(self::$operator);

		$result =  $sti->find('App\Model\Bowller');

		$this->assertInstanceOf('App\Model\Player', $result[0]);
		$this->assertEquals(
			[
				'type'=>'Player',
				'name'=>'foo',
				'id'=>$result[0]->id
			],
			$result[0]->getData());
			
		$this->assertInstanceOf('App\Model\Cricketer', $result[1]);
		$this->assertEquals(
			[
				'type'=>'Cricketer',
				'name'=>'hoge',
				'batting_average'=>100,
				'id'=>$result[1]->id
			],
			$result[1]->getData());
	
		$this->assertInstanceOf('App\Model\Player', $result[2]);
		$this->assertEquals(
			[
				'type'=>'Bowller',
				'name'=>'bar',
				'batting_average'=>20,
				'bowling_average'=>10,
				'id'=>$result[2]->id
			],
			$result[2]->getData());
	
		$this->assertInstanceOf('App\Model\Player', $result[3]);
		$this->assertEquals(
			[
				'type'=>'FootBaller',
				'name'=>'fuga',
				'club'=>'A club',
				'id'=>$result[3]->id
			],
			$result[3]->getData());
	
	}


	public function testFindBy(){
*		$conn->query("INSERT INTO players(id, type ,name, club, batting_average, bowling_average) 
*			VALUES
*			(1, 'Player', 'foo', null, null, null),
*			(2, 'Cricketer', 'hoge', null, 100, null),
*			(3, 'Bowller', 'bar', null, 20, 10),
*
*			(4, 'FootBaller','fuga', 'A club', null, null)");
*
		$sti = new STIStrategy(self::$operator);

		$record1 = $sti->find('App\Model\Player', [
			'where'=>[
				'field'=>'id',
				'comparision'=>'=',
				'value'=>1
			]
		])[0];
		
		$this->assertInstanceOf('App\Model\Player', $record1);
		$this->assertEquals(
			[
				'type'=>'Player',
				'name'=>'foo',
				'id'=>$record1->id
			]
			,$record1->getData());

		
		$record2 = $sti->find('App\Model\Player' ,[
			'where'=>[
				'field'=>'id',
				'comparision'=>'=',
				'value'=>2
			]
		])[0];
		
		$this->assertInstanceOf('App\Model\Cricketer', $record2);
		$this->assertEquals(
			[
				'type'=>'Cricketer',
				'name'=>'hoge',
				'batting_average'=>100,
				'id'=>$record2->id
			]
			,$record2->getData());

		
		$record3 = $sti->find('App\Model\Player', [
			'where'=>[
				'field'=>'id',
				'comparision'=>'=',
				'value'=>3
			]
		])[0];
		
		$this->assertInstanceOf('App\Model\Bowller', $record3);
		$this->assertEquals(
			[
				'type'=>'Bowller',
				'name'=>'bar',
				'batting_average'=>20,
				'bowling_average'=>10,
				'id'=>$record3->id
			]
			,$record3->getData());

		$record4 = $sti->find('App\Model\Player', [
			'where'=>[
				'field'=>'id',
				'comparision'=>'=',
				'value'=>4
			]
		])[0];
		
		$this->assertInstanceOf('App\Model\FootBaller', $record4);
		$this->assertEquals(
			[
				'type'=>'FootBaller',
				'name'=>'fuga',
				'club'=>'A club',
				'id'=>$record4->id
			]
			,$record4->getData());

	}


	public function testNewRecord(){
		$sti = new STIStrategy(self::$operator);


		$cricketer = $sti->newRecord('App\Model\FootBaller',['club'=>'a']);

		$this->assertEquals(
			[
				'id'=>null,
				'club'=>'a',
				'name'=>null,
				'type'=>'FootBaller',
			], $cricketer);
		$cricketer->name = 'new cricketer';
		$cricketer->undifined = '????';
$cricketer->insert($cricketer->getData());
		$this->assertEquals(true, $sti->save($cricketer));

		$r = self::$connection->query("select * from players");
		print_R($r->fetchAll(\PDO::FETCH_ASSOC));
	}
*/

}








