<?php
class ZheAction extends CommonAction {
	public function _initialize() {
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['zhe'])) {
            $this->error('五折卡功能已关闭');
            die;
        }
    }
  
   public function index(){
		$aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
	
	public function loaddata(){
        $Zheyuyue = D('Zheyuyue');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'closed' => 0);
		$bg_time = strtotime(TODAY);
        $aready = (int) $this->_param('aready');
        if ($aready == -1) {//已过期
            $map['is_used'] = -1;
        } elseif ($aready == 1) {
            $map['is_used'] = 1;
        } else {//待消费未过期
            $aready == null;
			$map['is_used'] = 0;
        }
		$this->assign('aready', $aready);
        $count = $Zheyuyue->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Zheyuyue->where($map)->order(array('yuyue_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
            if($zhe = D('Zhe')->where(array('zhe_id'=>$v['zhe_id']))->find()){
               $list[$k]['zhe'] = $zhe;
            }
			if($shop = D('Shop')->where(array('shop_id'=>$v['shop_id']))->find()){
               $list[$k]['shop'] = $shop;
            }
			if($users = D('Users')->where(array('user_id'=>$v['user_id']))->find()){
               $list[$k]['users'] = $users;
            }
			if(($v['create_time'] < $bg_time) && ($v['is_used'] == 0)){ //如果超过了今天
			    $Zheyuyue->save(array('yuyue_id'=>$v['yuyue_id'],'is_used'=> -1));
				$list[$k]['is_used'] = -1;
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display(); 
    }
	//五折卡详情
	public function detail($yuyue_id){
        if(!$yuyue_id = (int)$yuyue_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('Zheyuyue')->find($yuyue_id)){
            $this->error('该订单不存在');
        }elseif($detail['shop_id'] != $this->shop_id){
            $this->error('非法的订单操作');
        }else{
           $detail['zhe'] = D('Zhe')->where(array('zhe_id'=>$detail['zhe_id']))->find(); 
           $detail['shop'] = D('Shop')->where(array('shop_id'=>$detail['shop_id']))->find();
		   $detail['users'] = D('Users')->where(array('user_id'=>$detail['user_id']))->find();
           $this->assign('detail',$detail);
           $this->display();
        }
    }

	
	//核销二维码
	public function used(){
        $yuyue_id = (int) $this->_param('yuyue_id');
		$obj = D('Zheyuyue');
		if(!$obj->zhe_verify_yuyue($yuyue_id,$this->shop_id)){//判断一切错误
			$this->error($obj->getError());	  
		}else{
			$this->error('我们已向用户手机发送短信，正在为您跳转到下一页', U('zhe/verify',array('yuyue_id'=>$yuyue_id)));
		}
    }
	
	//核销二维码
	public function verify($yuyue_id = 0){
		$obj = D('Zheyuyue');
        $yuyue_id = (int) $yuyue_id;
		if(!$detail = $obj->find($yuyue_id)){
            $this->error('该订单不存在');
        }
		if ($this->isPost()) {
			$yzm = $this->_post('yzm');
			$zhe_used_mobile = session('zhe_used_mobile');
			$zhe_used_code = session('zhe_used_code');
			if (empty($yzm)) {
			   $this->tuMsg('请输入短信验证码');
			}
			if ($detail['mobile'] != $zhe_used_mobile) {
			   $this->tuMsg('手机号码和收取验证码的手机号不一致');
			}
			if ($yzm != $zhe_used_code) {
			   $this->tuMsg('短信验证码不正确');
			}
			if(false !== $obj->complete($detail['yuyue_id'])){
				$this->tuMsg('验证成功', U('zhe/index',array('aready'=>1)));
			}else{
				$this->tuMsg('操作失败');
			 }
		}else {
			$this->assign('detail',$detail);
            $this->display();
        }
		
    }
	
	 //验证码
    public function check() {
		$yuyue_id = (int) $this->_param('yuyue_id');
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            if (empty($code)) {
                $this->tuMsg('请输入验证码');
            }
            $obj = D('Zheyuyue');
            if(!$detail = $obj->where(array('code'=> $code))->find()){
			   $this->tuMsg('该订单不存在或者验证码错误');
		    }
			if(!$obj->zhe_verify_yuyue($detail['yuyue_id'],$this->shop_id)){//判断一切错误
				$this->tuMsg($obj->getError());	  
			}else{
				$this->tuMsg('我们已向用户手机发送短信，正在为您跳转到下一页', U('zhe/verify',array('yuyue_id'=>$detail['yuyue_id'])));
			}
        } else {
            $this->display();
        }
    }
}
