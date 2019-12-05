<?php
class 	ZheyuyueModel extends CommonModel{
    protected $pk = 'yuyue_id';
    protected $tableName = 'zhe_yuyue';
	
    public function getError() {
        return $this->error;
    }
	//预约时候获取电子消费
	public function get_zhe_yuyue_code(){
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
    //检测今日是否下单状态
	public function check_yuyue_time($zhe_id,$user_id){
	    $config = D('Setting')->fetchAll();
	    if(!$zhe = D('Zhe')->find($zhe_id)) {
            $this->error = '您预约的五折卡商家状态异常';
            return false;  
       }
	   if($zhe['closed'] != 0 || $zhe['audit'] != 1) {
            $this->error = '该五折卡商家已关闭或者没有审核';
            return false;  
        }
	   $week_id = date("w"); 
	   $explode_week_id = explode(',', $zhe['week_id']);
	   $a = in_array($week_id,$explode_week_id);
	   
	   $date_id = date("j"); 
       $explode_date_id = explode(',', $zhe['date_id']);
	   $b = in_array($date_id,$explode_date_id);

	   if(empty($a) && empty($b)) {
            $this->error = '抱歉，亲，今天该商家不参与五折卡活动，去其他商家看看嘛';
            return false;  
       }
	   $map = array('user_id'=>$user_id,'status'=>1,'closed'=>0,'end_time' => array('EGT', NOW_TIME));
	   $count = D('Zheorder')->where($map)->count();
	   if($count > 1){
		  $this->error = '您的五折卡状态异常';
		  return false;   
	   }
	   if(!$detail = D('Zheorder')->where($map)->find()){
		  $this->error = '您还没购买五折卡';
		  return false;   
	   }
	   $Users = D('Users')->find($user_id);
	   if(!$Users['mobile']){
		  $this->error = '您还没绑定手机号，暂时无法预约，请先到会员中心绑定手机号码';
		  return false;   
	   }
	   
	   $bg_time = strtotime(TODAY);//今日结束时间
	   $count_yuyue = $this->where(array('user_id'=>$user_id,'closed'=>0,'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
	   if($count_yuyue >= 1){
		  $this->error = '您今日已经预约超过限制了，明天再来吧';
		  return false;   
	   }
	  return true;
    }
	
	
    //商家确认完成，验证已走这里，扫码验证已走这里，全部封装返回真就是成功，如果管理员直接确认已是直接走这里
    public function complete($yuyue_id){
        if(!$yuyue_id = (int)$yuyue_id){
            return false;
        }elseif(!$detail = $this->find($yuyue_id)){
            return false;
        }else{
            $Zhe = D('Zhe')->where(array('zhe_id'=>$detail['zhe_id']))->find();
            if(false !== $this->save(array('yuyue_id'=>$yuyue_id,'is_used'=>1,'used_time'=>NOW_TIME))){
                D('Sms')->sms_zhe_yuyue_is_used_user($yuyue_id);
				D('Users')->addIntegral($detail['user_id'], $Zhe['credit'] , '五折卡【'.$Zhe['zhe_name'].'】消费奖励积分，预约编号:'.$yuyue_id);
                return true;
            }else{
                return false;
            }
            
        }  
    }
	
	 //检测今日是否下单状态
	public function zhe_verify_yuyue($yuyue_id,$shop_id){
	   if(!$detail = $this->find($yuyue_id)){
		   $this->error = '预约订单不存在';
		   return false; 
		}
		if($detail['is_used'] != 0){
		   $this->error = '预约订单状态异常';
		   return false; 
		}
		if($detail['is_used'] == 1){
		   $this->error = '该验证码已使用';
		   return false; 
		}
		if($detail['shop_id'] != $shop_id){
		   $this->error = '该订单不属于您管理，请不要操作';
		   return false; 
		}
		if(empty($detail['mobile'])){
		   $this->error = '该预约订单缺少手机号码无法验证';
		   return false; 
		}
		if(!$Users = D('Users')->find($detail['user_id'])){
		   $this->error = '预约的会员账户已被不存在';
		   return false; 
		}
		if($Users['closed'] == 1 || $Users['is_lock'] == 1 ){
		   $this->error = '预约的会员账户被删除或者被锁定，暂时无法处理预约信息';
		   return false; 
		}
		if (isMobile($detail['mobile'])) {
            session('zhe_used_mobile', $detail['mobile']);
            $randstring = session('zhe_used_code', 100);
            if (empty($randstring)) {
                $randstring = rand_string(6, 1);
                session('zhe_used_code', $randstring);
            }
			D('Sms')->sms_yzm($detail['mobile'],$randstring);
        }
	  return true;
    }
}