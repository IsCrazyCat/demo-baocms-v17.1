<?php



class WxappAction extends CommonAction{
	
	
	
	public function Url2(){
		$config = D('Setting')->fetchAll();
		$json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$config['site']['imgurl']);
        $json_str = json_encode($json_arr);
        exit($json_str); 
		
	}
	
	
	public function Url(){
		$config = D('Setting')->fetchAll();
		$json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$config['site']['host']);
        $json_str = json_encode($json_arr);
        exit($json_str); 
		
	}
	
	public function Views(){
		$views = D('Life')->sum('views');
		$json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$views);
        $json_str = json_encode($json_arr);
        exit($json_str); 
		
	}
    public function Num(){
		$count = D('Life')->where(array('audit' => 1, 'closed' => 0))->count();
		$this->ajaxReturn(array('status' => '1', 'msg' => '获取成功','data'=>$count));
		
	}
	
	public function type(){
		$cate = D('Lifecate')->getChannelMeans();
		
	    $arr = array();
	    foreach($cate as $k => $v){
		   $arr[$k]['id'] = $k;
		   $arr[$k]['type_name'] = $v;
		   $kk = $k ;
		   $arr[$k]['img'] = __HOST__.'/static/default/wap/image/life/life_cate_'.$kk.'.png';	
	    } 
		
		$arr = array_values($arr);
        $json_str = json_encode($arr);
        exit($json_str); 
		
	}
	
	
    public function map(){
		$config = D('Setting')->fetchAll();
		//$res['mapkey'] = 'IRTBZ-7KIR5-E6CIW-QEK5Z-EZIU5-JVFV7';
		$res['mapkey'] = $config['wxapp']['qqmap_key'];
		$op = I('op','','trim,htmlspecialchars');
        $url = "https://apis.map.qq.com/ws/geocoder/v1/?location=".$op."&key=".$res['mapkey']."&get_poi=0&coord_type=1";
        $html = file_get_contents($url);
	   //p($html);die;
        echo  $html;
	
		
	}
	
	
	//首页黄高
	public function Ad(){
		$list = D('Ad')->where(array('site_id'=>'57','closed'=>'0'))->select();
		foreach ($list as $k => $val){
			$list[$k]['type'] = 1;
			$list[$k]['id'] = $val['ad_id'];
			$list[$k]['img'] = strpos($val['photo'],"http")===false ?  __HOST__.$val['photo'] : $val['photo'];
		}
        $json_str = json_encode($list);
        exit($json_str); 
		
	}
	
	//帖子列表
	public function List2(){
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0);
		
		if($type_id = I('type_id','','trim')){
			$Lifecate = D('Lifecate')->where(array('channel_id'=>$type_id))->select();
			foreach($Lifecate as $val) {
				$cate_ids[$val['cate_id']] = $val['cate_id'];
			}
			$map['cate_id'] = array('in', $cate_ids);
        }
		
		$count = D('Life')->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = D('Life')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
        foreach($list as $k => $val){
			$Users = D('Users')->find($val['user_id']);
			$list[$k]['id'] = $val['life_id'];
		    $list[$k]['user'] = $Users;
		    $list[$k]['user_img'] = config_weixin_img($Users['face']);			
		    $list[$k]['user_name'] = config_user_name($Users['nickname']);
			$list[$k]['type_name'] = $this->getListChannel($val['cate_id']);//分类
			$list[$k]['type2_name'] = D('Lifecate')->where(array('cate_id'=>$val['cate_id']))->getField('cate_name');//分类
			$list[$k]['hb_money'] = D('LifePacket')->where(array('life_id'=>$val['life_id'],'closed'=>0,'status'=>1))->getField('packet_surplus_money');//红包剩余多少钱	
			$list[$k]['label'] = D('LifeCateTag')->where(array('cate_id'=>$val['cate_id']))->select();
			$list[$k]['time'] = $val['create_time'];
			$list[$k]['sh_time'] = $val['create_time'];
			$list[$k]['img'] = $this->getListPics($val['life_id']);
			$list[$k]['img1'] = $this->getListPics($val['life_id']);
			$Lifedetails = D('Lifedetails')->find($val['life_id']);
			$list[$k]['details'] = cleanhtml($Lifedetails['details']);
			if(empty($list[$k]['img'])){
				unset($list[$k]);
			}
		}
		foreach($list as $k => $val){
			$data2[]=array(
			  'tz'=>$list[$k],
			  'label'=>array(),
			 );
		}
        $json_str = json_encode($data2);
        exit($json_str); 
		
	}

	//获取频道
	public function getListChannel($cate_id){
		$Lifecate = D('Lifecate')->where(array('cate_id'=>$cate_id))->find();
		$this->lifechannel = D('Lifecate')->getChannelMeans();
		return $this->lifechannel[$Lifecate['channel_id']];
	}
	
	
	//获取列表图片开始
	public function getListPics($life_id){
		$list = D('Lifephoto')->getPics($life_id);
		foreach($list as $k => $val){
			$photos[$k] = config_weixin_img($val['photo']);
		}
		$Life = D('Life')->find($life_id);
		if($Life['photo']){
			$photo = config_weixin_img($Life['photo']);
			array_unshift($photos,$photo);
		}
		$res = implode(",",$photos);
		return "".$res ."";
	}
	
	public function SaveHotCity(){
		  $cityname = I('cityname','','trim,htmlspecialchars');
		  $user_id = I('user_id','','trim');
		 
		 //不知道微信吗写入
			echo  '1';
		
	}
	
	
	public function news(){
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0);
		$count = D('Article')->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if($Page->totalPages < $p){
            die('0');
        }
		$list = D('Article')->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
			$list[$k]['type'] = 1;
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	
	public function Storelist(){
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0);
		$count = D('Shop')->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		$list = D('Shop')->where(array('closed'=>'0'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			
			$list[$k]['id'] = $val['shop_id'];
			$list[$k]['time'] = $val['create_time'];
			$list[$k]['img'] = strpos($val['photo'],"http")===false ?  __HOST__.$val['photo'] : $val['photo'];
		}
	
        $json_str = json_encode($list);
        exit($json_str); 
		
	}
	
	
 	public function System(){
		$config = D('Setting')->fetchAll();
		$res['config'] = $config;
		$res['many_city'] = 2;
		$res['color'] = $config['other']['color'];
		$res['pt_name'] = $config['site']['sitename'];	
		$res['gd_key'] = $config['wxapp']['gd_key'];	
		$City = D('City')->find($config['site']['city_id']);
		$res['city_name'] = $City['name'];
		
		$json_str = json_encode($res);
        exit($json_str); 
		
		
	}
	
	public function openid(){
		$this->ajaxReturn(array('status' => '1', 'msg' => '获取成功','data'=>883));
		
	}
	
	public function Nav(){
		$this->ajaxReturn(array('status' => '1', 'msg' => '获取成功','data'=>883));
		
	}
	
	public function Login(){
		$this->ajaxReturn(array('status' => '1', 'msg' => '获取成功','data'=>883));
		
	}
}