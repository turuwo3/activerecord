<?php
namespace TRW\ActiveRecord\Strategy;

interface InheritanceStrategy {


	public function find($recordClass, $conditions = []);

	public function newRecord($recordClass, $fields = []);

	public function save($record);


}
