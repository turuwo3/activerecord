<?php
namespace TRW\ActiveRecord\Strategy;

/**
* このインターたーフェースはテーブル継承時の<br>
* 読み込み、書き込みなどのメソッドのアルゴリズムのインターフェース.
*
*
*
*/
interface BaseRecordStrategy {

	public function insert();
	
	public function update();
	
	public function find($conditions = []);
	
	public function newRecord($fields = []);	
		
		
}
