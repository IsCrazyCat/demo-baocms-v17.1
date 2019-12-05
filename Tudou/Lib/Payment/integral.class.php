<?php




//积分支付
class integral{
    public function  getCode($logs){
		return '<input type="button" class="payment" onclick="window.open(\''.U('members/pay/pay',array('logs_id'=>$logs['logs_id'],'code'=>'integral')).'\')" value=" 使用积分支付 " />';
    }
}