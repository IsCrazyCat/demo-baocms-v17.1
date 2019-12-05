<?php

class WxPayAction extends CommonAction{
  

    //统一下单
	public function index(){
        $rd_session = $this->_get('rd_session');
        $user = $this->checkLogin($rd_session);
        $this->uid = $user['uid'];
        $order_id = (int) $this->_get('order_id');
        $order = D('Eleorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            exit(json_encode(array('status'=>-1,'msg'=>'订单不存在','rd_session'=>'')));
        }
        $addr_id = (int) $this->_get('addr_id');
        if (empty($addr_id)) {
             exit(json_encode(array('status'=>-1,'msg'=>'请选择一个收货地址','rd_session'=>'')));
        }
        D('Eleorder')->save(array('addr_id' => $addr_id, 'order_id' => $order_id));

        //为写入物流记录，查询商家类型
        $shop = D('Shop');
        $fshop = $shop->where('shop_id =' . $order['shop_id'])->find();

        $code = 'weixin';
		
        $logs = D('Paymentlogs')->getLogsByOrderId('ele', $order_id);
        if (empty($logs)) {
            $logs = array(
                'type' => 'ele', 
                'user_id' => $this->uid, 
                'order_id' => $order_id, 
                'code' => $code, 
                'need_pay' => $order['need_pay'], 
                'create_time' => NOW_TIME, 
                'create_ip' => get_client_ip(), 
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
        } else {
            $logs['need_pay'] = $order['need_pay'];
            $logs['code'] = $code;
            D('Paymentlogs')->save($logs); 
        }
        D('Weixintmpl')->weixin_notice_ele_user($order_id,$this->uid,1);//外卖微信通知

       $payment = D('Payment')->getPayment('weixin');
	   
	   $openId = $this->getOpenId($rd_session);
	   $response = $this->getPrePayOrder('外卖下单',date("YmdHis")."_".$logs['log_id'],$order['need_pay'],$payment,$openId);
	   $res = $this->getRequestPayment($response,$payment);
	   
	   $json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$res);
       $json_str = json_encode($json_arr);
       exit($json_str); 
	}
	
	//小程序支付再次签名
	public function getRequestPayment($response,$payment){  
		
		$data = array(  
			'appId'     => $payment['appid'],  
			'timeStamp' => time(),  
			'nonceStr'  => $this->getRandChar(32),  
			'package'   => 'prepay_id='.$response['prepay_id'],  
			'signType'  => 'MD5'  
		);  
		
		$md5 = md5('appId='.$payment['appid'].'&nonceStr='.$this->getRandChar(32).'&package=prepay_id='.$response['prepay_id'].'&signType=MD5&timeStamp='.time().'&key='.$payment['appkey']);
		$data['paySign'] = strtoupper($md5);
		return $data;
	} 
	
	//获取预支付订单
	function getPrePayOrder($body,$out_trade_no,$total_fee,$payment,$openId){
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$notify_url = 'https://www.juhucms.com/api/WxPay/notify';
		
		$config = array(
			'appid'=>$payment['appid'],//开放平台的
			'appSecret'=>$payment['appsecret'],
			'partnerId'=>$payment['partnerid'],
			'key'=>$payment['appkey'],
		);
		$onoce_str = $this->getRandChar(32);
	
		$data["appid"] = $payment['appid'];
		$data["body"] = $body;
		$data["mch_id"] = $payment['mchid'];
		$data["nonce_str"] = $onoce_str;
		$data["notify_url"] = $notify_url;
		$data["out_trade_no"] = $out_trade_no;
		$data["spbill_create_ip"] = get_client_ip();
		$data["total_fee"] = $total_fee;
		$data["trade_type"] = "JSAPI";
		$data["openid"] = $openId;
		$s = $this->getSign($payment,$data, false);
		$data["sign"] = $s;
		$xml = $this->arrayToXml($data);
		$response = $this->postXmlCurl($xml, $url);
		
		//将微信返回的结果xml转成数组
		return $this->xmlstr_to_array($response);
	}

	//执行第二次签名，才能返回给客户端使用
	function getOrder($prepayId,$payment){
	  
		$config = array(
			'appid'=>$payment['appid'],//开放平台的
			'appSecret'=>$payment['appsecret'],
			'partnerId'=>$payment['partnerid'],
			'key'=>$payment['appkey'],
		);
		
		$data["appid"] = $config['appid'];
		$data["noncestr"] = $this->getRandChar(32);;
		$data["package"] = "Sign=WXPay";
		$data["partnerid"] = $config['partnerId'];
		$data["prepayid"] = $prepayId;
		$data["timestamp"] = time();
		$s = $this->getSign($payment,$data);
		$data["sign"] = $s;
		$info = json_encode($data);
		$info = str_replace('null', '""', $info);
		
		
		
		return $info;
	}
	
	//回调地址
	public function notify() {
        $xml = file_get_contents("php://input");
        if (empty($xml))
            $this->notify_return(false);
        $xml = new SimpleXMLElement($xml);
        if (!$xml)
            $this->notify_return(false);
        $data = array();
        foreach ($xml as $key => $value) {
            $data[$key] = strval($value);
        }
		//p($data );die;
		//file_put_contents('data.txt', var_export($data, true));
		
		
        if (empty($data['return_code']) || $data['return_code'] != 'SUCCESS') {
            $this->notify_return(false);
        }
        if (empty($data['result_code']) || $data['result_code'] != 'SUCCESS') {
            $this->notify_return(false);
        }
        if (empty($data['out_trade_no'])){
            $this->notify_return(false);
        }
        ksort($data);
        reset($data);
        $payment = D('Payment')->getPayment('weixin_app');
        /* 检查支付的金额是否相符 */
        if (!D('Payment')->checkMoney($data['out_trade_no'], $data['total_fee'])) {
            $this->notify_return(false);
        }
        $sign = array();
        foreach ($data as $key => $val) {
            if ($key != 'sign') {
                $sign[] = $key . '=' . $val;
            }
        }
        $sign[] = 'key=' . $payment['appkey'];
        $signstr = strtoupper(md5(join('&', $sign)));
        if ($signstr != $data['sign']){
            $this->notify_return(false);
        }    
        //D('Payment')->logsPaid($data['out_trade_no']);
		
		$trade = explode('-',$data['out_trade_no']);//新版回调
		D('Payment')->logsPaid($trade[0]);
		
		
        $this->notify_return();
    }

