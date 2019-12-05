<?php
class  PayAction extends CommonAction{
	
	//请求支付
	public function order($log_id){
		$logs = D('Paymentlogs')->where(array('log_id'=>$log_id))->find();//查找日志
		$order_money = $logs['need_pay'] *100;//订单金额
		$out_trade_no =$logs['log_id'].'-'.time();//订单号
		$total_fee = $logs['need_pay'];//总金额
		$orderBody = $logs['logs_id'].'订单支付';//商品描述
		
		$payment = D('Payment')->where(array('code'=>'weixin_app'))->find();
		$payment = unserialize($payment['setting']);
		$response = $this->getPrePayOrder($orderBody,$out_trade_no,$total_fee,$payment);
		// p($response);die;
		
		
		
		$x = $this->getOrder($response['prepay_id'],$payment);
		echo json_encode($x);

    }
	
	
	//获取预支付订单
	function getPrePayOrder($body,$out_trade_no,$total_fee,$payment){
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$notify_url = $payment['notify_url'];
		
		$config = array(
			'appid'=>$payment['appid'],//开放平台的
			'appSecret'=>$payment['appsecret'],
			'partnerId'=>$payment['partnerid'],
			'key'=>$payment['appkey'],
		);
		$onoce_str = $this->getRandChar(32);
	
		$data["appid"] = $config['appid'];
		$data["body"] = $body;
		$data["mch_id"] = $config['partnerId'];
		$data["nonce_str"] = $onoce_str;
		$data["notify_url"] = $notify_url;
		$data["out_trade_no"] = $out_trade_no;
		$data["spbill_create_ip"] = get_client_ip();
		$data["total_fee"] = $total_fee;
		$data["trade_type"] = "APP";
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
	
}