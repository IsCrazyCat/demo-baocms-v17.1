<?php
class StockteamModel extends CommonModel{
    protected $pk = 'team_id';
    protected $tableName = 'stock_team';
   
	public function getError() {
        return $this->error;
    }
	
	
 }