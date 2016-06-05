<?php
namespace TRW\ActiveRecord;

class RecordProperty implements RecordPropertyInterface {

	public function tableName($className){
		return $className::tableName();
	}

	public function useColumn($className){
		return $className::useColumn();		
	}

	public function primaryKey($className){
		return $className::primaryKey();
	}

}
