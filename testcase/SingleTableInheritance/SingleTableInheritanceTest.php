<?php

require '../../vendor/autoload.php';


require 'Player.php';
require 'FootBaller.php';
require 'Cricketer.php';
require 'Bowller.php';

use TRW\ActiveRecord\Database\Driver\MySql;

use App\Model\FootBaller;
use App\Model\Cricketer;
use App\Model\Bowller;
use App\Model\Player;

class SingleTableInheritanceTest extends PHPUnit_Framework_TestCase {


	protected static $connection;

	public static function setUpBeforeClass() {
		$config = require '../config.php';
		self::$connection = new MySql($config['Database']['MySql']);
		TRW\ActiveRecord\BaseRecord::setConnection(self::$connection);
	}

	public function setUp(){
		$conn = self::$connection;
		TRW\ActiveRecord\IdentityMap::clearAll();
		
		$conn->query("DELETE FROM players");
		$conn->query("INSERT INTO players(id, type ,name, club, batting_average, bowling_average) 
			VALUES
			(1, 'Player', 'foo', null, null, null),
			(2, 'Cricketer', 'hoge', null, 100, null),
			(3, 'Bowller', 'bar', null, 20, 10),

			(4, 'FootBaller','fuga', 'A club', null, null)");
	}

	public function testRead(){
		$player = Player::read(1);

		$this->assertInstanceOf('App\Model\Player', $player);
		$this->assertEquals('Player', $player->type);
		$this->assertEquals('foo', $player->name);
		$this->assertEquals(null, $player->club);
		$this->assertEquals(null, $player->batting_average);
		$this->assertEquals(null, $player->bowling_average);


		$cricketer = Player::read(2);

		$this->assertInstanceOf('App\Model\Cricketer', $cricketer);
		$this->assertEquals('Cricketer', $cricketer->type);
		$this->assertEquals('hoge', $cricketer->name);
		$this->assertEquals(null, $cricketer->club);
		$this->assertEquals(100, $cricketer->batting_average);
		$this->assertEquals(null, $cricketer->bowling_average);
	
	
		$bowller = Player::read(3);

		$this->assertInstanceOf('App\Model\Bowller', $bowller);
		$this->assertEquals('Bowller', $bowller->type);
		$this->assertEquals('bar', $bowller->name);
		$this->assertEquals(null, $bowller->club);
		$this->assertEquals(20, $bowller->batting_average);
		$this->assertEquals(10, $bowller->bowling_average);


		$footballer = Player::read(4);
	
		$this->assertInstanceOf('App\Model\FootBaller', $footballer);	
		$this->assertEquals('FootBaller', $footballer->type);
		$this->assertEquals('fuga', $footballer->name);
		$this->assertEquals('A club', $footballer->club);
		$this->assertEquals(null, $footballer->batting_average);
		$this->assertEquals(null, $footballer->bowling_average);
	}


	public function testCreateAndNewRecordSave(){
		$player = Player::create([
				'name' => 'foo',
			]);
/*
*	typeを明示しなければ各クラスで定義されたフィールドを持ち、
*	typeには呼び出したクラス名が挿入される
*/
		$this->assertInstanceOf('App\Model\Player', $player);
		$this->assertEquals(
			[
				'name' => 'foo',
				'type' => 'Player'
			],
			Player::read($player->id)->getData());



		$footballer = FootBaller::create();

		$this->assertInstanceOf('App\Model\FootBaller', $footballer);
		$this->assertEquals(
			[
				'name' => null,
				'type' => 'FootBaller',
				'club' => null
			],
			Player::read($footballer->id)->getData());



		$cricketer = Player::newRecord([
			'name' => 'bar',
			'type' => 'Cricketer',
			'batting_average' => 10
		]);

		$this->assertInstanceOf('App\Model\Cricketer', $cricketer);
		$this->assertEquals(true, $cricketer->save());
		$this->assertEquals(
			[
				'name' => 'bar',
				'type' => 'Cricketer',
				'batting_average' => 10
			],
			Player::read($cricketer->id)->getData());



		$bowller = Player::create([
			'name' => 'bar',
			'type' => 'Bowller',
			'batting_average' => 10
		]);
		$bowller->bowling_average = 100;

		$this->assertInstanceOf('App\Model\Bowller', $bowller);
		$this->assertEquals(
			[
				'name' => 'bar',
				'type' => 'Bowller',
				'batting_average' => 10,
				'bowling_average' => 100
			],
			Bowller::read($bowller->id)->getData());


/*
*	update
*/
		$bowller->name = 'modified';
		
		$this->assertEquals(true, $bowller->save());
		$this->assertEquals(
			[
				'name' => 'modified',
				'type' => 'Bowller',
				'batting_average' => 10,
				'bowling_average' => 100
			],
			Player::read($bowller->id)->getData());
		


	}


}























