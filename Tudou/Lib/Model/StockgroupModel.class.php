<?php
class StockgroupModel extends CommonModel{
    protected $pk = 'group_id';
    protected $tableName = 'stock_group';
   
	public function getError() {
        return $this->error;
    }
	
 }