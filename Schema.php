<?php
namespace TRW\ActiveRecord;

use PDO;
use Exception;
use TRW\ActiveRecord\BaseRecord;

class Schema {

	const
		INTEGER = 'integer',
		DOUBLE = 'double',
		FLOAT = 'double',
		STRING = 'string',
		DATETIME = 'datetime';


	private $table;

/*
*	array = [
*		$field => [
*			'type' => typename,
*			'null' => boolean,
*			'key' => boolean,
*			'default' => '',
*			'extra' => ''
*		]
*	]	
*	
*	@var array
*/
	private $schema;

/*
*	array = [
*		$field => $value,
*	]
*
*	@var array;
*/
	private $columns;


	private $defaults;

	public function __construct($table){
		$this->table = $table;
	}

	public function table($table = null){
		if($table !== null){
			return $this->table = $table;
		}
		return $this->table;
	}


	public function schema(){
		if(empty($this->schema)){
			 $this->schema = BaseRecord::schema($this->table);
		}
		return $this->schema;
	}


	public function columns(){
		if(empty($this->columns)){

			if(empty($this->schema)){

				$this->schema();

				if(empty($this->schema)){
						throw new Exception('missing table from ' . $this->table);
				}
			}
			
			foreach($this->schema as $row){
				$type = $row['Type'];
				$field = $row['Field'];
				if(preg_match('/int\(.*\)/',$type) 
						|| preg_match('/bigint\(.*\)/', $type)
						|| preg_match('/tinyint\(.*\)/', $type) ){
					$result[$field] = self::INTEGER;
				}else if($type === 'float' || $type === 'double'){
					$result[$field] = self::DOUBLE;
				}else if(preg_match('/char\(.*\)/', $type) ||
					 preg_match('/[tiny|midium|long]text/', $type) || $type === 'text' ){
					$result[$field] = self::STRING;
				}else if($type === 'timestamp'){
					$result[$field] = self::DATETIME;
				}
			}

			$this->columns = $result;
		}
					
		return $this->columns;
	}

	public function defaults(){

		if(empty($this->defaults)){

			if(empty($this->schema)){
				$this->schema();		
				if(empty($this->schema)){
						throw new Exception('missing table from ' . $this->table);
				}
			}
			
			foreach($this->schema as $row){
				$this->defaults[$row['Field']] = $row['Default'];
			}

		}
		return $this->defaults;
	}



}



















