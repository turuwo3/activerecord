<?php
namespace TRW\ActiveRecord;

interface RecordOperatorInterface {

	public function find($className, $conditions = []);

	public function insert($className, $recordObject);

	public function update($className, $recordObject);

	public function delete($className, $recordObject);

	public function hydrate($className, $rowData);

}
