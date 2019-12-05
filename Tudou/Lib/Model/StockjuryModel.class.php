<?php
class StockjuryModel extends CommonModel{
    protected $pk = 'jury_id';
    protected $tableName = 'stock_jury';
   
	public function getError() {
        return $this->error;
    }
	//检测是否自己购买
	public function create_get_team_id($jury_id){
        $detail = $this->where(array('jury_id'=>$jury_id))->find();
        if($detail){
		   return $detail['team_id'];
		}else{
		   return false; 
	    }
    }
 }