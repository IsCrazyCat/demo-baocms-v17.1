<?php
/**
 * 微信支付帮助库
 * ====================================================
 * 接口分三种类型：
 * 【请求型接口】--Wxpay_client_
 * 		统一支付接口类--UnifiedOrder
 * 		订单查询接口--OrderQuery
 * 		退款申请接口--Refund
 * 		退款查询接口--RefundQuery
 * 		对账单接口--DownloadBill
 * 		短链接转换接口--ShortUrl
 * 【响应型接口】--Wxpay_server_
 * 		通用通知接口--Notify
 * 		Native支付——请求商家获取商品信息接口--NativeCall
 * 【其他】
 * 		静态链接二维码--NativeLink
 * 		JSAPI支付--JsApi
 * =====================================================
 * 【CommonUtil】常用工具：
 * 		trimString()，设置参数时需要用到的字符处理函数
 * 		createNoncestr()，产生随机字符串，不长于32位
 * 		formatBizQueryParaMap(),格式化参数，签名过程需要用到
 * 		getSign(),生成签名
 * 		arrayToXml(),array转xml
 * 		xmlToArray(),xml转 array
 * 		postXmlCurl(),以post方式提交xml到对应的接口url
 * 		postXmlSSLCurl(),使用证书，以post方式提交xml到对应的接口url
*/
	include_once("SDKRuntimeException.php");
	include_once("WxPay.pub.config.php");


 
class Common_util_pub{
	function __construct() {
	}

	function trimString($value)
	{
		$ret = null;
		if (null != $value) 
		{
			$ret = $value;
			if (strlen($ret) == 0) 
			{
				$ret = null;
			}
		}
		return $ret;
	}
	
	//用：产生随机字符串，不长于32位
	
	public function createNoncestr($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		}  
		return $str;
	}
	
	//作用：格式化参数，签名过程需要使用
	function formatBizQueryParaMap($paraMap, $urlencode){
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v)
		{
		    if($urlencode)
		    {
			   $v = urlencode($v);
			}
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen($buff) > 0) 
		{
			$reqPar = substr($buff, 0, strlen($buff)-1);
		}
		return $reqPar;
	}
	
	//生成签名
	
	public function getSign($Obj){
		$payment = D('Payment')->getPayment('weixin');
		foreach ($Obj as $k => $v){
			$Parameters[$k] = $v;
		}
		ksort($Parameters);
		$String = $this->formatBizQueryParaMap($Parameters, false);

		$String = $String."&key=".$payment['appkey'];
		$String = md5($String);
		$result_ = strtoupper($String);
		return $result_;
	}
	
	
	function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
        	 if (is_numeric($val))
        	 {
        	 	$xml.="<".$key.">".$val."</".$key.">"; 

        	 }
        	 else
        	 	$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
	
	
	public function xmlToArray($xml)
	{		
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $array_data;
	}

	//作用：以post方式提交xml到对应的接口url
	
	
	public function postXmlCurl($xml,$url,$second=30)
	{	

	
        //初始化curl        
       	$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_PROXY, $second);
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
		curl_close($ch);
		//返回结果

		p($data);
		
		
		if($data)
		{
			curl_close($ch);
			return $data;
		}
		else 
		{ 
			$error = curl_errno($ch);
			p($error);die;
			echo "curl出错，错误码:$error"."<br>"; 
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}

	//作用：使用证书，以post方式提交xml到对应的接口url
	
	
	function postXmlSSLCurl($xml,$url,$second=30)
	{
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
		curl_setopt($ch,CURLOPT_HEADER,FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		//设置证书
		
		
		//使用证书：cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		
	
		//curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		//curl_setopt($ch,CURLOPT_SSLCERT, WxPayConf_pub::SSLCERT_PATH);
		//默认格式为PEM，可以注释
		//curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		//curl_setopt($ch,CURLOPT_SSLKEY, WxPayConf_pub::SSLKEY_PATH);
		
		
		curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/cret/apiclient_cert.pem');
		//默认格式为PEM，可以注释
		curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
		curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/cret/apiclient_key.pem');
		curl_setopt($ch,CURLOPT_CAINFO,getcwd().'/cret/rootca.pem');
		
		//证书重写代码//
		
		
		
		
		//post提交方式
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		}
		else { 
			$error = curl_errno($ch);
			
			echo "curl出错，错误码:$error"."<br>"; 
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}
	function printErr($wording='',$err=''){
		print_r('<pre>');
		echo $wording."</br>";
		var_dump($err);
		print_r('</pre>');
	}
}

