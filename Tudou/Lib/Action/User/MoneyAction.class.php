<?php
class MoneyAction extends CommonAction{
    public function index(){
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->display();
    }
    public function moneypay(){
        $money = (int) ($this->_post('money') * 100);
        $code = $this->_post('code', 'htmlspecialchars');
        if ($money <= 0) {
            $this->error('请填写正确的充值金额');
        }
        if ($money > 1000000) {
            $this->error('每次充值金额不能大于1万');
        }
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->error('该支付方式不存在');
        }
        $logs = array(
			'user_id' => $this->uid, 
			'type' => 'money', 
			'code' => $code, 
			'order_id' => 0, 
			'need_pay' => $money, 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip()
		);
        $logs['log_id'] = D('Paymentlogs')->add($logs);
        $this->assign('button', D('Payment')->getCode($logs));
        $this->assign('money', $money);
        $this->assign('logs', $logs);
        $this->display();
    }
    public function recharge(){
        //代金券充值
        if ($this->isPost()) {
            $card_key = $this->_post('card_key', htmlspecialchars);
            if (!D('Lock')->lock($this->uid)) {
                $this->tuMsg('服务器繁忙，1分钟后再试');
            }
            if (empty($card_key)) {
                D('Lock')->unlock();
                $this->tuMsg('充值卡号不能为空');
            }
            if (!($detail = D('Rechargecard')->where(array('card_key' => $card_key))->find())) {
                D('Lock')->unlock();
                $this->tuMsg('该充值卡不存在');
            }
            if ($detail['is_used'] == 1) {
                D('Lock')->unlock();
                $this->tuMsg('该充值卡已经使用过了');
            }
            $member = D('Users')->find($this->uid);
            $member['money'] += $detail['value'];
            if (D('Users')->save(array('user_id' => $this->uid, 'money' => $member['money']))) {
                D('Usermoneylogs')->add(array(
					'user_id' => $this->uid, 
					'money' => +$detail['value'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'intro' => '代金券充值' . $detail['card_id']
				));
                $res = D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'is_used' => 1));
                if (!empty($res)) {
                    D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'user_id' => $this->uid, 'used_time' => NOW_TIME));
                }
                $this->tuMsg('充值成功', U('money/recharge'));
            }
            D('Lock')->unlock();
        } else {
            $this->display();
        }
    }
	
	 //积分兑换余额
      public function exchange(){
        if($this->isPost()){
			$config = D('Setting')->fetchAll();
			$integral_buy = $config['integral']['buy'];
			//判断积分设置是否合法
			if (false == D('Users')->check_integral_buy($integral_buy)) {
				$this->tuMsg('网站后台积分设置不合法，请联系管理员');
			}
			
            $exchange = (int)$this->_post('exchange');
			if($exchange <=0){
                $this->tuMsg('要兑换的数量不能为空');
            }
			$scale  = D('Users')->obtain_integral_scale($integral_buy);//获取积分比例便于同步
			
			//批量检测积分兑换余额批量代码封装
			if (!D('Users')->check_integral_exchange_legitimate($exchange,$scale)) {
				$this->tuMsg(D('Users')->getError());	  
			}
	
            if($this->member['integral'] < $exchange){
                $this->tuMsg('账户积分不足');
            }
			$actual_integral = $exchange*$scale;
			$money = $actual_integral - intval(($actual_integral*$config['integral']['integral_exchange_tax'])/100);
			if($money > 0){
				if(D('Users')->addMoney($this->uid,$money,'积分兑换现金')){
					D('Users')->addIntegral($this->uid,-$exchange,'扣除兑换余额使用积分');          
				} 
			}
            $this->tuMsg('您成功兑换余额'.round($money/100,2).'元',U('logs/moneylogs')); 
        }else{
             $this->display();
        }
    }
	
	//获取验证码
	  public function sendsms() {
        if (!$mobile = $this->_post('mobile')) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'请输入正确的手机号码'));
        }
        if (!isMobile($mobile)) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'请输入正确的手机号码'));
        }
        if (!$user = D('Users')->where(array('mobile' => $mobile))->find()) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'手机号码不存在！'));
        }
		if ($user['user_id'] != $this->uid) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'非法操作！'));
        }
        session('mobile', $mobile);
		
		$randstring = session('code');
		if(!empty($randstring)){
			session('code',null);
		}
        $randstring = rand_string(4,1);
        session('code', $randstring);
	
		D('Sms')->sms_yzm($mobile, $randstring);//发送短信
        $this->ajaxReturn(array('status'=>'success','msg'=>'短信发送成功，请留意收到的短信','code'=>session('code')));
    }

	//检测手机号合法
	public function check_mobile(){
        $mobile = $this->_get('mobile');
		if(!empty($mobile)){
			$count_mobile = D('Users')->where(array('mobile' => $mobile))->count();
			if($count_mobile == 1){
				$user = D('Users')->where(array('mobile' => $mobile))->find();//这个版本不加手机号
				if (empty($user) || $user['mobile'] == $this->member['mobile']) {
					echo '0';
				} else {
					echo '您转账到对方昵称是'.'<font color="#F00">'.$user['nickname'].'</font>'.'转账后无法退款，请跟对方核实后再操作打款，建议转账前先联系对方！';
				}
			}else{
				echo '0';
			}
		}else{
			echo '0';
		}
		
    }
	
	//好友转账
      public function transfer(){
        if($this->isPost()){
			$config = D('Setting')->fetchAll();
			$obj = D('Usertransferlogs');
			$cash_is_transfer = $config['cash']['is_transfer'];
			
			//判断网站后台设置是否合法
			if (false == $obj->check_admin_is_transfer($cash_is_transfer)) {
				$this->tuMsg('网站后台设置不合法，请联系管理员');
			}
			
			//检测被赠送的用户手机封装
            $mobile = $this->_post('mobile');
			if (false == $obj->check_transfer_user_mobile($mobile,$this->member['mobile'])) {
				$this->tuMsg($obj->getError());
			}
	
			//检测余额小于0，用户余额是不是不足，超过最大限制，最小限制，检测用户转账间隔时间
			$money = ((int)$this->_post('money'))*100;
			
			if (false == $obj->check_transfer_user_money($money,$this->uid)) {
				$this->tuMsg($obj->getError());
			}

			$yzm = $this->_post('yzm');
            if (empty($mobile) || empty($yzm))
                $this->tuMsg('请填写正确的手机及手机收到的验证码');
            $session_mobile = session('mobile');
            $session_code = session('code');
            if ($this->member['mobile'] != $session_mobile)
                $this->tuMsg('手机号码和收取验证码的手机号不一致');
            if ($yzm != $session_code){
				$this->tuMsg('验证码不正确');
			}
			
			if(!empty($config['cash']['is_transfer_commission'])){
				$commission = intval(($money*$config['cash']['is_transfer_commission'])/100);
				$receive_money = $money + $commission ;//实际扣除
			}
			
			//获取接收的USER
			$users = $obj->get_receive_users($mobile);
			$intro = $this->member['nickname'].'给您转账了'.round($money/100,2).'元';
			$intro1 = $this->member['nickname'].'给'.$users['nickname'].'转账了'.round($money/100,2).'元，手续费'.round($commission/100,2).'元';
			if($money > 0){
				if(D('Users')->addMoney($users['user_id'],$money,$intro)){
				    $logs = array();
					$logs['user_id'] = $this->uid;
					$logs['uid'] = $users['user_id'];
					$logs['money'] = $money;
					$logs['commission'] = $commission;
					$logs['intro'] = $intro1;
					$logs['create_time'] = time();
					$logs['create_ip'] = get_client_ip();
					$log_id = $obj->add($logs);
					if($log_id){
						$intro2 = '您给'.$users['nickname'].'转账了'.round($money/100,2).'元，手续费'.round($commission/100,2).'元';
						if(D('Users')->addMoney($this->uid,-$receive_money,$intro2)){
							$this->tuMsg('恭喜您转账成功',U('logs/moneylogs')); 
						}else{
							$this->tuMsg('操作失败');
						}
					}else{
						$this->tuMsg('操作失败');
					}        
				} 
			}
            
        }else{
             $this->display();
        }
    }
	
	//好友积分转账转账
      public function integral(){
        if($this->isPost()){
			$obj = D('Users');
			$safecode = $this->_post('safecode', 'htmlspecialchars');
			if($safecode != session('safecode')) {
                $this->tuMsg('请勿重复操作');
            }
			$difference = msectime() - $safecode;
			$cha = 10000 - $difference;
			if($difference < 10000) {
				$this->tuMsg('请'.((int)($cha/1000)).'秒后再操作');
			}
	 		$mobile = $this->_post('mobile');
			if(!$mobile){
				$this->tuMsg('请填写手机号');
			}
			$integral = (int)$this->_post('integral');
			if($integral <= 0){
				$this->tuMsg('积分填写错误');
			}
			$intro = $this->_post('intro', 'htmlspecialchars');
			if(!$intro){
				$this->tuMsg('请填写备注');
			}
			if($words = D('Sensitive')->checkWords($intro)) {
                $this->tuMsg('备注中含有敏感词：' . $words);
            }	 
			//获取接收的USER
			$users = $obj->where(array('mobile'=>$mobile))->find();
			if($this->member['integral'] < $integral){
				$this->tuMsg('您的积分账户余额不足，无法转账');
			}
			if($users['user_id'] == $this->uid){
				$this->tuMsg('请不要非法操作');
			}
			if($users){
				$intro = $this->member['nickname'].'给'.$users['nickname'].'转账了'.$integral.'积分：理由'.$intro;
				$obj->addIntegral($this->uid,-$integral,$intro);
				$obj->addIntegral($users['user_id'],$integral,$intro);
				session('safecode', null);
				$this->tuMsg('恭喜您转账积分成功',U('logs/integral')); 
			}else{
				$this->tuMsg('没有找到会员');
			}
        }else{
			 $safecode = msectime();
			 session('safecode', $safecode);
             $this->assign('safecode', $safecode);
             $this->display();
        }
    }
	
	
	//好友威望转账转账
      public function prestige(){
		$_NAME = !empty($this->_CONFIG['prestige']['name']) ? $this->_CONFIG['prestige']['name']:'威望';
        if($this->isPost()){
			$obj = D('Users');
			
			if($this->member['is_prestige_frozen'] != 1){
				$this->ajaxReturn(array('code'=>'1','msg'=>'您的'.$_NAME.'账户未激活，无法转账','url'=>U('money/index')));
			}
			
	 		$mobile = $this->_post('mobile');
			if(!$mobile){
				$this->ajaxReturn(array('code'=>'0','msg'=>'请填写手机号'));
			}
			$prestige = (int)$this->_post('prestige');
			if($prestige <= 0){
				$this->ajaxReturn(array('code'=>'0','msg'=>$_NAME.'填写错误'));
			}
			$intro = $this->_post('intro', 'htmlspecialchars');
			if(!$intro){
				$this->ajaxReturn(array('code'=>'0','msg'=>'请填写备注'));
			}
			if($words = D('Sensitive')->checkWords($intro)) {
				$this->ajaxReturn(array('code'=>'0','msg'=>'备注中含有敏感词：' . $words));
            }	 
			//获取接收的USER
			$users = $obj->where(array('mobile'=>$mobile))->find();
			
			if($this->member['prestige'] < $prestige){
				$this->ajaxReturn(array('code'=>'0','msg'=>'您的'.$_NAME.'账户余额不足，无法转账！'));
			}
			if($users['user_id'] == $this->uid){
				$this->ajaxReturn(array('code'=>'0','msg'=>'请不要非法操作'));
			}
			if($users){
				$intro = $this->member['nickname'].'给'.$users['nickname'].'转账了'.$integral.'-'.$_NAME.'理由:'.$intro;
				$obj->addPrestige($this->uid,-$prestige,$intro);
				$obj->addPrestige($users['user_id'],$prestige,$intro);
				$this->ajaxReturn(array('code'=>'1','msg'=>'恭喜您转账'.$_NAME.'成功','url'=>U('logs/prestige')));
			}else{
				$this->ajaxReturn(array('code'=>'0','msg'=>'没有找到会员，无法转账'));
			}
        }else{
             $this->display();
        }
    }
	
	
}