<?php
class Access{
	public $appid = '';
	public $appsecret = '';
	public $aeskey = '';
	public $lock_sn = '';
	public $sim_no = '';
	
	
	//加密序列号
	public function getLock_sn(){
		$lock_sn = $this->aesEncrypt($this->lock_sn,$this->aeskey); 
		return $lock_sn;
	}
	
	public function getPostlock(){
		$lock_sn = $this->aesEncrypt($this->lock_sn,$this->aeskey); 
		//p($lock_sn);die;
		$postlock = $this->httpPost('https://www.wmj.com.cn/api/postlock.html?appid='.$this->appid.'&appsecret='.$this->appsecret, $this->getLock_sn());
		$postlock = trim($postlock, "\xEF\xBB\xBF"); //去除BOM头
		return(json_decode($postlock, true));
	}
	//开门接口
	public function getOpenlock(){
		$openlock = $this->httpPost('https://www.wmj.com.cn/api/openlock.html?appid='.$this->appid.'&appsecret='.$this->appsecret, $this->getLock_sn());
		$openlock = trim($openlock, "\xEF\xBB\xBF"); //去除BOM头
		return(json_decode($openlock, true));
	}
	
	//删除模块接口
	public function getDellock(){
		$dellock = $this->httpPost('https://www.wmj.com.cn/api/dellock.html?appid='.$this->appid.'&appsecret='.$this->appsecret, $this->getLock_sn());
		$dellock = trim($dellock, "\xEF\xBB\xBF"); //去除BOM头
		return(json_decode($dellock, true));
	}
	
	//查询模块状态
	public function getLockstate(){
		$lockstate = $this->httpPost('https://www.wmj.com.cn/api/lockstate.html?appid='.$this->appid.'&appsecret='.$this->appsecret, $this->getLock_sn());
		$lockstate = trim($lockstate, "\xEF\xBB\xBF"); //去除BOM头
		return(json_decode($lockstate, true));
	}
	

	
	//SIM卡信息查询接口
	public function getSim(){
		$sim_no = '#########'; //锁的SIM号码，和设备序列号一样，贴在设备上的。
		//$sim_no = aesEncrypt($sim_no, AESKEY);  //传递数据经过AES加密，如果需要的话就用。
		$sim = $this->httpPost('https://www.wmj.com.cn/api/sim.html?appid='.$this->appid.'&appsecret='.$this->appsecret, $sim_no);
		$sim = trim($sim, "\xEF\xBB\xBF"); //去除BOM头
		return(json_decode($sim, true));
	}
	

	function httpPost($url, $str) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, $str);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($str))
		);
		$res = curl_exec ($curl);
		curl_close($curl);
		return $res;
	}

	function aesEncrypt ($value, $key) {
		$padSize = 16 - (strlen($value) % 16);
		$value   = $value . str_repeat(chr($padSize), $padSize) ;
		$output  = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $value, MCRYPT_MODE_CBC, str_repeat(chr(0), 16));
	 
		return base64_encode($output);
	}
 
 
	

}