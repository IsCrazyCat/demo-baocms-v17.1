<?php

class weixinh5{
	
    public function init($payment){
        define('WEIXIN_APPID', $payment['appid']);
        define('WEIXIN_MCHID', $payment['mchid']);
        define('WEIXIN_APPSECRET', $payment['appsecret']);
        define('WEIXIN_KEY',$payment['appkey']);
    }
	
	
    public function getCode($logs, $payment){
		$config = D('Setting')->fetchAll();
		require_once "weixin/wechatH5Pay.php";
		$wechatAppPay = new wechatAppPay($payment['appid'],$payment['mchid'], $payment['notify_url'],$payment['appkey']);
		$params['body'] = $logs['subject']; //商品描述
        $params['out_trade_no'] = $logs['logs_id'].'-'.time();  //自定义的订单号
        $params['total_fee'] = $logs['logs_amount'] *100;    //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'MWEB';   //交易类型 JSAPI | NATIVE | APP | WAP 
        $params['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "'.$config['site']['host'].'","wap_name": "'.$logs['subject'].'"}}';//上报业务场景
		
		
        $result = $wechatAppPay->unifiedOrder($params);
		if($result['mweb_url']){
			 $url = $result['mweb_url'].'&redirect_url='.$config['site']['host'] . U( 'wap/payment/yes',array('log_id' => $logs['logs_id'],'code'=>'weixinh5'));//redirect_url 是支付完成后返回的页面
			//$url = $result['mweb_url'].'&redirect_url='.urlencode($config['site']['host'] .'wap/payment/respond/code/weixinh5');//redirect_url 是支付完成后返回的页面
			$button = '<a href="'.$url. '" type="button" class="button button-block bg-dot button-big text-center">立刻微信H5支付</a>';
		}else{
			$button = '<a type="button" class="button button-block bg-gray button-big text-center">H5支付配置错误或者下单失败</a>';
		}
		
       
        return $button;
    }
	
	
	  public function respond() {
        $xml = file_get_contents("php://input");
        if(empty($xml))
            return false;
        $xml = new SimpleXMLElement($xml);
        if(!$xml)
            return false;
        $data = array();
        foreach($xml as $key => $value){
            $data[$key] = strval($value);
        }
        if(empty($data['return_code']) || $data['return_code'] != 'SUCCESS'){
            return false;
        }
        if(empty($data['result_code']) || $data['result_code'] != 'SUCCESS'){
            return false;
        }
        if(empty($data['out_trade_no'])){
            return false;
        }
        ksort($data);
        reset($data);
        $payment = D('Payment')->getPayment('weixinh5');
        if(!D('Payment')->checkMoney($data['out_trade_no'], $data['total_fee'])){
            return false;
        }
        $sign = array();
        foreach($data as $key => $val){
            if($key != 'sign'){
                $sign[] = $key . '=' . $val;
            }
        }
        $sign[] = 'key=' . $payment['appkey'];
        $signstr = strtoupper(md5(join('&', $sign)));
        if($signstr != $data['sign']){
            return false;
        }    
		$trade = explode('-',$data['out_trade_no']);//新版回调
		D('Payment')->logsPaid($trade[0]);
        return true;
    }

}