<?php



class money{
	
    public function  getCode($logs,$setting=array()){
        return '<input type="button" class="payment" onclick="window.open(\''.U('members/pay/pay',array('logs_id'=>$logs['logs_id'],'code'=>'money')).'\')" value=" 使用余额支付 " />';
    }
	
    public function respond(){
    }
    
}