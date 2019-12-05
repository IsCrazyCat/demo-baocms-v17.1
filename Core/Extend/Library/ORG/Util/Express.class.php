<?php
class Express{
	public $type = '';//KEY
	public $keys = '';//KEY
	public $company = 'suer';//快递公司编码
	public $customer = '';//公司编号
	public $num = '0';//快递单号


	public function getContent($type = 1){
		$config = D('Setting')->fetchAll();
		
		if($config['config']['express_api_type'] == 1){
			$typeCom = $this->company;//快递公司
			$typeNu = $this->num;  //快递单号
			$AppKey=$this->keys;//请将XXXXXX替换成您在http://kuaidi100.com/app/reg.html申请到的KEY
			$url ='http://api.kuaidi100.com/api?id='.$AppKey.'&com='.$typeCom.'&nu='.$typeNu.'&show=2&muti=1&order=asc';
			//p($url);
			//请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
			$powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ';
			//优先使用curl模式发送数据
			if(function_exists('curl_init') == 1){
			  $curl = curl_init();
			  curl_setopt ($curl, CURLOPT_URL, $url);
			  curl_setopt ($curl, CURLOPT_HEADER,0);
			  curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
			  curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			  curl_setopt ($curl, CURLOPT_TIMEOUT,5);
			  $get_content = curl_exec($curl);
			  curl_close ($curl);
			}
			$res = $get_content . '<br/>' . $powered;
			return $res;
		}else{
			//参数设置
			$post_data = array();
			$post_data["customer"] = $this->customer;
			$key= $this->keys;
			$post_data["param"] =  '{"com":"'.$this->company.'","num":"'.$this->num.'"}';
			$url='http://poll.kuaidi100.com/poll/query.do';
			$post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
			$post_data["sign"] = strtoupper($post_data["sign"]);
			$o=""; 
			foreach ($post_data as $k=>$v){
				$o.= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
			}
			$post_data=substr($o,0,-1);
			//去查询结果
		
			import("@/Net.Curl");
			$this->curl = new Curl();
			$result = $this->curl->post($url,$post_data);
			$result = json_decode($result,true);
		
			
			if($result['message'] == 'ok'){
				foreach ($result['data'] as $k =>$val) {
					$str .= '<p class="express-time">时间："' . $val['time'] .'</p><br/>';
					$str .= '<p class="express-ftime">更新时间："' . $val['ftime'] .'</p><br/>';
					$str .= '<p class="express-context">说明："' . $val['context']  .'</p><br/>';
					$str .= '<p class="express-line">----------------分割线----------------------</p><br/>';
                }
				return $str;
			}else{
				return '查询数据错误'.$result['message'];
			}
			
			//return $result['message'];
		}
	}

}