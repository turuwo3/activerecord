<?php
namespace TRW\ActiveRecord;

interface RecordOperatorInterface {

	public function find($className, $conditions = []);

	public function insert($recordObject);

	public function update($recordObject);

	public function delete($recordObject);

}
