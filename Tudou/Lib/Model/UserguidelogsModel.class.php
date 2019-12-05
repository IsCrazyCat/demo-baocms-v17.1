<?php
class  UserguidelogsModel extends CommonModel{
     protected $pk   = 'log_id';
     protected $tableName =  'user_guide_logs';
	 
	 
	 
	 public function AddMoney($shop_id, $price, $order_id,$type){
	  //找到分成会员ID，分成，循环写入日志
	  $arr = D('Shopguide')->where(array('shop_id'=>$shop_id,'rate'=>array('gt',0),'user_id'=>array('neq','')))->select();
	  $intro = '订单类型【'.$type.'】原始订单ID【'.$order_id.'】会员推荐商家分成';
	  $i=0;
	  
	  foreach($arr as $k => $v){
		  $money = ($price * $v['rate'])/1000;
		  $this->add(array(
			  'shop_id' => $shop_id, 
			  'user_id' => $v['user_id'], 
			  'guide_id' => $v['guide_id'], 
			  'order_id' => $order_id, 
			  'type' => $type, 
			  'money' => $money, 
			  'intro' => $intro, 
			  'create_time' => NOW_TIME, 
			  'create_ip' => get_client_ip()
		  ));
		  $i++;
		  $total_money += $money;
		  D('Users')->addMoney($v['user_id'],$money,$intro);  //写入会员余额
	  }
	  return $total_money;
	 }
}