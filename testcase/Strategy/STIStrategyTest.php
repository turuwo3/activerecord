<?php
namespace Test\Model;

require '../../vendor/autoload.php';

use TRW\ActiveRecord\BaseRecord;
use TRW\ActiveRecord\Database\Driver\MySql;
use TRW\ActiveRecord\Strategy\STIStrategy;

class Player extends BaseRecord{

	protected static $useColumn = [
		'name' => 'string'
	];
	
	public static function tableName(){
		return 'players';
	}
}

class FootBaller extends Player {
	protected static $useColumn = [
		'club' => 'string'
	];
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

class STIStrategyTest extends \PHPUnit_Framework_TestCase {

	protected static $connection;

	public static function setUpBeforeClass(){
		$config = require '../config.php';
		self::$connection = new MySql($config['Database']['MySql']);

		BaseRecord::setConnection(self::$connection);
	}

	public function setUp(){
		$conn = self::$connection;
		$conn->query("DELETE FROM players");
		$conn->query("INSERT INTO players(id, type ,name, club, batting_average, bowling_average) 
			VALUES
			(1, 'Player', 'foo', null, null, null),
			(2, 'Cricketer', 'hoge', null, 100, null),
			(3, 'Bowller', 'bar', null, 20, 10),
			(4, 'FootBaller','foga', 'A club', null, null),
			(5, 'Cricketer', 'aaaa', null, 100, null),
			(6, 'FootBaller','foga', 'A club', null, null)
			
			");
	}

	public function testFind(){
		$sti = new STIStrategy('Test\Model\Player', self::$connection);

		print_r($sti->find());
	}


}
