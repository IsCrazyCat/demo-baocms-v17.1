<?php
class ApiAction extends CommonAction {
	
	
	
	 //用户打开门禁
	 public function unlock($id = 0,$list_id  = 0,$time = 0){
		$list_id = (int) $list_id;
		$id = (int) $id;
		$time = $time;
		if(!$res = D('CommunityAccessList')->find($list_id)){
			$this->error('您打开的门禁信息不存在');
		}
		if((time() - $time) >= 600){
			$this->error('当前二维码已经失效');
		}
		if($this->uid){
			$uid = $this->uid;
		}else{
			$uid = 1;
		}
		//游客解锁
		if(false == D('CommunityAccessUserOpen')->touristUnlock($type = '0',$id,$list_id,$uid)){
			$this->error(D('CommunityAccessUserOpen')->getError());
		}else{
			$this->error('开门成功',U('user/member/index'));
		}
			
    }
	
	
	/*public function dingwei() {
        $lat = I('lat');
        $lng = I('lon');
		$address = I('address');
		cookie('lat',$lat,3600);
        cookie('lng',$lng,3600);
		cookie('address',$address,3600);
        $this->ajaxReturn(array('lat' => $lat,'lon' => $lng, 'address' => $address));
    }*/
	
	//获取定位
	public function dingwei(){
        $lat = I('lat');
        $lng = I('lon');
		$address = I('address');
		cookie('lat',$lat,3600);
        cookie('lng',$lng,3600);
		
	
		
		list($code,$city_id,$city_name,$msg,$address,$url) = $this->getDingwei($lat, $lng);
		
						
		
        $this->ajaxReturn(array('la'=>$lat,'lon'=>$lng,'code'=>$code,'city_id'=> $city_id,'city_name'=> $city_name,'msg'=> $msg,'address'=>$address,'url'=>$url));
    }
	
	//返回定位
	public function getDingwei($lat, $lng){
		$config = D('Setting')->fetchAll();
		$addr = $this->getArea($lat, $lng);
		cookie('addr',$addr, 3600);
		if(!empty($addr)) {
			cookie('location', 2);
		}
		$city = mb_substr($addr['city'], 0, -1, 'utf-8');
		$district = mb_substr($addr['district'], 0, -1, 'utf-8');
	
		$position = $city . $district;
		$city = D('Pinyin')->pinyin($city);//城市拼音
		$district = D('Pinyin')->pinyin($district);//地区拼音
		
		$town = D('City')->where(array('pinyin'=>$district,'is_open' =>1))->find();//城市
		$county = D('City')->where(array('pinyin'=>$city,'is_open' =>1))->find();//县城
		
		
		$city_id = cookie('city_id');
		$cityop = cookie('cityop');
		
		
		if(!empty($city_id)){
			if($city_id == $county['city_id'] || $city_id == $town['city_id'] || $cityop == 1) {
				return array($code = 1,$city_id = 999,$msg = '不弹出了',$position);
			}else{
				$City = D('City')->where(array('city_id' =>$city_id))->getField('name');
				if(!empty($county)){
					return array($code = 2,$city_id = $county['city_id'],$city_name = $county['name'],$msg = '县城位置【'.$position.'】',$position,$url = U('city/change', array('city_id' => $county['city_id'])));
				}elseif(!empty($town)){
					return array($code = 3,$city_id = $town['city_id'],$city_name = $town['name'],$msg = '县城位置【'.$position.'】',$position,$url = U('city/change', array('city_id' => $town['city_id'])));
				}
			}
		}else{
			if(!empty($county)){
				return array($code = 4,$city_id = $county['city_id'],$city_name = $county['name'],$msg = '没有cookie情况下成功匹配到县城',$position,$url = U('city/change', array('city_id' => $county['city_id'])));
			}elseif(!empty($town)) {
				return array($code = 5,$city_id = $town['city_id'],$city_name = $town['name'],$msg = '没有cookie情况下已经成功匹配到城市',$position,$url = U('city/change', array('city_id' => $town['city_id'])));
			}else{
				$detail = D('City')->find($config['site']['city_id']);//城市
				return array($code = 6,$city_id = $config['site']['city_id'],$city_name = $detail['name'],$msg = '没有匹配到城市，跳转到默认城市',$position,$url = U('city/change', array('city_id' => $config['site']['city_id'])));

			}
		}
		
		
	}
	
	//通过接口将坐标转地理位置
    function getArea($lat, $lng){
        $url = 'https://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location=' . $lat . ',' . $lng . '&output=json&pois=1';
        $arr = file_get_contents($url);
        $arr = json_decode($arr);
        $place = $pois = $po = array();
        foreach ($arr->result->pois as $value) {
            $po['name'] = $value->name;
            $po['addr'] = $value->addr;
            $pois[] = $po;
        }
        $place['formatted_address'] = $arr->result->formatted_address;
		$place['city'] = $arr -> result -> addressComponent -> city;
		$place['district'] = $arr -> result -> addressComponent -> district;
        $place['pois'] = $pois;
        return $place;
    }
	
	
	//地图展示
	public function maps(){
		$lat = I('lat', '', 'trim,htmlspecialchars');
		$lng = I('lng', '', 'trim,htmlspecialchars');
		$address = I('address', '', 'trim,htmlspecialchars');
		
		$lat2 = addslashes(cookie('lat'));
        $lng2 = addslashes(cookie('lng'));
		
		//p($lat2);die;
		
		
		$this->assign('lat', ($lat ? $lat : ($lat2 ? $lat2 : $this->_CONFIG['site']['lat'])));
		$this->assign('lng', ($lng ? $lng : ($lng2 ? $lng2 : $this->_CONFIG['site']['lng'])));
		
		
        //$this->assign('lat', $lat ? $lat : $this->_CONFIG['site']['lat']);
        //$this->assign('lng', $lng ? $lng : $this->_CONFIG['site']['lng']);
		$this->assign('address', $address);
		
		//p($_SERVER['HTTPS']);
		
        $this->display();
    }
	
	
	
	//新版生成带参数二维码
	public function guideqrcode(){
		$user_id = (int) $this->_param('user_id');
		$detail = D('Users')->find($user_id);
		if($detail){
			$token = 'guide_id_' . $fuid;
			$url = U('user/apply/index', array('guide_id' => $user_id));
			$file = tuQrCode($token, $url);
			$this->assign('file', $file);
			$this->display();
		}else{
			$this->error('错误');
		}
   }
   
   
   //新版生成带参数二维码
	public function qrcode(){
		$fuid = (int) $this->_param('fuid');
		$detail = D('Users')->find($fuid);
		if($fuid && $detail){
			$file = D('Api')->getQrcode($fuid);//生成二维码
			$this->assign('detail',$detail);
			$this->assign('file', $file);
			$this->display();
		}else{
			$this->error('错误');
		}
   }

	//我的海报
 public function poster(){
        $fuid = (int) $this->_param('fuid');
		
		if($fuid){
			if(!$detail = D('Users')->find($fuid)){
				$this->error('会员信息不存在');
			}
			$file = D('Api')->getPoster($fuid);//生成二维码
			$this->assign('detail',$detail);
			$this->assign('file', $file);
			$this->display();
		}else{
			$this->error('错误');
		}
    }
}