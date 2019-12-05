<?php
//header("Content-type: text/html; charset=utf-8"); 

class UserscashModel extends CommonModel{
    protected $pk = 'cash_id';
    protected $tableName = 'users_cash';
	public function getError(){
        return $this->error;
    }
	
	
	//微信企业付款封装
	public function alipayUserCach($cash_id,$tpye){
		$config = D('Setting')->fetchAll();
		
		$detail = D('Userscash')->where(array('cash_id'=>$cash_id))->find();
		if(!$detail){
           $this->error = '提现的订单不存在';
		   return false;
        }
		if($detail['status'] !=0){
           $this->error = '提现订单状态不正确';
		   return false;
        }

		$money = ($detail['type'] =='user') ? $detail['money']: $detail['gold'];//获取金额
	
		if($money < 10){
			$this->error = '申请提现的金额不合法';
		    return false;
		}
		$money = round($money/100,2);//金额优化
		
		
		$payment = D('Payment')->getPayment('alipay');
		if(!$payment){
			$this->error = '网站没有配置支付宝支付';
		    return false;
		}
		
		include (APP_PATH . 'Lib/Payment/alipay/AopClient.php');
		include (APP_PATH . 'Lib/Payment/alipay/request/AlipayFundTransToaccountTransferRequest.php');
			
		$aop = new AopClient();
		$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
		
		$aop->appId = $payment['alipay_app_id'];//商户ID
		$aop->rsaPrivateKey = $payment['alipay_private_key'];//请填写开发者私钥去头去尾去回车，一行字符串'
		$aop->alipayrsaPublicKey=$payment['alipay_rsa_public_key'];//请填写支付宝公钥，一行字符串
		
		$aop->apiVersion = '1.0';
		$aop->signType = 'RSA2';
		$aop->postCharset='utf-8';
		$aop->format='json';
		
		
		$request = new AlipayFundTransToaccountTransferRequest();
		
		
		$request->setBizContent("{" .
		"\"out_biz_no\":\"{$detail['cash_id']}\"," .//商户转账唯一订单号
		"\"payee_type\":\"ALIPAY_LOGONID\"," .//收款方账户类型
		"\"payee_account\":\"{$detail['alipay_account']}\"," .//收款方账户
		"\"amount\":\"{$money}\"," .//转账金额
		"\"payer_show_name\":\"{$config['site']['sitename']}\"," .//付款方姓名
		"\"payee_real_name\":\"{$detail['alipay_real_name']}\"," .//付款人真实姓名
		"\"remark\":\"申请提现\"" .//备注
		"}");
		$result = $aop->execute($request); 
		$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
		$resultCode = $result->$responseNode->code;
		
		$res = (array)$result->$responseNode;

		if(!empty($resultCode)&&$resultCode == 10000){
			$arr = array();
		
			
			$arr['mch_billno'] = $res['order_id'];//支付宝转账单据号
			$arr['return_msg'] = $res['msg'];//业务返回码描述，参见具体的API接口文档
			$arr['partner_trade_no'] = $res['out_biz_no'];//商户转账唯一订单号
			$arr['mpayment_time'] = $res['pay_date'];
			
			
			$arr['status'] = 1;
			D('Userscash')->where(array('cash_id'=>$cash_id))->save($arr);
			D('Weixintmpl')->weixin_cash_user($detail['user_id'],2);
			return true;
			//如果退款成功
		}else{
			$this->error = '退款失败:错误编码【'.$res['code'].'】sub_code：【'.$res['sub_code'].'】错误说明：【'.$res['sub_msg'].'】';
			return false;
		}	
    }

	
	
	
	//微信企业付款封装
	public function weixinUserCach($cash_id,$tpye){
		$detail = D('Userscash')->where(array('cash_id'=>$cash_id))->find();
		if(!$detail){
           $this->error = '提现的订单不存在';
		   return false;
        }
		if($detail['status'] !=0){
           $this->error = '提现订单状态不正确';
		   return false;
        }

		$money = ($detail['type'] =='user') ? $detail['money']: $detail['gold'];//获取金额
	
		if($money < 100){
			$this->error = '申请提现的金额不合法';
		    return false;
		}
		
		
		
		
		$payment = D('Payment')->getPayment('weixin');
		if(!$payment){
			$this->error = '网站没有配置微信支付';
		    return false;
		}
		$connect = D('Connect')->where(array('uid'=>$detail['user_id'],'type'=>weixin))->find();
		if(empty($connect['open_id'])){
			$this->error = '您没有关注微信或者不是微信登录';
		    return false;
		}
		include (APP_PATH . 'Lib/Payment/WxPayPubHelper/WxPayPubHelper.php');
			
		//调用请求接口基类
        $Redpack = new Withdrawals();
        $Redpack->setParameter('mch_appid', $payment['appid']);
        $Redpack->setParameter('mchid', $payment['mchid']);
        $Redpack->setParameter('partner_trade_no', $cash_id);//商户订单号
        $Redpack->setParameter('re_user_name',$detail['re_user_name']);//收款人姓名
        $Redpack->setParameter('amount', $money);//付款金额
        $Redpack->setParameter('desc','申请提现付款');
        $Redpack->setParameter('openid',$connect['open_id']);
        $Redpack->setParameter('check_name', 'NO_CHECK');
		
        $result = $Redpack->sendMerchantCash();
		
		
		if (is_array($result) && $result['result_code'] == 'SUCCESS'){
			
			$arr = array();
			$arr['mch_billno'] = $result['mch_billno'];
			$arr['return_msg'] = $result['return_msg'];
			$arr['payment_no'] = $result['payment_no'];
			$arr['partner_trade_no'] = $result['partner_trade_no'];
			$arr['mpayment_time'] = time();
			$arr['status'] = 1;
			D('Userscash')->where(array('cash_id'=>$cash_id))->save($arr);
			D('Weixintmpl')->weixin_cash_user($detail['user_id'],2);
			return true;
			//如果退款成功
		}else{
			$this->error = '退款失败:原因【'.$result['return_msg'] .''.$result['err_code_des'].'】';
			return false;
		}	
	
			
		
    }



    //检测分站的提现每天提现多少次
	public function check_cash_addtime($user_id,$type){
		$config = D('Setting')->fetchAll();
		$bg_time = strtotime(TODAY);
		
		if($type == 1){
			$count = $this->where(array('user_id'=>$user_id,'type'=>user,'addtime' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
			if($config['cash']['user_cash_second']){
				if($count > $config['cash']['user_cash_second']){
					return false;
				}
			}
			return true; 
		}elseif($type == 2){
			$count = $this->where(array('user_id'=>$user_id,'type'=>shop,'addtime' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
			if($config['cash']['shop_cash_second']){
				if($count > $config['cash']['shop_cash_second']){
					return false;
				}
			}
			return true;
		}else{
			return true;
		}

    }
}
