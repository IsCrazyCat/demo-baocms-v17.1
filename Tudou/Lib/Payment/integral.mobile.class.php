<?php




//余额支付
class integral{
    public function  getCode($logs){
        return '<input type="button" class="button button-block bg-yellow button-big" onclick="window.open(\''.U('user/member/pay',array('logs_id'=>$logs['logs_id'],'code'=>'integral')).'\')" value=" 立刻使用积分支付 " />';
    }
}