    function notify_return($result = true){
    	if($result){
    		$arr = array(
    			'return_code' => 'SUCCESS',
    			'return_msg' => 'OK'
    		);
    	}else{
    		$arr = array('return_code' => 'FAIL',"return_msg" => '签名失败');
    	}

    	$xml = $this->arrayToXml($arr);
    	die($xml);
    }
	
	
	//生成签名
	function getSign($payment,$Obj){
		$config = array(
			'appid'=>$payment['appid'],//开放平台的
			'appSecret'=>$payment['appsecret'],
			'partnerId'=>$payment['partnerid'],
			'key'=>$payment['appkey'],
		);
		
		foreach ($Obj as $k => $v){
			$Parameters[strtolower($k)] = $v;
		}
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$String = $this->formatBizQueryParaMap($Parameters, false);
		//echo htmlspecialchars("&not");//&not
		//echo "</br>".$String."</br>";
		//签名步骤二：在string后加入KEY
	
		$String = $String."&key=".$config['key'];
		// echo "<textarea style='width: 50%; height: 150px;'>$String</textarea> <br />";
		//echo "</br>".$String."<br/>";
		//签名步骤三：MD5加密
		$result_ = strtoupper(md5($String));
		return $result_;
	}

	//获取指定长度的随机字符串
	function getRandChar($length){
		$str = null;
		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($strPol)-1;
	
		for($i=0;$i<$length;$i++){
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}
	
		return $str;
	}

	//数组转xml
	function arrayToXml($arr){
		$xml = "<xml>";
		foreach ($arr as $key=>$val){
			if (is_numeric($val)){
				$xml.="<".$key.">".$val."</".$key.">";
			}else
			 $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
		}
		$xml.="</xml>";
		return $xml;
	}

	//post https请求，CURLOPT_POSTFIELDS xml格式
	function postXmlCurl($xml,$url,$second=30){
		//初始化curl
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data)
		{
			curl_close($ch);
			return $data;
		}
		else
		{
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error"."<br>";
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}
	
	//将数组转成url字符串
	function formatBizQueryParaMap($paraMap, $urlencode){
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v){
			if($urlencode){
				$v = urlencode($v);
			}
			$buff .= strtolower($k) . "=" . $v . "&";
	
		}
		$buff = rtrim($buff, '&');//去除最后一个&
		return $buff;
	}

	//xml转成数组
	function xmlstr_to_array($xmlstr) {
		$doc = new\DOMDocument();
		$doc->loadXML($xmlstr);
		return $this->domnode_to_array($doc->documentElement);
	}
	
	function domnode_to_array($node) {
		$output = array();
		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
			case XML_ELEMENT_NODE:
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = $this->domnode_to_array($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;
						if(!isset($output[$t])) {
							$output[$t] = array();
						}
						$output[$t][] = $v;
					}
					elseif($v) {
						$output = (string) $v;
					}
				}
				if(is_array($output)) {
					if($node->attributes->length) {
						$a = array();
						foreach($node->attributes as $attrName => $attrNode) {
							$a[$attrName] = (string) $attrNode->value;
						}
						$output['@attributes'] = $a;
					}
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1 && $t!='@attributes') {
							$output[$t] = $v[0];
						}
					}
				}
				break;
		}
		return $output;
	}

	//从xml中获取数组
	function getXmlArray() {
		$xmlData = file_get_contents("php://input");
		if ($xmlData) {
			$postObj = simplexml_load_string($xmlData, 'SimpleXMLElement', LIBXML_NOCDATA);
			if (! is_object($postObj)) {
				return false;
			}
			$array = json_decode(json_encode($postObj), true); // xml对象转数组
			return array_change_key_case($array, CASE_LOWER); // 所有键小写
		} else {
			return false;
		}
	}
	/**
	 * Wechat::verifyNotify()
	 * 验证服务器通知
	 * @param array $data
	 * @return array
	 */
	function verifyNotify($data) {
		$xml = $this->getXmlArray();
		if (! $xml || ! $data) {
			return false;
		}
		$AppSignature = $xml['AppSignature'];
		unset($xml['signmethod'], $xml['appsignature']);
		$sign = $this->buildSign($xml);
		if ($AppSignature != $sign) {
			return false;
		} elseif ($data['trade_state'] != 0) {
			return false;
		}
	
		return $xml;
	}
	//设置参数时需要用到的字符处理函数
	function trimString($value){
		$ret = null;
		if(null != $value){
			$ret = $value;
			if(strlen($ret) == 0){
				$ret = null;
			}
		}
		return $ret;
	}
	
	
	
	 //支付成功回调函数
    public function notify2(){

		$xmlObj=simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA']); //解析回调数据
        $out_trade_no=$xmlObj->out_trade_no;//商户订单号
    	$trad_no = (string)$out_trade_no;
    	$arr = explode('_',$trad_no);
        D('Payment')->logsPaid($arr[1]);
    	echo "success";
    }
	
	
}
