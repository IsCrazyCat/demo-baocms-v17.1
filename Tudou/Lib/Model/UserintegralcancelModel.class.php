<?php
class UserintegralcancelModel extends CommonModel{
    protected $pk   = 'cancel_id';
    protected $tableName =  'user_integral_cancel';
	
    public function getError() {
        return $this->error;
    }
	//
	
	
    //商家确认完成，验证已走这里，扫码验证已走这里，全部封装返回真就是成功，如果管理员直接确认已是直接走这里
    public function complete($user_id,$shop_id,$type,$worker_id,$integral,$intro){
        if(!$user_id = (int)$user_id){
            return false;
        }elseif(!$detail = D('Users')->find($user_id)){
            return false;
        }else{
			$data = array();
			$data['user_id'] = $user_id;
			$data['shop_id'] = $shop_id;
			$data['worker_id'] = $worker_id;
			$data['type'] = $type;
			$data['integral'] = $integral;
			$data['intro'] = $intro;
			$data['cancel_date'] = $this->get_date();
			$data['create_time'] = NOW_TIME;
			$data['create_ip'] = get_client_ip();
			if($this->add($data)){
				if(D('Users')->addIntegral($user_id, - $integral, $intro)){
					  return true;
				}else{
				 return false; 
				}
			}else{
				return false;
			}
        }  
    }
	public function get_date(){
        $TODAY_time = strtotime(TODAY) - 60 * 60 * 24;
        return date('Y-m-d', $TODAY_time);
		
	}
	
	 //检测状态发送短信
	public function integral_cancel_verify($user_id,$shop_id){
		if(!$detail = D('Users')->find($user_id)){
		   $this->error = '预约的会员账户已被不存在';
		   return false; 
		}
		if($detail['closed'] == 1 || $detail['is_lock'] == 1 ){
		   $this->error = '该会员账户被删除或者被锁定，暂时无法核销积分';
		   return false; 
		}
		if(empty($detail['mobile'])){
		   $this->error = '该会员的手机号码不存在';
		   return false; 
		}
		if (isMobile($detail['mobile'])) {
            session('cancel_used_mobile', $detail['mobile']);
            $randstring = session('cancel_used_code', 100);
            if (empty($randstring)) {
                $randstring = rand_string(6, 1);
                session('cancel_used_code', $randstring);
            }
			D('Sms')->sms_yzm($detail['mobile'],$randstring);
        }else{
			$this->error = '该会员的手机号码格式不正确';
		    return false;
		}
	  return true;
    }
}