<?php
class DeliveryOrderModel extends RelationModel {
      protected $pk   = 'order_id';
      protected $tableName =  'delivery_order';

	  protected $_link = array(
        'Delivery' => array(
            'mapping_type' => BELONGS_TO,
            'class_name' => 'Delivery',
            'foreign_key' => 'delivery_id',
            'mapping_fields' =>'name,mobile',
            'as_fields'=>'name,mobile', 
        ),
     );
	 
	 
	 
	 //抢单数据库操作
	 public function upload_deliveryOrder($delivery_id,$order_id){
		$config = D('Setting')->fetchAll();
		$interval_time = (int)$config['delivery']['interval_time'] ? (int)$config['delivery']['interval_time'] :'300';
		$res = M('DeliveryOrder')->where(array('delivery_id' =>$delivery_id,'status'=>'2','closed'=>'0'))->order('update_time desc')->find();
		$cha = time() - $res['update_time'];
		if($cha < $interval_time){
			$second = $interval_time  -	$cha;
		}
		if($res && $cha < $interval_time){
			$this->error = '操作频繁请【'.$second .'】秒后再试';
			return false;
		}
		 

			$Delivery = D('Delivery')->where(array('id'=>$delivery_id))->find();
			$do = D('DeliveryOrder')->where(array('order_id' =>$order_id))->find();//详情
			
			if(empty($do)){
				$this->error = '配送订单不存在';
				return false;
			}elseif(($do['is_appoint'] ==1) || (!empty($do['appoint_user_id']))){
				//如果指定了配送员，非配送员抢单报错处理
				if($Delivery['id'] != $do['appoint_user_id']){
					$this->error = '该订单指定了配送员配送您不能抢单';
					return false;
				}
			}elseif($do['closed'] ==1){
				$this->error = '当前订单已经关闭';
				return false;
			}else{
				if($do['type'] == 0){
				   D('Order')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'2'));
				   D('Ordergoods')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'1'));
				   D('Weixintmpl')->delivery_qiang_tz_user($order_id,$delivery_id,$type=0,$status=0);
				}elseif($do['type'] == 1){
				   if(!D('Eleorder')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'2'))){
					  $this->error = '更新外卖订单状态失败';
					  return false; 
				   }
				   D('Weixintmpl')->delivery_qiang_tz_user($order_id,$delivery_id,$type=1,$status=0);
				}elseif($do['type'] == 3){
				   if(!D('Marketorder')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'2'))){
					  $this->error = '更新菜市场订单状态失败';
					  return false; 
				   }
				   D('Weixintmpl')->delivery_qiang_tz_user($order_id,$delivery_id,$type=3,$status=0);
				}elseif($do['type'] == 4){
					//p($Delivery = D('Storeorder')->where(array('order_id'=>$do['type_order_id']))->find());die;
				   if(!D('Storeorder')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'2'))){
					  $this->error = '更新便利店订单状态失败';
					  return false; 
				   }
				   D('Weixintmpl')->delivery_qiang_tz_user($order_id,$delivery_id,$type=4,$status=0);
				}
			}
			return true;
	  } 
	  
	  
	  
	  
	 //确认完成数据库操作
	 public function ok_deliveryOrder($delivery_id,$order_id){
			$do = D('DeliveryOrder')->where(array('order_id'=>$order_id))->find();
			if(empty($do) ||$do['closed'] ==1 ){
				return false;	
			}else{
				if($do['type'] == 0){
				   D('Order')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'2'));
				   D('Ordergoods')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'1'));
				   D('Weixintmpl')->delivery_qiang_tz_user($order_id,$delivery_id,$type=0,$status=1);//微信通知用户已完成配送
				}elseif($do['type'] == 1){
				   D('EleOrder')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'2'));
				   D('Weixintmpl')->delivery_qiang_tz_user($order_id,$delivery_id,$type=1,$status=1);//微信通知用户已完成配送
				}elseif($do['type'] == 1){
				   D('Marketorder')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'2'));
				   D('Weixintmpl')->delivery_qiang_tz_user($order_id,$delivery_id,$type=3,$status=1);//微信通知用户已完成配送
				}elseif($do['type'] == 1){
				   D('Storeorder')->where(array('order_id'=>$do['type_order_id']))->save(array('status'=>'2'));
				   D('Weixintmpl')->delivery_qiang_tz_user($order_id,$delivery_id,$type=4,$status=1);//微信通知用户已完成配送
				}
			}
			return true;
	  }  
 }
