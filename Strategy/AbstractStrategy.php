<?php
namespace TRW\ActiveRecord\Strategy;

use TRW\ActiveRecord\Strategy\InheritanceStrategy;

abstract class AbstractStrategy implements InheritanceStrategy {

	protected $operator;

	public function __construct($operator){
		$this->operator = $operator;
	}


	abstract function newRecord($className, $fields = []);



}
