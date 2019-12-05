<?php
class MemberAction extends CommonAction{
	
	
 public function pay($code= ''){
        $logs_id = (int) $this->_param('logs_id');
		$code = $this->_param('code', 'htmlspecialchars');
		
		$name = ($code =='money') ? '余额' : '积分';
		
		if(empty($this->uid)){
            $this->error('UID不存在', U('user/money/index'));
        }
        if(empty($logs_id)){
            $this->error('支付ID不存在', U('user/money/index'));
        }
		if(empty($code)){
            $this->error('支付方式不存在', U('user/money/index'));
        }
        if(!($detail = D('Paymentlogs')->find($logs_id))){
            $this->error('支付记录不存在', U('user/money/index'));
        }
        if($detail['code'] != $code){
            $this->error('支付方式不正确', U('user/money/index'));
        }
		
		if($detail['need_pay'] <= 0){
            $this->error('支付金额有误', U('user/money/index'));
        }
		
        $member = D('Users')->find($this->uid);
        if($detail['is_paid']){
            $this->error('支付日志状态错误', U('user/money/index'));
        }
		
		if($code == 'money' && $member['money'] < $detail['need_pay']){
			$this->error('余额不足无法支付', U('user/money/index'));
		}
		
        if($code == 'integral' && $member['integral'] < $detail['need_pay']){
			$this->error('您积分账户不足无法支付', U('user/index/index'));
		}
		
		$intro = '【'.$name.'】支付'.round($detail['need_pay']/100,2).'元，支付ID('.$logs_id.')，原始订单ID：【'.$detail['order_id'].'】';
		if($code == 'money'){
			$member['money'] -= $detail['need_pay'];
			if(D('Users')->save(array('user_id' => $this->uid,'money' => $member['money']))){
				D('Usermoneylogs')->add(array(
					'user_id' => $this->uid, 
					'money' => - $detail['need_pay'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'intro' => $intro
				));
			}
		}
		
		if($code == 'integral'){
			$member['integral'] -= $detail['need_pay'];
			if(D('Users')->save(array('user_id' =>$this->uid, 'integral' => $member['integral']))){
				D('Userintegrallogs')->add(array(
					'user_id' => $this->uid, 
					'integral' => - $detail['need_pay'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'intro' => $intro
				));
			}
			
		}
		
		D('Paymentlogs')->where(array('log_id'=>$logs_id))->save(array('code'=>$code));//更新支付方式
		D('Payment')->logsPaid($logs_id);//回调函数
		
        if($detail['type'] == 'ele'){
			$this->success('恭喜您外卖订单付款成功', U('eleorder/detail',array('order_id'=>$detail['order_id'])));
        }elseif($detail['type'] == 'booking') {
			$this->success('恭喜您付款成功', U('booking/detail',array('order_id'=>$detail['order_id'])));
        }elseif($detail['type'] == 'farm') {
			$this->success('恭喜您付款成功', U('farm/detail',array('order_id'=>$detail['order_id'])));
        }elseif($detail['type'] == 'appoint') {
			$this->success('恭喜您家政支付成功啦', U('appoint/detail',array('order_id'=>$detail['order_id'])));
        }elseif($detail['type'] == 'running') {
			$this->success('恭喜您付款成功', U('user/running/index'));
        }elseif($detail['type'] == 'goods'){
			if($detail['order_id']){
				$this->success('商城订单支付成功啦', U('goods/detail',array('order_id'=>$detail['order_id'])));
			}else{
				$this->success('商城合并付款支付成功啦', U('goods/index',array('aready'=>'1')));
			}
        }elseif($detail['type'] == 'edu') {
			$this->success('恭喜您支付成功啦', U('edu/detail',array('order_id'=>$detail['order_id'])));
        }elseif($detail['type'] == 'community') {
            $this->success('恭喜您小区缴费成功啦', U('community/order'));
        }elseif($detail['type'] == 'stock') {
			$this->success('恭喜您支付成功啦', U('stock/detail',array('order_id'=>$detail['order_id'])));
        }elseif($detail['type'] == 'money') {
            $this->success('恭喜您充值成功', U('member/index'));
	    }elseif($detail['type'] == 'book'){
            $this->success('恭喜您付款成功', U('book/detail',array('order_id'=>$detail['order_id'])));
	    }elseif($detail['type'] == 'breaks'){
            $this->success('恭喜您买单付款成功', U('breaks/detail',array('order_id'=>$detail['order_id'])));
	    }elseif($detail['type'] == 'life'){
				$this->success('恭喜您分类信息付款成功', U('life/index'));
			}else{
            $this->success('恭喜您付款成功', U('member/index'));
        }
    }
	
	
	

    public function index(){
        if (empty($this->uid)) {
            header('Location: ' . U('Wap/passport/login'));
            die;
        }
		$this->assign('card', $Usercard = D('Usercard')->where(array('user_id' => $this->uid))->find());
        $this->assign('shop', $Shop = D('Shop')->where(array('user_id' => $this->uid))->find());
        $this->display();
    }
   
	public function zhe(){
		$obj = D('Zheorder');
		$map = array('user_id'=>$this->uid,'closed'=>0,'status'=>1,'end_time' => array('EGT', NOW_TIME));
		$count = $obj->where($map)->count();
		if($count > 1){
			$this->error('五折卡状态不正确，请联系管理员处理');
		}
        $detail = $obj->where($map)->find();
		$detail['user'] = D('Users')->find($detail['user_id']);
		$this->assign('detail',$detail);
        $this->display('zhe_detail');
    }
	
	public function zhe_yuyue(){
		$aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
	
	public function zhe_yuyue_loaddata(){
        $Zheyuyue = D('Zheyuyue');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'closed' => 0);
		$bg_time = strtotime(TODAY);
		$NOW_TIME = strtotime(TODAY) + 86000;//今天晚上的时间
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
	public function zhe_yuyue_detail($yuyue_id){
        if(!$yuyue_id = (int)$yuyue_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('Zheyuyue')->find($yuyue_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法的订单操作');
        }else{
           $detail['zhe'] = D('Zhe')->where(array('zhe_id'=>$detail['zhe_id']))->find(); 
           $detail['shop'] = D('Shop')->where(array('shop_id'=>$detail['shop_id']))->find();
		   $detail['users'] = D('Users')->where(array('user_id'=>$detail['user_id']))->find();
           $this->assign('detail',$detail);
           $this->display();
        }
    }
	
	//五折卡详情
	public function zhe_yuyue_qrcode($yuyue_id){
        if(!$yuyue_id = (int)$yuyue_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('Zheyuyue')->find($yuyue_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法的订单操作');
        }else{
           $url = U('seller/zhe/used', array('yuyue_id' => $yuyue_id, 't' => NOW_TIME, 'sign' => md5($yuyue_id . C('AUTH_KEY') . NOW_TIME)));
		   $token = 'zhe_yuyue_id_' . $yuyue_id;
		   $file = tuQrCode($token, $url);
		   $this->assign('file', $file);
           $this->assign('detail',$detail);
           $this->display();
        }
    }
  
   
   
	public function fabu(){
        $this->display();
    }
   
  
    public function xiaoxizhongxin(){
        $msg = D('Msg');//用户收到的总通知
        $msg_common = $msg->where(array('is_used' => 0,'is_fenzhan'=>0))->count();
        $msg_qita = $msg->where(array('user_id' => $this->uid, 'is_used' => 0,'is_fenzhan'=>0))->count();
        $this->assign('msg_common', $msg_common);
        $this->assign('msg_qita', $msg_qita);
        $message = D('Message');
        $message = $message->where('user_id =' . $this->uid)->count();
        $this->assign('message', $message);
        $counts = array();
        $bg_time = strtotime(TODAY);//今日时间，需要统计其他的下面写。
        $counts['message_xiaoqu'] = (int) D('Message')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $counts['mesg'] = (int) D('Msg')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $this->assign('counts', $counts);
        $this->display();
    }
    public function zijinguanli(){
        $this->display();
    }
    public function xiaoqu(){
        $this->assign('community', D('Community')->where(array('user_id' => $this->uid, 'closed' => 0, 'audit' => 1))->count());//加入的小区
        $this->assign('feedback', D('Feedback')->where(array('user_id' => $this->uid, 'closed' => 0))->count());//报修数量
        $this->assign('communityorder', D('Communityorder')->where(array('user_id' => $this->uid))->count()); //账单
        $this->assign('tieba', D('Communityposts')->where(array('user_id' => $this->uid))->count());//账单//统计今日新的数量
        $counts = array();
        $bg_time = strtotime(TODAY);//今日时间，需要统计其他的下面写。
        $counts['feedback_today'] = (int) D('Feedback')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $counts['communityorder_today'] = (int) D('Communityorder')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $counts['tieba_today'] = (int) D('Communityposts')->where(array('user_id' => $this->user_id, 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $this->assign('counts', $counts);
        $this->display();
    }
}