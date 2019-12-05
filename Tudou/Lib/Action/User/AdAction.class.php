<?php
class AdAction extends CommonAction{

    public function index(){
        $this->display();
    }

    public function apply(){
		
     if($this->isPost()){
        $data = $this->checkFields($this->_post('data', false),array('user_id', 'title','city_id', 'active_time','prestore_integral','link_url','photo','site_id', 'ad_id'));
 		$data['photo'] = htmlspecialchars($data['photo']);
        if(empty($data['photo'])){
			 $this->ajaxReturn(array('code' => '0', 'msg' => '请上传图片素材'));
        }
        if(!isImage($data['photo'])){
			$this->ajaxReturn(array('code' => '0', 'msg' => '素材图片格式不正确'));
        }
        $data['link_url'] = htmlspecialchars($data['link_url']);
        if(empty($data['link_url'])){
			$this->ajaxReturn(array('code' => '0', 'msg' => '图片链接地址不能为空'));
        }
        $map['site_id'] = $data['site_id'];
		
		$Adsite = D('Adsite')->find($map['site_id']);
		if(!$Adsite){
			$this->ajaxReturn(array('code' => '0', 'msg' => '广告不存在'));
		}
        $map['city_id'] = $data['city_id'];
        $map['user_id'] = $this->uid;
        if(D('Adrecord')->where($map)->select()){
			$this->ajaxReturn(array('code' => '0', 'msg' => '相同位置广告已存在,请勿重复添加'));
        }
        $data['user_id'] = $this->uid;
        $data['title'] = $Adsite['site_name'];
        $data['create_time'] = date('Y-m-d H:i:s',NOW_TIME);
        $data['create_ip'] = get_client_ip();
		
        if(D('Adrecord')->add($data)){
			$this->ajaxReturn(array('code' => '1', 'msg' => '恭喜您申请成功，等待平台审核','url'=>U('member/index')));
        }else{
			$this->ajaxReturn(array('code' => '0', 'msg' => '申请失败'));
		}
     }else{
		 $site_id = "57,68,75,77,30,31,73,78,79,32,33,34,80,70,61,35,81,82";
		 $this->assign('integral',$this->member['integral']);
		 $this->assign('sites',$adsite = D('Adsite')->where('site_id in ('.$site_id.')')->select());
		 $this->assign('place', D('Adsite')->getPlace());
		 $this->assign('types', D('Adsite')->getType());
		 $this->display();
     }
  }



	//获取广告位价格
	public function getsiteprice(){
		$site_id = I('site_id');
		if(!$site_id){
           $this->ajaxReturn(array('code' => '0', 'msg' => '请选择广告位'));
        }
		if(!$detail = D('Adsite')->find($site_id)){
			$this->ajaxReturn(array('code' => '0', 'msg' => '广告位不存在'));
		}
		if($detail['site_price']){
			$this->ajaxReturn(array('code' => '1', 'price' => $detail['site_price']));
		}else{
			$this->ajaxReturn(array('code' => '0', 'msg' => '当前广告位价格配置不正确暂时无法购买'));
		}
    }
	
	
   public function applylog(){
  	 $this->display();
   }
   
   public function applylog_data(){ 	
  	    $applylog = D('Adrecord');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $applylog->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();      
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $adsite = D('Adsite');
        $list = $applylog->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k=>$v){
		  $map['site_id'] = $v['site_id'];
		  $result = $adsite->where($map)->find();
		  $list[$k]['site_name'] = $result['site_name'];
     	} 
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
   }

   public function adlist(){
      $this->display();
   }

   public function adlist_data(){
        $adlist = D('Ad');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $adlist->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();      
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $adsite = D('Adsite');
        $list = $adlist->where($map)->order(array('ad_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $city =  D('city');
		foreach($list as $k=>$v){
		  $map['site_id'] = $v['site_id'];    
		  $result = $adlist->where($map)->find();
		  $adsiteinfo = $adsite->where($map)->find();
		  $cityinfo = $city->where(array('city_id'=>$result['city_id']))->find();
		  $list[$k]['cityname'] = $cityinfo['name'];
		  $list[$k]['site_name'] = $adsiteinfo['site_name'];
		  $list[$k]['bgtime'] =  strtotime($result['bg_date']);
		  $list[$k]['endtime'] =  strtotime($result['end_date']);
		} 
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
   }
   
   
}