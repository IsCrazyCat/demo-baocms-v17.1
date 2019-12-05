<?php



class PincheAction extends CommonAction{
	
	
	protected function _initialize(){
        parent::_initialize();
        $this->getPincheCate = D('Pinche')->getPincheCate();
        $this->assign('getPincheCate',$this->getPincheCate);
    }
	
	//首页黄高
	public function Ad(){
		$list = D('Ad')->where(array('site_id'=>'57','closed'=>'0'))->select();
		foreach ($list as $k => $val){
			$list[$k]['type'] = 4;
			$list[$k]['id'] = $val['ad_id'];
			$list[$k]['img'] = strpos($val['photo'],"http")===false ?  __HOST__.$val['photo'] : $val['photo'];
		}
        $json_str = json_encode($list);
        exit($json_str); 
		
	}
	//拼车标签
	public function CarTag(){
		$res= '';
        $json_str = json_encode($res);
        exit($json_str); 
	}
	
	//拼车列表
	public function CarList(){
		$obj = D('Pinche');
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0);
		$count = $obj->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['is_open'] = $val['closed'];
			$list[$k]['typename'] = $this->getPincheCate[$val['cate_id']];
			$list[$k]['time'] = $val['create_time'];
			$list[$k]['start_time1'] = $val['create_time'];
			$list[$k]['start_time2'] = $val['create_time'];
			$list[$k]['img'] = strpos($val['photo'],"http")===false ?  __HOST__.$val['photo'] : $val['photo'];
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
	
	//拼车详情
	public function CarInfo(){
		$pinche_id = I('id','','trim');
		$obj = D('Pinche');
		$detail = $obj->find($pinche_id);
		
		$detail['is_open'] = $detail['closed'];
		$detail['img'] = strpos($detail['photo'],"http")===false ?  __HOST__.$detail['photo'] : $detail['photo'];
		$detail['typename'] = $this->getPincheCate[$detail['cate_id']];
		$detail['start_time1'] = $detail['create_time'];
		$detail['start_time2'] = $detail['create_time'];
		$detail['time'] = $detail['create_time'];
		$Users = D('Users')->find($detail['user_id']);
		$detail['user'] = $Users;
		$detail['user']['user_name'] = config_user_name($Users['nickname']);
		$detail['user']['user_img'] = strpos($Users['face'],"http")===false ?  __HOST__.$Users['face'] : $Users['face'];
 		$data['pc']=$detail;
     	$data['tag']=array();
        $json_str = json_encode($data);
        exit($json_str); 
		
	}


	//拼车列表2
	public function TypeCarList(){
		$typename = I('typename','','trim,htmlspecialchars');
		$cate_id = array_search($typename,$this->getPincheCate);
		$obj = D('Pinche');
		import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' =>0,'cate_id'=>$cate_id);
		$count = $obj->where($map)->count();
        $Page = new Page($count,5);
        $show = $Page->show();
        $var = 'page';
        $p = I('p');
        if($Page->totalPages < $p){
            die('');
        }
		$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
			$list[$k]['is_open'] = $val['closed'];
			$list[$k]['typename'] = $this->getPincheCate[$val['cate_id']];
			$list[$k]['time'] = $val['create_time'];
			$list[$k]['start_time1'] = $val['create_time'];
			$list[$k]['start_time2'] = $val['create_time'];
			$list[$k]['img'] = strpos($val['photo'],"http")===false ?  __HOST__.$val['photo'] : $val['photo'];
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

	//发布拼车
	public function car(){
		$data['typename'] = I('typename','','trim,htmlspecialchars');
		$data['cate_id'] = array_search($data['typename'],$this->getPincheCate);
		if(empty($data['cate_id'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'类型错误'));
        }
		$data['city_id'] = I('city_id','','trim');
        if(empty($data['city_id'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'城市id错误'));
        }
		$data['user_id'] = I('user_id','','trim');		
        $data['start_time'] = I('start_time','','trim,htmlspecialchars');
        if(empty($data['start_time'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'出发日期不能为空'));
        }
		$data['goplace'] = I('goplace','','trim,htmlspecialchars');
        if(empty($data['goplace'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'出发地不能为空'));
        }
        $data['toplace'] = I('toplace','','trim,htmlspecialchars');
        if(empty($data['toplace'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'目的地不能为空'));
        }
		$data['middleplace'] = I('middleplace','','trim,htmlspecialchars');
		$data['num_1'] = I('num','','trim');
		$data['num_2'] = I('num','','trim');
		$data['num_3'] = I('num','','trim');
		$data['num_4'] = I('num','','trim');
		$data['name'] = I('name','','trim,htmlspecialchars');
        $data['mobile'] = I('mobile','','trim,htmlspecialchars');
		if(empty($data['mobile'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'手机不能为空'));
        }
        if(!ismobile($data['mobile'])){
			$this->ajaxReturn(array('code'=>'0','msg'=>'手机格式不正确'));
        }
		$data['star_lat'] = I('star_lat','','trim,htmlspecialchars');
        $data['star_lng'] = I('star_lng','','trim,htmlspecialchars');
        $data['end_lat'] = I('end_lat','','trim,htmlspecialchars');
        $data['end_lng'] = I('end_lng','','trim,htmlspecialchars');
        $data['audit'] = 1;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        if($pinche_id = D('Pinche')->add($data)){
			$this->ajaxReturn(array('code'=>'1','msg'=>'发布成功'));
        }
		$this->ajaxReturn(array('code'=>'0','msg'=>'发布失败'));
	}

	public function news(){
		$list = D('Article')->where(array('audit' => 1, 'closed' => 0))->limit(0,5)->select();
		foreach($list as $k => $val){
			$list[$k]['type'] = 3;
		}
        $json_str = json_encode($list);
        exit($json_str); 
	}
	
	
}