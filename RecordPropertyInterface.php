<?php
namespace TRW\ActiveRecord;

interface RecordPropertyInterface {

	public function tableName($className);

	public function useColumn($className); 

	public function primaryKey($className);

}
