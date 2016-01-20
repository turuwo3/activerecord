<?php
namespace TRW\ActiveRecord;

use Exception;
use TRW\ActiveRecord\Util;

abstract class Association {

	private $recordNamespace;
    private $source;
	private $target;

	private $option = [];
	    
	public function __construct($source, $target, $option = []){
		$this->source = $source;
		$this->target = $target;
		$this->option = $option;
	}
					   
	public function source(){
		return $this->source;
	}
								   
	public function target(){
		return $this->target;
	}
	
	protected function mergeOption($conditions){
		$conditions = array_merge($conditions, $this->option);

		return $conditions;
	}

	public function getOption($key = null){
		if($key === null){
			return $this->option;
		}
		if(empty($this->option[$key])){
			return false;
		}
		return $this->option[$key];
	}
											   
	abstract public function find($record);

	abstract public function isOwningSide($tableName);
	
	abstract public function save($record);

	public function recordNamespace($name){
		if($this->recordNamespace === null){
			$source = $this->source;
			$parts = explode('\\', $source);

			array_pop($parts);

			$namespace = implode('\\', $parts);

			$this->recordNamespace = $namespace;
		}

		return $this->recordNamespace . '\\' . $name;
	}

	protected function createTableName($source){
		list(, $class) = Util::namespaceSplit($source);
		return Util::plural(lcfirst($class));
	}
																 
	protected function createForeignKey($name){
		list(, $class) = Util::namespaceSplit($name);

		return lcfirst($class) . '_id';
	}

																		 

}







