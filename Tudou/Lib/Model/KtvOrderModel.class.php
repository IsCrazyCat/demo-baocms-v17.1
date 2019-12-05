<?php
class KtvOrderModel extends CommonModel{
    
    protected $pk   = 'order_id';
    protected $tableName =  'ktv_order';
	
	public function getError() {
        return $this->error;
    }
	
	protected $types = array(
		0 => '待付款', 
		1 => '已付款', 
		2 => '已过期', 
		3 => '退款中', 
		4 => '已退款', 
		8 => '已完成'
	);
	
    public function getType(){
        return $this->types;
    }
	
	//获取验证码
	public function getCode(){
        $i = 0;
        while (true) {
            $i++;
            $code = rand_string(8, 1);
            $data = $this->find(array('where' => array('code' => $code)));
            if (empty($data)) {
                return $code;
            }
            if ($i > 20) {
                return $code;
            }
        }
    }
	
	//检测今日是否可下单
	public function checkYuyueDate($time,$ktv_id,$user_id){
	   if(!$detail = D('Ktv')->where(array('ktv_id'=>$ktv_id))->find()) {
            $this->error = '当前KTV不存在';
            return false;  
       }
	   $date = date('Y-m-d',$time);
	   $date_id = ((int)substr($date,8,2));
	   
       $explode_date_id = explode(',', $detail['date_id']);
	   $b = in_array($date_id,$explode_date_id);
	   if(empty($b)) {
            $this->error = '您选择的日期不支持预约请选择其他日期';
            return false;  
       }
	  return true;
    }
	
	
	//获取订单编号
	public function getOrderNumber(){
		$data = date("YmdHis");
        return $data.''.rand_string(6, 1);
    }
	
	//在线支付回调
	public function save_ktv_logs_status($order_id){
        $detail = $this->where(array('order_id'=>$order_id))->find();
        if (!empty($detail)) {
			if ($this->save(array('order_id' => $order_id, 'status' => '1'))){
				D('Ktv')->where(array('ktv_id'=>$detail['ktv_id']))->setInc('orders_num',1);//新增一个预约销量
				D('Sms')->sms_ktv_notice_user($order_id);//通知用户
				D('Sms')->sms_ktv_notice_shop($order_id);//通知商家
				return TRUE; 
			}
        }else{
			return TRUE; 
           //由于支付回调，直接忽略报错 return false;
        }
    }
	
	//KTV退款逻辑封装
	public function ktv_user_refund($order_id){
		if(!$detail = $this->where('order_id =' . $order_id)->find()) {
           $this->error = '没有找到订单';
		   return false;
        }else{
			if($this->where('order_id =' . $order_id)->setField('status', 3)){
				return true;
			}else{
				$this->error = '更新退款状态失败';
				return false;
			}
        }
    }
	
	//KTV处理过期订单
	public function gotimeExpired(){
	   $list = $this->where(array('status' => array('IN',array(0,1))))->select();
       foreach ($list as $key => $val) {
            if(($val['gotime'] + 86400) < NOW_TIME){ 
                $list[$key]['status'] = 2;
				D('Sms')->sms_ktv_gotime_expired_user($val['order_id']);//过期订单短信通知买家
            }
			
        }
		return true;
	}
	//统计当前房间今日预约多次人次
	public function roomTomayNum($room_id){
		$count = $this->where(array(
			'room_id' => $room_id, 
			'closed' => 0, 
			'gotime' => array(array('ELT', NOW_TIME), array('EGT', strtotime(TODAY))), 
			'status' => array('IN',array(1,8))
		))->count();
		return $count;	
	}		
	//KTV退款给用户逻辑封装
    public function ktv_agree_refund($order_id){
		if($order_id = (int)$order_id){
			if($detail = $this->find($order_id)){
				if(false !== $this->save(array('order_id'=>$order_id,'status'=>4))){
					D('Sms')->sms_ktv_refund_user($order_id);//家政退款通知用户手机
					D('Ktv')->where(array('ktv_id'=>$detail['ktv_id']))->setDec('orders_num',1);//减去一个预约销量
					D('Users')->addMoney($detail['user_id'], $detail['price'], 'KTV申请退款，订单号：'.$order_id);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 8,$status = 4);
				    D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 8,$status = 4);
					return true;
				}else{
					$this->error = '更新退款状态失败';
					return false;
				}
			}else{
				$this->error = '当前订单不存在';
				return false;
			}
		}else{
			$this->error = '没找到订单编号';
			return false;
		}
     }  
	
   	//订单验证完成
    public function complete($order_id){
        if(!$order_id = (int)$order_id){
            return false;
        }elseif(!$detail = $this->find($order_id)){
            return false;
        }else{
            $Ktv = D('Ktv')->where(array('ktv_id'=>$detail['ktv_id']))->find();
			$Shop = D('Shop')->find($Ktv['shop_id']);
            if($detail['status'] == 1){
                if ($this->save(array('order_id'=>$order_id,'status'=>8,'is_used_code'=>1))) {
					if($detail['jiesuan_price'] > 0){//有设置结算价格采取结算
						$info = 'KTV订单ID:'.$order_id.'完成，结算金额'.round($detail['jiesuan_price']/100,2);
						D('Shopmoney')->insertData($order_id,$id ='0',$Ktv['shop_id'],$detail['jiesuan_price'],$type ='ktv',$info);//结算给商家
						D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 8,$status = 8);
				    	D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 8,$status = 8);
					}
                   }else{
					   return false;
					}
            }else{
				 return false;
			}
        }  
    }
}