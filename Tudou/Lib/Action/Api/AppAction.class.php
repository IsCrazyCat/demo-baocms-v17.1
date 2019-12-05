<?php



class AppAction extends CommonAction{
	
	//短信信息直接输出开启
	public function IsSms(){
		echo 1;
	}
	
	//获取系统帮助文章
	public function GetHelp(){
		$res = D('Systemcontent')->order(array('create_time' => 'desc'))->limit(0,6)->select();
		foreach($res as $k => $val){
			$res[$k]['id'] = $val['content_id'];
		    $res[$k]['detail'] = cleanhtml($val['details']);
	    }
        $json_str = json_encode($res);
        exit($json_str); 
	}
	
	//发送短信 
	public function Sms(){
		$tel = I('tel','','trim,htmlspecialchars');
		$code = I('code','','trim,htmlspecialchars');
		$res = D('Sms')->sms_yzm($tel,$code);//发送验证码
		$data=file_get_contents($res);
		print_r($data);
	} 
	
	
	
	public function uploadify(){
        $model = I('model');
		import('ORG.Net.UploadFile');
		$upload = new UploadFile(); 
		$upload->maxSize = 3145728; 
		$upload->allowExts = array('jpg', 'gif', 'png', 'jpeg'); 
		$name = date('Y/m/d', NOW_TIME);
		$dir = BASE_PATH . '/attachs/' . $name . '/';
		if(!is_dir($dir)){
			mkdir($dir, 0755, true);
		}
		$upload->savePath = $dir;
		if(isset($this->_CONFIG['attachs'][$model]['thumb'])) {
			$upload->thumb = true;
			if(is_array($this->_CONFIG['attachs'][$model]['thumb'])) {
				$prefix = $w = $h = array();
				foreach($this->_CONFIG['attachs'][$model]['thumb'] as $k=>$v){
					$prefix[] = $k.'_';
					list($w1,$h1) = explode('X', $v);
					$w[]=$w1;
					$h[]=$h1;
				}
				$upload->thumbPrefix = join(',',$prefix);
				$upload->thumbMaxWidth =join(',',$w);
				$upload->thumbMaxHeight =join(',',$h);
			}else{
				$upload->thumbPrefix = 'thumb_';
				list($w, $h) = explode('X', $this->_CONFIG['attachs'][$model]['thumb']);
				$upload->thumbMaxWidth = $w;
				$upload->thumbMaxHeight = $h;
			}
		}
		if(!$upload->upload()){
			var_dump($upload->getErrorMsg());//上传错误提示错误信息
		}else{
			$info = $upload->getUploadFileInfo();
			if(!empty($this->_CONFIG['attachs']['water'])){
				import('ORG.Util.Image');
				$Image = new Image();
				$Image->water(BASE_PATH . '/attachs/'. $name . '/thumb_' . $info[0]['savename'],BASE_PATH . '/attachs/'.$this->_CONFIG['attachs']['water']);
			}
			if($upload->thumb) {
                $picurl =  $this->_CONFIG['site']['host'].'/attachs/'.$name . '/thumb_' . $info[0]['savename'];
                 echo $picurl;
           }else{
                $picurl = $this->_CONFIG['site']['host'].'/attachs/'.$name . '/' . $info[0]['savename'];
                echo $picurl;
           }
		 }
    }
	
	
}