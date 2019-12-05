<?php
class IntegralAction extends CommonAction {
	public function _initialize() {
        parent::_initialize();
    }
  
   public function index(){
		$aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
	//加载店员
	public function loaddata(){
        $obj = D('Userintegralcancel');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id,'worker_id' =>$this->workers['worker_id']);
        $count = $obj->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array('cancel_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
			if($users = D('Users')->where(array('user_id'=>$v['user_id']))->find()){
               $list[$k]['users'] = $users;
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        echo $this->fetch();
    }
	
	
	//店员核销二维码
	public function verify($user_id = 0,$type = 0){
		$obj = D('Users');
        $user_id = (int) $user_id;
		$type = (int) $type;
		if(!$detail = $obj->find($user_id)){
            $this->error('该会员不存在');
        }
		if ($this->isPost()) {
			$integral = (int) $this->_post('integral');
			if (empty($integral)) {
			   $this->tuMsg('核销的积分不能为空');
			}
			if ($integral > $detail['integral']) {
			   $this->tuMsg('核销的积分不能大于该会员的总积分');
			}
			$yzm = $this->_post('yzm');
			$cancel_used_mobile = session('cancel_used_mobile');
			$cancel_used_code = session('cancel_used_code');
			if (empty($yzm)) {
			   $this->tuMsg('请输入短信验证码');
			}
			if ($detail['mobile'] != $cancel_used_mobile) {
			   $this->tuMsg('手机号码和收取验证码的手机号不一致');
			}
			if ($yzm != $cancel_used_code) {
			   $this->tuMsg('短信验证码不正确');
			}
			$intro = '店员【'.$this->workers['name'].'】积分核销：' . $integral.'所属商家'.$this->shop['shop_name'];
			if(false !== D('Userintegralcancel')->complete($detail['user_id'],$this->shop_id,$type,$this->workers['worker_id'],$integral,$intro)){
				$this->tuMsg('恭喜店员'.$this->workers['name'].'验证成功', U('integral/index',array('aready'=>1)));
				session('cancel_used_code', null);
			}else{
				session('cancel_used_code', null);
				$this->tuMsg('操作失败');
			 }
		}else {
			$this->assign('type',$type);
			$this->assign('detail',$detail);
            $this->display();
        }
		
    }
	
	//店员核销验证码
    public function used() {
		$user_id = (int) $this->_param('user_id');
		$obj = D('Userintegralcancel');
		if(!$obj->integral_cancel_verify($user_id,$this->shop_id)){//判断一切错误
			$this->error($obj->getError());	  
		}else{
			$this->error('尊敬的店员，我们已向用户手机发送短信，正在为您跳转到下一页', U('integral/verify',array('user_id'=>$user_id,'type'=>2)));
		}
    }
}
