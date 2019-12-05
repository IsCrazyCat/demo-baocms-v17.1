<?php 



class paypal{
	
		/** 
	* 自己的paypal账号 
	*/ 
	private $account = 'admin@gmail.com';
	
	/** 
	* paypal支付网关地址 
	*/ 
	private $gateway = 'https://www.paypal.com/cgi-bin/webscr?'; 

    //成支付代码
    function getCode($logs,$payment){
		$config = D('Setting')->fetchAll();
		$arr = array();
		// 告诉Paypal，我的网站是用的我自己的购物车系统 
		$arr['cmd']	= '_xclick';
		// 告诉paypal，我的（商城的商户）Paypal账号，就是这钱是付给谁的 
		$arr['business']= $payment['paypal_ec_username'];
		// 用户将会在Paypal的支付页面看到购买的是什么东西，只做显示，没有什么特殊用途，
		// 如果是多件商品，则直接告诉用户，只支付某个订单就可以了 
		$arr['item_name']= $logs['subject'];
		$arr['amount']	= $logs['logs_amount']; // 告诉Paypal，我要收多少钱 
		// 告诉Paypal，我要用什么货币。这里需要注意的是，由于汇率问题，
		// 如果网站提供了更改货币的功能，那么上面的amount也要做适当更改，
		// paypal是不会智能的根据汇率更改总额的 
		$arr['currency_code']= $payment['paypal_ec_currency'];
		// 当用户成功付款后paypal会将用户自动引导到此页面。
		// 如果为空或不传递该参数，则不会跳转 
		$arr['return']	= $config['site']['host'] . U('Home/payment/respond', array('code'=>'paypal','id'=>$logs['logs_id']));	
		$arr['invoice']	= $logs['logs_id']; 
		$arr['charset']	= 'utf-8'; 
		$arr['no_shipping']	= '1'; 
		$arr['no_note']		= '1'; 
	
		// 当跳转到paypal付款页面时，用户又突然不想买了。则会跳转到此页面 
		$arr['cancel_return']	= $config['site']['host'] . U('Home/payment/respond', array('code'=>'paypal','id'=>$logs['logs_id']));	
		// Paypal会将指定 invoice 的订单的状态定时发送到此URL 
		// (Paypal的此操作，是paypal的服务器和我方商城的服务器点对点的通信，用户感觉不到）
		$arr['notify_url']	= $config['site']['host'] . U('Home/payment/respond', array('code'=>'paypal','id'=>$logs['logs_id'])); 
		$arr['rm']	= '2'; 
		
		$paypal_payment_url = $this->gateway.http_build_query($arr); 
        $button = '<input type="button" class="payment" onclick="window.open(\''.$paypal_payment_url. '\')" value="立刻支付"/>';
        return $button;
    }

  
    function respond(){
        //由于这个文件只有被Paypal的服务器访问，所以无需考虑做什么页面什么的，
		//这个页面不是给人看的，是给机器看的 
		
		$log_id = (int) $_REQUEST['id']; //获取ID
		
	    $payment = D('Payment')->getPayment('paypal');//获取支付
		
		
		// 由于该URL不仅仅只有Paypal的服务器能访问，其他任何服务器都可以向该方法发起请求。
		// 所以要判断请求发起的合法性，也就是要判断请求是否是paypal官方服务器发起的 
		// 拼凑 post 请求数据 
		$req = 'cmd=_notify-validate';
		//验证请求 
		foreach($_POST as $k=>$v){ 
			$v = urlencode(stripslashes($v)); 
			$req .= "&{$k}={$v}"; 
		} 
		
	
		$ch = curl_init(); 
		curl_setopt($ch,CURLOPT_URL,'http://www.paypal.com/cgi-bin/webscr'); 
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($ch,CURLOPT_POST,1); 
		curl_setopt($ch,CURLOPT_POSTFIELDS,$req); 
		$res = curl_exec($ch); 
		curl_close($ch); 
	    
	
		if($res){ 
			// 本次请求是否由Paypal官方的服务器发出的请求 
			if(strcmp($res, 'VERIFIED') == 0){ 
				/** 
				* 判断订单的状态 
				* 判断订单的收款人 
				* 判断订单金额 
				* 判断货币类型 
				*/ 
				
				/*
				if(!D('Payment')->checkMoney($order_id, $_POST['mc_currency'])){
					return false;
				}
				*/
			
				if(($_POST['payment_status'] != 'Completed')
				 OR($_POST['receiver_email'] != $payment['paypal_ec_username'])
				   OR($payment['paypal_ec_currency'] != $_POST['mc_currency'])){ 
				   //如果有任意一项成立，则终止执行。由于是给机器看的，所以不用考虑什么页面。直接输出即可 
					return false;
				}else{
					//file_put_contents(APP_PATH . 'Lib/Payment/1.txt', var_export($payment, true));
					//如果验证通过，则证明本次请求是合法的 
					D('Payment')->logsPaid($log_id);
					return true;
				} 
			}else{ 
				return false;
			} 
		} 
    }

   

}