//请求型接口的基类
class Wxpay_client_pub extends Common_util_pub 
{
	var $parameters;//请求参数，类型为关联数组
	public $response;//微信返回的响应
	public $result;//返回参数，类型为关联数组
	var $url;//接口链接
	var $curl_timeout;//curl超时时间
	
	function setParameter($parameter, $parameterValue)
	{
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	
	
	function createXml()
	{
	   	$this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
	   	$this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
	    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
	    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
	    return  $this->arrayToXml($this->parameters);
	}

	function postXml()
	{
	    $xml = $this->createXml();
		$this->response = $this->postXmlCurl($xml,$this->url,$this->curl_timeout);
		return $this->response;
	}
	

	function postXmlSSL()
	{	
	    $xml = $this->createXml();
		$this->response = $this->postXmlSSLCurl($xml,$this->url,$this->curl_timeout);
		return $this->response;
	}

	function getResult() 
	{		
		$this->postXml();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}
}


//统一支付接口类
class UnifiedOrder_pub extends Wxpay_client_pub
{	
	function __construct() 
	{
		$this->url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
	}
	
	function createXml()
	{
		try
		{
			if($this->parameters["out_trade_no"] == null) 
			{
				throw new SDKRuntimeException("缺少统一支付接口必填参数out_trade_no！"."<br>");
			}elseif($this->parameters["body"] == null){
				throw new SDKRuntimeException("缺少统一支付接口必填参数body！"."<br>");
			}elseif ($this->parameters["total_fee"] == null ) {
				throw new SDKRuntimeException("缺少统一支付接口必填参数total_fee！"."<br>");
			}elseif ($this->parameters["notify_url"] == null) {
				throw new SDKRuntimeException("缺少统一支付接口必填参数notify_url！"."<br>");
			}elseif ($this->parameters["trade_type"] == null) {
				throw new SDKRuntimeException("缺少统一支付接口必填参数trade_type！"."<br>");
			}elseif ($this->parameters["trade_type"] == "JSAPI" &&
				$this->parameters["openid"] == NULL){
				throw new SDKRuntimeException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！"."<br>");
			}
		   	$this->parameters["appid"] = $this->parameters["appid"];//公众账号ID
		   	$this->parameters["mch_id"] = $this->parameters["mch_id"];//商户号
		   	$this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//终端ip	    
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
			
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	
	function getPrepayId()
	{
		$this->postXml();
		$this->result = $this->xmlToArray($this->response);
		$prepay_id = $this->result["prepay_id"];
		return $prepay_id;
	}
	
}

//订单查询接口

class OrderQuery_pub extends Wxpay_client_pub
{
	function __construct() 
	{
		//设置接口链接
		$this->url = "https://api.mch.weixin.qq.com/pay/orderquery";
		//设置curl超时时间
		$this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;		
	}
	function createXml()
	{
		try
		{
			if($this->parameters["out_trade_no"] == null && 
				$this->parameters["transaction_id"] == null){
					throw new SDKRuntimeException("订单查询接口中，out_trade_no、transaction_id至少填一个！"."<br>");
				}
		   	$this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
		   	$this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}

}

//退款申请接口

class Refund_pub extends Wxpay_client_pub{
	
	function __construct() {
		$this->url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
		$this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;		
	}

	function createXml()
	{
		try
		{
			//检测必填参数
			if($this->parameters["out_trade_no"] == null && $this->parameters["transaction_id"] == null) {
				throw new SDKRuntimeException("退款申请接口中，out_trade_no、transaction_id至少填一个！"."<br>");
			}elseif($this->parameters["out_refund_no"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数out_refund_no！"."<br>");
			}elseif($this->parameters["total_fee"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数total_fee！"."<br>");
			}elseif($this->parameters["refund_fee"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数refund_fee！"."<br>");
			}elseif($this->parameters["op_user_id"] == null){
				throw new SDKRuntimeException("退款申请接口中，缺少必填参数op_user_id！"."<br>");
			}
		   	$this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
		   	$this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	function getResult() 
	{		
		$this->postXmlSSL();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}
	
}


//退款查询接口
class RefundQuery_pub extends Wxpay_client_pub
{
	
	function __construct() {
		$this->url = "https://api.mch.weixin.qq.com/pay/refundquery";
		$this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;		
	}
	
	function createXml()
	{		
		try 
		{
			if($this->parameters["out_refund_no"] == null &&
				$this->parameters["out_trade_no"] == null &&
				$this->parameters["transaction_id"] == null &&
				$this->parameters["refund_id "] == null) 
			{
				throw new SDKRuntimeException("退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！"."<br>");
			}
		   	$this->parameters["appid"] = WxPayConf_pub::APPID;
		   	$this->parameters["mch_id"] = WxPayConf_pub::MCHID;
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}

	function getResult() 
	{		
		$this->postXmlSSL();
		$this->result = $this->xmlToArray($this->response);
		return $this->result;
	}

}

//对账单接口
class DownloadBill_pub extends Wxpay_client_pub{

	function __construct() 
	{
		$this->url = "https://api.mch.weixin.qq.com/pay/downloadbill";
		$this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;		
	}

	function createXml()
	{		
		try 
		{
			if($this->parameters["bill_date"] == null ) 
			{
				throw new SDKRuntimeException("对账单接口中，缺少必填参数bill_date！"."<br>");
			}
		   	$this->parameters["appid"] = WxPayConf_pub::APPID;
		   	$this->parameters["mch_id"] = WxPayConf_pub::MCHID;
		    $this->parameters["nonce_str"] = $this->createNoncestr();
		    $this->parameters["sign"] = $this->getSign($this->parameters);
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}

	function getResult() 
	{		
		$this->postXml();
		$this->result = $this->xmlToArray($this->result_xml);
		return $this->result;
	}
	
	

}

//短链接转换接口
class ShortUrl_pub extends Wxpay_client_pub
{
	function __construct() 
	{
		$this->url = "https://api.mch.weixin.qq.com/tools/shorturl";
		$this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;		
	}
	
	function createXml()
	{		
		try 
		{
			if($this->parameters["long_url"] == null ) 
			{
				throw new SDKRuntimeException("短链接转换接口中，缺少必填参数long_url！"."<br>");
			}
		   	$this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
		   	$this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
		    $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}
	}
	
	function getShortUrl()
	{
		$this->postXml();
		$prepay_id = $this->result["short_url"];
		return $prepay_id;
	}
	
}


class Wxpay_server_pub extends Common_util_pub 
{
	public $data;
	var $returnParameters;
	
	function saveData($xml)
	{
		$this->data = $this->xmlToArray($xml);
	}
	
	function checkSign()
	{
		$tmpData = $this->data;
		unset($tmpData['sign']);
		$sign = $this->getSign($tmpData);//本地签名
		if ($this->data['sign'] == $sign) {
			return TRUE;
		}
		return FALSE;
	}

	function getData()
	{		
		return $this->data;
	}
	
	function setReturnParameter($parameter, $parameterValue)
	{
		$this->returnParameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}
	

	function createXml()
	{
		return $this->arrayToXml($this->returnParameters);
	}

	function returnXml(){
		$returnXml = $this->createXml();
		return $returnXml;
	}
}


//通用通知接口
class Notify_pub extends Wxpay_server_pub {

}




//请求商家获取商品信息接口
class NativeCall_pub extends Wxpay_server_pub{
	function createXml()
	{
		if($this->returnParameters["return_code"] == "SUCCESS"){
		   	$this->returnParameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
		   	$this->returnParameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
		    $this->returnParameters["nonce_str"] = $this->createNoncestr();//随机字符串
		    $this->returnParameters["sign"] = $this->getSign($this->returnParameters);//签名
		}
		return $this->arrayToXml($this->returnParameters);
	}
	function getProductId(){
		$product_id = $this->data["product_id"];
		return $product_id;
	}
	
}


class NativeLink_pub  extends Common_util_pub{
	var $parameters;
	var $url;

	function __construct() {
	}

	function setParameter($parameter, $parameterValue) {
		$this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
	}

	function createLink(){
		try {		
			if($this->parameters["product_id"] == null) {
				throw new SDKRuntimeException("缺少Native支付二维码链接必填参数product_id！"."<br>");
			}			
		   	$this->parameters["appid"] = WxPayConf_pub::APPID;
		   	$this->parameters["mch_id"] = WxPayConf_pub::MCHID;
		   	$time_stamp = time();
		   	$this->parameters["time_stamp"] = "$time_stamp";
		    $this->parameters["nonce_str"] = $this->createNoncestr();
		    $this->parameters["sign"] = $this->getSign($this->parameters);	
			$bizString = $this->formatBizQueryParaMap($this->parameters, false);
		    $this->url = "weixin://wxpay/bizpayurl?".$bizString;
		}catch (SDKRuntimeException $e){
			die($e->errorMessage());
		}
	}
	
	function getUrl() {		
		$this->createLink();
		return $this->url;
	}
}


class JsApi_pub extends Common_util_pub{
	var $code;
	var $openid;
	var $parameters;
	var $prepay_id;
	var $curl_timeout;

	function __construct() {
		$this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
	}

	function createOauthUrlForCode($redirectUrl){
		$urlObj["appid"] = WxPayConf_pub::APPID;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}


	function createOauthUrlForOpenid(){
		$urlObj["appid"] = WxPayConf_pub::APPID;
		$urlObj["secret"] = WxPayConf_pub::APPSECRET;
		$urlObj["code"] = $this->code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->formatBizQueryParaMap($urlObj, false);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}
	

	function getOpenid(){
		$url = $this->createOauthUrlForOpenid();
       	$ch = curl_init();
		curl_setopt($ch, CURLOP_TIMEOUT, $this->curl_timeout);
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
		curl_close($ch);
		$data = json_decode($res,true);
		$this->openid = $data['openid'];
		return $this->openid;
	}


	function setPrepayId($prepayId){
		$this->prepay_id = $prepayId;
	}

	function setCode($code_){
		$this->code = $code_;
	}

	public function getParameters(){
		$jsApiObj["appId"] = WxPayConf_pub::APPID;
		$timeStamp = time();
	    $jsApiObj["timeStamp"] = "$timeStamp";
	    $jsApiObj["nonceStr"] = $this->createNoncestr();
		$jsApiObj["package"] = "prepay_id=$this->prepay_id";
	    $jsApiObj["signType"] = "MD5";
	    $jsApiObj["paySign"] = $this->getSign($jsApiObj);
	    $this->parameters = json_encode($jsApiObj);
		
		return $this->parameters;
	}
}


class Redpack_pub extends Wxpay_client_pub{
    function __construct(){
        $this->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }
    function createXml(){
        try{
            if($this->parameters["mch_billno"] == null){
                throw new SDKRuntimeException("缺少发红包接口必填参数mch_billno！"."<br>");
            }elseif($this->parameters["nick_name"] == null){
                throw new SDKRuntimeException("缺少发红包接口必填参数nick_name！"."<br>");
            }elseif ($this->parameters["send_name"] == null ) {
                throw new SDKRuntimeException("缺少发红包接口必填参数send_name！"."<br>");
            }elseif ($this->parameters["total_amount"] == null) {
                throw new SDKRuntimeException("缺少发红包接口必填参数total_amount！"."<br>");
            }elseif($this->parameters["min_value"] == null){
                throw new SDKRuntimeException("缺少发红包接口必填参数min_value！"."<br>");
            }elseif ($this->parameters["max_value"] == null ) {
                throw new SDKRuntimeException("缺少发红包接口必填参数max_value！"."<br>");
            }elseif ($this->parameters["total_num"] == null) {
                throw new SDKRuntimeException("缺少发红包接口必填参数total_num！"."<br>");
            }elseif ($this->parameters["wishing"] == null) {
                throw new SDKRuntimeException("缺少发红包接口必填参数wishing！"."<br>");
            }elseif ($this->parameters["act_name"] == null) {
                throw new SDKRuntimeException("缺少发红包接口必填参数act_name！"."<br>");
            }elseif ($this->parameters["remark"] == null) {
                throw new SDKRuntimeException("缺少发红包接口必填参数remark！"."<br>");
            }
            $this->parameters["wxappid"] = WxPayConf_pub::APPID;
            $this->parameters["mch_id"] = WxPayConf_pub::MCHID;
            $this->parameters["client_ip"] = $_SERVER['REMOTE_ADDR'];
            $this->parameters["nonce_str"] = $this->createNoncestr();
            $this->parameters["re_openid"] = $this->parameters["re_openid"];
            $this->parameters["sign"] = $this->getSign($this->parameters);
            return  $this->arrayToXml($this->parameters);
        }catch (SDKRuntimeException $e){
            die($e->errorMessage());
        }
    }
    
    
    function sendRedpack(){
        $this->postXmlSSL();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }

    function createOauthUrlForCode($redirectUrl){
        $urlObj["appid"] = WxPayConf_pub::APPID;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }
    

    function createOauthUrlForOpenid(){
        $urlObj["appid"] = WxPayConf_pub::APPID;
        $urlObj["secret"] = WxPayConf_pub::APPSECRET;
        $urlObj["code"] = $this->code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    function getOpenid() {
        $url = $this->createOauthUrlForOpenid();
        $ch = curl_init();
        curl_setopt($ch, CURLOP_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($res,true);
        $this->openid = $data['openid'];
        return $this->openid;
    }
    function setCode($code_){
        $this->code = $code_;
    }
}


//企业付款

class Withdrawals extends Wxpay_client_pub{
    function __construct(){
        $this->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }
    function createXml(){
        try{
            if($this->parameters["partner_trade_no"] == null){
                throw new SDKRuntimeException("缺少商户订单号"."<br>");
            }elseif($this->parameters["check_name"] == null){
                throw new SDKRuntimeException("缺少检洨用户名"."<br>");
            }elseif ($this->parameters["amount"] == null ) {
                throw new SDKRuntimeException("缺少付款金额！"."<br>");
            }elseif ($this->parameters["desc"] == null) {
                throw new SDKRuntimeException("缺少付款说明！"."<br>");
            }
            $this->parameters["mch_appid"] = $this->parameters["mch_appid"];//公众账号appid
            $this->parameters["mchid"] = $this->parameters["mchid"];//商户号
			$this->parameters["partner_trade_no"] = $this->parameters["partner_trade_no"];//商户订单号
			$this->parameters["re_user_name"] = $this->parameters["re_user_name"];//付款人姓名
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//终端ip
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["openid"] = $this->parameters["openid"];//用户openid
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
			
            return  $this->arrayToXml($this->parameters);
        }catch (SDKRuntimeException $e){
            die($e->errorMessage());
        }
    }
    
    
    function sendMerchantCash(){
        $this->postXmlSSL();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }

}

//查询企业付款

class gettransferinfo extends Wxpay_client_pub{
    function __construct(){
        $this->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo";
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }
    function createXml(){
        try{
            if($this->parameters["mch_id"] == null){
                throw new SDKRuntimeException("缺少商户号"."<br>");
            }elseif($this->parameters["appid"] == null){
                throw new SDKRuntimeException("缺少appid"."<br>");
            }elseif ($this->parameters["partner_trade_no"] == null ) {
                throw new SDKRuntimeException("缺少商户订单号"."<br>");
            }
            $this->parameters["mch_id"] = $this->parameters["mch_id"];//商户ID
            $this->parameters["appid"] = $this->parameters["appid"];//APPid
			$this->parameters["partner_trade_no"] = $this->parameters["partner_trade_no"];//商户订单号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return  $this->arrayToXml($this->parameters);
        }catch (SDKRuntimeException $e){
            die($e->errorMessage());
        }
    }
    
    
    function sendtransferinfo(){
        $this->postXmlSSL();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }

}
