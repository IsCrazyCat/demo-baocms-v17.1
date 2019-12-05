<?php
class 	ZheorderModel extends CommonModel{
    protected $pk = 'order_id';
    protected $tableName = 'zhe_order';
	
    public function getError() {
        return $this->error;
    }
	//检测会员状态
	public function Check_Zhe_Order_User_Buy($user_id){
	   $config = D('Setting')->fetchAll();
	   $user = D('Users')->find($user_id);
	   if(empty($config['zhe']['week_card_price']) || empty($config['zhe']['year_card_price'])){
		 $this->error = '五折卡配置错误，无法购买';
		  return false;  
	   }
	   if($user['closed'] == 1){
		  $this->error = '您的会员状态不正确，无法申请';
		  return false; 
	   }
       if(D('Zheorder')->where(array('user_id'=>$user_id,'closed'=>0,'status'=>1,'end_time' => array('EGT', NOW_TIME)))->find()){
		   $this->error = '您已经有五折卡无需申请';
		   return false;
		}
	  return true;
    }
	
	//获取付款价格
	public function get_zhe_need_pay($type){
		$config = D('Setting')->fetchAll();
		if($type == 1){
			$need_pay =  $config['zhe']['week_card_price'] * 100;
			if($need_pay > 0 ){
				return $need_pay;
			}else{
				return false;
			}
		}else{
			$need_pay =  $config['zhe']['year_card_price'] * 100;
			if($need_pay > 0 ){
				return $need_pay;
			}else{
				return false;
			}
		}
       return false;
    }
	
	
	//更新带去流量的购买指数
	public function updateCount_buy_num($order_id){
		$order_id = (int)$order_id;
		$detail = $this->where(array('order_id'=>$order_id))->find();;//查找日志
        D('Zhe')->updateCount($detail['zhe_id'], 'buy_index');	
        return true;
    }
	
	
	//在线支付回调
	public function save_zhe_logs_status($order_id){
        $detail = $this->find($order_id);
        if (!empty($detail)) {
			if ($this->save(array('order_id' => $order_id, 'status' => '1'))) {
				$time = time();
				if($detail['type'] == 1){
					$end_time = $time + 86400*7;
				}else{
					$time = time(); //当前时间戳
					$date = date('Y',$time) + 1 . '-' . date('m-d H:i:s');//一年后日期
					$end_time = strtotime($date);
				}
				if ($this->save(array('order_id' => $order_id, 'start_time' =>$time ,'end_time' =>$end_time))){
					D('Zhe')->updateCount($detail['zhe_id'], 'index');	
					D('Sms')->sms_zhe_notice_user($order_id);//购买五折卡成功通知买家，不用通知网站了
					
					//无折卡分销开始
					$config = D('Setting')->fetchAll();
					if($config['profit']['profit']){
						if($config['profit']['profit_is_zhe']){
							$profit = D('Userprofitlogs')->pay_zhe_profit_user($detail);//无折卡分销
						}
					}
					
					
				}
				return TRUE; 
			}
        }else{
			return TRUE; 
           //由于支付回调，直接忽略报错 return false;
        }
    }

	
	//获取客户的编码
	public function get_zhe_number($user_id){
		$config = D('Setting')->fetchAll();
		$rand = rand_string(4, 1);
		if($config['zhe']['number']){
			return $config['zhe']['number'].''.$rand.''.$user_id;
		}else{
			return '8000'.''.$rand.''.$user_id;
		}
       return true;
    }

}