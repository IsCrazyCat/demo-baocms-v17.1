<?php

class OrderChangeLogsModel extends CommonModel {
    protected $pk = 'log_id';
    protected $tableName = 'order_change_logs';
   
   //修改商城价格
   public function orderChangePrice($order_id,$change_price) {
		if(!($detail = D('Order')->find($order_id))){
            $this->error = '订单不存在';
			return false;
        }
		
		
		$res = D('Order')->where(array('order_id'=>$order_id))->save(array('need_pay'=>$change_price,'is_change'=>1));
		
		
		
		if($res == false){
			$this->error = '价格没有变动';
			return false;
		}
		
		$result = D('Paymentlogs')->where(array('type'=>'goods','is_paid'=>'1','order_id'=>$order_id))->find();
		if($result){
			if(D('Paymentlogs')->where(array('type'=>'goods','is_paid'=>'0','order_id'=>$order_id))->save(array('need_pay'=>$change_price))){
				$this->error = '修改日志失败或者价格没变动';
				return false;
			}
		}
		
		
		//如果没有支付日志
		if(!$result){
			$arr = array();
			$arr['user_id'] = $detail['user_id'];
			$arr['type'] = 'goods';
			$arr['order_id'] = $order_id;
			$arr['code'] = 'weixin';
			$arr['need_pay'] = $change_price;
			$arr['create_time'] = time();
			$arr['create_ip'] = get_client_ip();
			if(D('Paymentlogs')->add($data)){
				$this->error = '添加到支付日志失败';
				return false;
			}
		}
		
		
		
		$data = array();
		$data['order_id'] = $order_id;
		$data['shop_id'] = $detail['shop_id'];
		$data['user_id'] = $detail['user_id'];
		$data['change_price'] = $change_price;
		$data['create_time'] = NOW_TIME;
		$data['create_ip'] = get_client_ip();
		
		if($this->add($data)) {
			D('Sms')->order_change_price_user($detail,$change_price);
			return true;
		}else{
			$this->error = '写入数据库失败';
			return false;
		}
	   return true;
    }
	
	
}