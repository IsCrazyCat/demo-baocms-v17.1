<?php
    //微信NATIVE--原生扫码支付，www.juhucms.com二次开发
    require_once "weixin/WxPay.Api.php";
    require_once "weixin/WxPay.NativePay.php";
    require_once 'weixin/WxPay.Notify.php';
    require_once 'weixin/notify.php';
    class native{

		public function init($payment) {
        define('WEIXIN_APPID', $payment['appid']);
        define('WEIXIN_MCHID', $payment['mchid']);
        define('WEIXIN_APPSECRET', $payment['appsecret']);
        define('WEIXIN_KEY',$payment['appkey']);
        define('WEIXIN_SSLCERT_PATH', '../cert/apiclient_cert.pem');
        define('WEIXIN_SSLKEY_PATH', '../cert/apiclient_key.pem');
        define('WEIXIN_CURL_PROXY_HOST', "0.0.0.0"); 
        define('WEIXIN_CURL_PROXY_PORT', 0); 
        define('WEIXIN_REPORT_LEVENL', 1);
        require_once "weixin/WxPay.Api.php";
        require_once "weixin/WxPay.JsApiPay.php";

    }
        public function getCode($logs ,$payment ){
			
			$config = D('Setting')->fetchAll();
			
			$this->init($payment);
            $notify = new NativePay();
            $url1 = $notify->GetPrePayUrl($logs['logs_id']);
            $url1 = urlencode( $url1 );
            $input = new WxPayUnifiedOrder();
            $input->SetBody( $logs['subject'] );//是 商品或支付单简要描述
            $input->SetAttach( $logs['subject'] );//否 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
            $input->SetDetail( $logs['subject'] );//否 商品名称明细列表
            $input->SetOut_trade_no( $logs['logs_id'].'-'.time());//商户系统内部的订单号,32个字符内、可包含字母,
            $logs['logs_amount'] = $logs['logs_amount'] * 100;
            $input->SetTotal_fee($logs['logs_amount'] );//订单总金额，单位为分
            $input->SetTime_start(date("YmdHis"));//否 订单生成时间
            $input->SetTime_expire(date("YmdHis" , time() + 600));// 否 注意：最短失效时间间隔必须大于5分钟
            $input->SetGoods_tag($logs['subject']); // 否 商品标记，代金券或立减优惠功能的参数
            $input->SetNotify_url( $config['site']['host'] . U( 'Home/payment/respond' , array('code' => 'native')));
            $input->SetTrade_type("NATIVE");
            $input->SetProduct_id($logs['logs_id']);//此参数必传此id为二维码中包含的商品ID，商户自行定义
            $result = $notify->GetPayUrl($input);
            $url2 = $result["code_url"];
            $url2 = urlencode( $url2 );
            $questurl = 'http://paysdk.weixin.qq.com/example/qrcode.php?data=';
            $img = '<img src=' . '\'' . $questurl . $url2 . '\'' . ' style="width:260px;height:260px;"/>';
            return $img;

        }
        public function respond(){
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            if(!empty($xml)){
                $res = new WxPayResults();
				$data = $res->FromXml($xml);
                if(true){
                    if($data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS'){
                        $map['log_id'] = $data['out_trade_no'];
                        $logs = D('Paymentlogs')->where($map)->find();
                        if(!$logs){
							$trade = explode('-',$data['out_trade_no']);//新版回调
                            D('Payment')->logsPaid($trade[0]);
                            return true;
                        }else{
                            if(!$logs['is_paid']){
                                $result = D('Payment')->checkMoney($data['out_trade_no'],$data['total_fee']);
                                if($result){
                                    $trade = explode('-',$data['out_trade_no']);//新版回调
                            		D('Payment')->logsPaid($trade[0]);
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
            return false;
        }
        
    }

