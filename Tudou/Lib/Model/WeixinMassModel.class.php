<?php
class WeixinMassModel extends CommonModel{
    protected $pk = 'mass_id';
    protected $tableName = 'weixin_mass';
	
	private $curl = null;
	

	//预览接口
	 public function preview($mass_id){
		if($detail = $this->find($mass_id)){
			$data = array(
				'touser' => 'otB7Bwk09qcwJaGZOiuHt1rnEvVc',
				'mpnews' =>array(
					'media_id' => $detail['media_id']
				),
				'msgtype' => 'mpnews',
			);
			$data = json_encode($data);
			import("@/Net.Curl");
			$this->curl = new Curl();
			$token = D('Weixin')->getToken($shop_id);
			$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token='.$token;
			$result = $this->curl->post($url,$data);
			$result = json_decode($result,true);
			if($result['errcode']){
				$this->error = $result['errcode'].'错误原因：'.$result['errmsg'];
             	return false;
       		}
			return true;
        }else{
			 $this->error = '信息不存在';
             return false;
		}
	}
	
	//群发接口
	 public function send($mass_id){
		if($detail = $this->find($mass_id)){
			$lists = D('Connect')->where(array('type'=>'weixin'))->field('open_id')->select();
			foreach ($lists as $key => $val) {
				if(!empty($val['open_id'])) {
					$arr2[] = $val['open_id'];
				}
			}
			$list =implode('","',$arr2);
			//$list =  str_replace(",",'","',$list);
			$data = array(
				'touser' =>[$list],
				'mpnews' =>array(
					'media_id' => $detail['media_id']
				),
				'msgtype' => 'mpnews',
				'title' => urlencode($detail['title']),
				'description' => urlencode($detail['content']),
				'thumb_media_id' => $detail['thumb_media_id'],
				'send_ignore_reprint' => '0',
			);
			
			//$data = json_encode($data);
			$data = urldecode(json_encode($data));
			$data =  str_replace("\\",'',$data);
			
			//p($data);die;
			import("@/Net.Curl");
			$this->curl = new Curl();
			$token = D('Weixin')->getToken($shop_id);
			$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.$token;
			$result = $this->curl->post($url,$data);
			$result = json_decode($result,true);
			//p($result);die;
			//转义代码没做
			if($result['errcode']){
				$this->error = $result['errcode'].'错误原因：'.$result['errmsg'];
             	return false;
       		}
			return true;
        }else{
			 $this->error = '信息不存在';
             return false;
		}
	}
	//转义函数
	public function escape($detail){
		foreach ($detai as &$item){
			foreach ($item as $k=>$v){
				if($k =='content'){
					$item[$k] = urlencode(htmlspecialchars(str_replace("\"","'",$v)));
				}else{
					$item[$k] = urlencode($v);
				}
			}
		}
	}
	
	
}