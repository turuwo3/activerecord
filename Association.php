<?php
namespace TRW\ActiveRecord;

use Exception;
use TRW\ActiveRecord\Util;

abstract class Association {

	private $recordNamespace;
    private $source;
	private $target;
	    
	public function __construct($source, $target){
		$this->source = $source;
		$this->target = $target;
	}
					   
	public function source(){
		return $this->source;
	}
								   
	public function target(){
		return $this->target;
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







