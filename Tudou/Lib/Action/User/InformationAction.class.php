<?php
class InformationAction extends CommonAction{
    public function index(){
        $u = D('Users');
        $ud = D('UserAddr');
        $bc = D('Connect');
        $map = array('user_id' => $this->uid);
        $res = $u->where($map)->find();
        $addr_count = $ud->where($map)->count();
        $rbc = $bc->where('uid =' . $this->uid)->select();
        $bind = array();
        foreach ($rbc as $val) {
            $bind[$val['type']] = $val;
        }
        $this->assign('res', $res);
        $this->assign('addr_count', $addr_count);
        $this->assign('bind', $bind);
        $this->display();
    }
	
    public function upload_face(){
        if (!$this->uid) {
            $this->ajaxReturn(array('status' => 'error', 'message' => '您没有登录或登录超时！'));
        } else {
            $avatar = I('avatar', '', 'trim,htmlspecialchars');
            if (!$avatar) {
                $this->ajaxReturn(array('status' => 'error', 'message' => '没有上传头像！'));
            } else {
                $u = D('Users');
                $up = $u->where('user_id =' . $this->uid)->setField('face', $avatar);
                if ($up) {
                    $this->ajaxReturn(array('status' => 'success', 'message' => '修改成功！'));
                } else {
                    $this->ajaxReturn(array('status' => 'error', 'message' => '修改失败！'));
                }
            }
        }
    }
    public function worker($worker_id = 0){
        if (empty($worker_id)) {
            $this->error('访问错误');
        }
        $worker = D('Shopworker')->find($worker_id);
        if (empty($worker)) {
            $this->error('访问错误');
        }
        if ($worker['user_id'] != $this->uid) {
            $this->error('没有权限访问错误');
        }
        if ($worker['status'] == 1) {
            $this->error('您已经同意过这条请求');
        }
        $shop = D('Shop')->find($worker['shop_id']);
        $this->assign('worker', $worker);
        $this->assign('shop', $shop);
        $this->display();
    }
    public function worker_agree($worker_id = 0) {
        if (empty($worker_id)) {
            $this->error('访问错误');
        }
        $worker = D('Shopworker')->find($worker_id);
        if (empty($worker)) {
            $this->error('访问错误');
        }
		if ($worker['status'] == 1) {
            $this->error('您已经确认过了');
        }
        if ($worker['user_id'] != $this->uid) {
            $this->error('没有权限访问错误');
        }
		if(D('Shopworker')->save(array('status' => 1, 'worker_id' => $worker['worker_id']))){
			$this->success('恭喜您成为了该商家的员工', U('worker/index/index'));
		}else{
			$this->error('操作失败');
		}
        
    }
    public function worker_refuse($worker_id = 0){
        if (empty($worker_id)) {
            $this->error('访问错误');
        }
        $worker = D('Shopworker')->find($worker_id);
        if (empty($worker)) {
            $this->error('访问错误');
        }
		if ($worker['status'] == 1) {
            $this->error('您不能执行此操作');
        }
        if ($worker['user_id'] != $this->uid) {
            $this->error('没有权限访问错误');
        }
		if(D('Shopworker')->where(array('worker_id' => $worker['worker_id']))->delete()){
			$this->success('您残忍地拒绝了该商家的请求', U('index/index'));
		}else{
			$this->error('操作失败');
		}
    }
	
	//会员等级购买
	public function buy(){
		$this->assign('rankss', D('Userrank')->where(array('rank_id'=>array('gt',$this->member['rank_id'])))->select());
		$this->assign('payment', D('Payment')->getPayments(true));
		$this->display();
		
	}
	
	//会员等级购买
	public function buylogs(){
		$this->assign('nextpage', LinkTo('information/buyloaddata', array('t' => NOW_TIME,'p' => '0000')));
		$this->display();
		
	}
	
	//会员等级购买日志
	public function buyloaddata(){
		$obj = D('Paymentlogs');
        import('ORG.Util.Page');
        $map = array('is_paid' => 1,'type'=>'rank','user_id' => $this->uid);
        $count = $obj->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if($Page->totalPages < $p){
            die('0');
        }
        $list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $val){
			$list[$k]['rank'] = D('Userrank')->where(array('rank_id'=>$val['types']))->find();
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
		
	}
	//会员等级筛选价格
	public function getprice(){
		$rank_id = I('rank_id');
		if(!$rank_id){
           $this->ajaxReturn(array('code' => '0', 'msg' => '请选择广告位'));
        }
		if(!$detail = D('Userrank')->find($rank_id)){
			$this->ajaxReturn(array('code' => '0', 'msg' => '广告位不存在'));
		}
		if($detail['price']){
			$this->ajaxReturn(array('code' => '1', 'price' => round($detail['price']/100,2)));
		}else{
			$this->ajaxReturn(array('code' => '0', 'msg' => '等级价格配置不正确'));
		}
		
	}
	//会员等级付款渠道
	public function pay($rank_id = 0){
		if(!$rank_id = I('rank_id')){
           $this->ajaxReturn(array('code' => '0', 'msg' => '请选择广告位'));
        }
        if(!($detail = D('Userrank')->find($rank_id))){
			$this->ajaxReturn(array('code'=>'0','msg'=>'等级不存在'));
		}
		if($detail['price'] < 1){
			$this->ajaxReturn(array('code'=>'0','msg'=>'购买金额配置错误'));
		}
		if($this->member['rank_id'] == $detail['rank_id']){
			$this->ajaxReturn(array('code'=>'0','msg'=>'您无需购买此等级'));
		}
		
		$price = I('price','','trim')*100;
		
		$code = I('code','','trim,htmlspecialchars');
		if(!$code){
			$this->ajaxReturn(array('code'=>'0','msg'=>'必须选择支付方式'));
		}
		
		$arr = array(
			'type' => 'rank', 
			'types' => $rank_id,//特别字段
			'user_id' => $this->uid, 
			'order_id' => $order_id, 
			'code' => $code, 
			'need_pay' => $price, 
			'create_time' => time(), 
			'create_ip' => get_client_ip(), 
			'is_paid' => 0
		);
		if($log_id = D('Paymentlogs')->add($arr)){
			$this->ajaxReturn(array('code'=>'1','msg'=>'正在为您跳转支付','url'=>U('wap/payment/payment', array('log_id' =>$log_id))));
		}else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'操作失败'));
		}
    }
	
	
	
	
}