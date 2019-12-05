<?php
class StockorderModel extends CommonModel{
    protected $pk   = 'order_id';
    protected $tableName =  'stock_order';
	
    public function getError() {
        return $this->error;
    }
    
	
	//删除订单
    public function stock_order_delete($order_id){
        if(!$order_id = (int)$order_id){
            return false;
        }elseif(!$detail = $this->find($order_id)){
            return false;
        }else{
            if(false !== $this->save(array('order_id'=>$order_id,'closed'=>1))){
                return true;
            }else{
                return false;
            }
            
        }  
    }
	
	
	//查询股权数量
    public function get_user_stock_order_num($user_id){
       return $this->where(array('user_id'=>$user_id,'closed'=>0,'status'=>1))->sum('num');
    }
	

}