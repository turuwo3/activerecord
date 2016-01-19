<?php

require '../../vendor/autoload.php';

use TRW\ActiveRecord\Database\Driver\Sqlite;

class SqliteTest extends PHPUnit_Framework_TestCase {

	protected  $db;

	public function setUp(){
		$config = require '../config.php';
		$this->db =  new Sqlite($config['Database']['Sqlite']);
		$conn = $this->db;
		$conn->query("DELETE FROM users");
		$conn->query("INSERT INTO users(id,name) VALUES(1,'bar'), (2, 'foo'), (3, 'hoge'), (4, 'fuga')");
	}

	public function testTableExists(){

		$bool = $this->db->tableExists('users');

		$this->assertEquals(true, $bool);
	}

}
