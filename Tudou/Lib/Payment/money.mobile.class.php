<?php


//余额支付
class money{
    public function  getCode($logs){
        return '<input type="button" class="button button-block bg-green button-big" onclick="window.open(\''.U('user/member/pay',array('logs_id'=>$logs['logs_id'],'code'=>'money')).'\')" value=" 立刻使用余额支付 " />';
    }
}