<?php

require '../../vendor/autoload.php';


use TRW\ActiveRecord\Database\Driver;
use TRW\ActiveRecord\Database\Driver\MySql;

class MySqlTest extends PHPUnit_Framework_TestCase {

	protected  $db;

	public function setUp(){
		$config = require '../config.php';
		$this->db =  new MySql($config['Database']['MySql']);
		$conn = $this->db;
		$conn->query("DELETE FROM users");
		$conn->query("INSERT INTO users(id,name) VALUES(1,'bar'), (2, 'foo'), (3, 'hoge'), (4, 'fuga')");
	}


	public function testTableExists(){

		$bool = $this->db->tableExists('users');

		$this->assertEquals(true, $bool);
	}

	public function testShema(){

		$schema = $this->db->schema('users');

	}
	

	public function testBuildSelectQuery(){
		$queryObject = $this->db->buildSelectQuery(
			'users',
			['id', 'name', 'age'],
			[
				'where' => [
					'field' => 'id',
					'comparision' => '=',
					'value' => 1
				],
				'and' => [
					'field' => 'name',
					'comparision' => '=',
					'value' => 'bar'
				]
			]
		);

		$this->assertEquals('SELECT id,name,age FROM users WHERE id=:id AND name=:name   ', $queryObject['sql']);
		$this->assertEquals([':id'=>1, ':name'=>'bar'], $queryObject['bindValue']);

		$queryObject2 = $this->db->buildSelectQuery(
			'users',
			[]
		);

		print_r($queryObject);

	}


	public function testRead(){
		$result = $this->db->read(
			'users',
			['id', 'name'],
			[
				'where'=>[
					'field' => 'id',
					'comparision' => '=',
					'value' => 1
				]
			]
		);
		
		$this->assertEquals(
			[
				'id' => 1,
				'name' => 'bar'
			],
		 	$result->fetch());
		
		
		
		$result2 = $this->db->read(
			'users',
			['id', 'name']
		);

		$this->assertEquals(4, count($result2->fetchAll()));
	}



	public function testRowCount(){

		$result = $this->db->read(
			'users',
			['id', 'name'],
			[
				'where'=>[
					'field' => 'id',
					'comparision' => '=',
					'value' => 1
				]
			]
		);

		$this->assertEquals(4, $this->db->rowCount());		

	}



	public function testInsert(){
		$bool = $this->db->insert('users', ['id' => 5, 'name' => 'create']);
		$this->assertEquals(true, $bool);

		$result = $this->db->read('users',
			['id', 'name'],
			[
				'where'=>[
			 		'field'=>'id',
					'comparision'=>'=',
					'value'=>5
			 	]
			]
		);

		$this->assertEquals(['id' => 5, 'name' => 'create'], $result->fetch());
	}


	public function testUpdate(){
		$bool = $this->db->update('users',
			['name' => 'modified'],
			[
				'where'=>[
			 		'field'=>'id',
					'comparision'=>'=',
					'value'=>1
			 	]
			]
		);

		$this->assertEquals(true, $bool);



		$result = $this->db->read('users',
			['id', 'name'],
			[
				'where'=>[
			 		'field'=>'id',
					'comparision'=>'=',
					'value'=>1
			 	]
			]
		);

		$this->assertEquals(['id' => 1, 'name' => 'modified'], $result->fetch());
	}


	public function testDelete(){
		$bool = $this->db->delete('users',
			[
				'where'=>[
			 		'field'=>'id',
					'comparision'=>'=',
					'value'=>1
			 	]
			]
		);

		$this->assertEquals(true, $bool);



		$result = $this->db->read('users',
			['id', 'name'],
			[
				'where'=>[
			 		'field'=>'id',
					'comparision'=>'=',
					'value'=>1
			 	]
			]
		);

		$this->assertEquals([], $result->fetchAll());



		$result2 = $this->db->read('users',
			['id', 'name']
		);

		$this->assertEquals(3, count($result2->fetchAll()));


	}



}





















