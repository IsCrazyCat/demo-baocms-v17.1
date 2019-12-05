<?php
class CommunityAction extends CommonAction {
	protected function _initialize(){
        parent::_initialize();
        if(!$this->_CONFIG['operation']['community']){
            $this->error('此功能已关闭');die;
        }
    }
    public function index() {
        $this->display(); 
    }

    public function user() {
        $this->display(); 
    }
	public function user_load(){
		$obj = D('CommunityAccessUser');
        import('ORG.Util.Page');
        $map = array('user_id'=>$this->uid);
        if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['owner_id|user_id'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
		$var = C('VAR_PAGE')?C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->order(array('id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $v){
            if($user = D('Users')->where(array('user_id'=>$v['user_id']))->find()){
                $list[$k]['user'] = $user;
            }
			if($access = D('CommunityAccessList')->where(array('list_id'=>$v['list_id']))->find()){
                $list[$k]['access'] = $access;
            }
			if($owner = D('Communityowner')->where(array('owner_id'=>$v['owner_id']))->find()){
                $list[$k]['owner'] = $owner;
            }
			if($community = D('Community')->where(array('community_id'=>$v['community_id']))->find()){
                $list[$k]['community'] = $community;
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('detail', $detail);
        $this->display(); 
    }
	
	//生成二维码开门
	 public function qrcode($list_id = 0,$id = 0,$time = 0){
        $list_id = (int) $list_id;
		$id = (int) $id;
		$time = $time;//时间戳
		
        if(!$detail = D('CommunityAccessUser')->find($id)){
			$this->error('用户不存在');
		}
		if($detail['user_id'] != $this->uid){
			$this->error('非法操作');
		}
		if(!$access = D('CommunityAccessList')->find($list_id)){
			$this->error('门禁不存在');
		}
        $url = $this->_CONFIG['site']['host'].''.U('wap/api/unlock', array('id' =>$id,'list_id' =>$list_id,'time' => $time,'sign' => md5($time . C('AUTH_KEY') . NOW_TIME)));
        $token = 'time_' . $time;
        $file = tuQrCode($token, $url);
		$this->assign('file', $file);
        $this->assign('time', $time);
        $this->assign('detail', $detail);
		$this->assign('access', $access);
        $this->display();
    }
	
	
	//用户更新查询模块状态
	 public function state($list_id = 0,$id = 0){
		$list_id = (int) $list_id;
		$id = (int) $id;
        if($list_id && $id){
			if(!$detail = D('CommunityAccessUser')->find($id)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'不存在'));
			}
			if($detail['user_id'] != $this->uid){
				$this->ajaxReturn(array('code'=>'0','msg'=>'非法操作'));
			}
			if($detail['bg_date'] >= TODAY){
				$this->ajaxReturn(array('code'=>'0','msg'=>'门禁还没开始生效'));
			}
			if($detail['end_date'] <= TODAY){
				$this->ajaxReturn(array('code'=>'0','msg'=>'门禁已过有效期'));
			}
            $obj = D('CommunityAccessList');
			$res = $obj->Lockstate($list_id);
			if($res['state'] == 1){
				$obj->save(array('list_id'=>$list_id,'online'=>$res['data']['online'],'query_time'=>NOW_TIME));
				$this->ajaxReturn(array('code'=>'1','msg'=>'更新门禁状态成功','url'=>U('community/user')));
			}else{
				$this->ajaxReturn(array('code'=>'1','msg'=>'更新门禁状态成功','url'=>U('community/user')));
			}
        }else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'非法错误'));
		}
    }
	
	//用户开门
	 public function open($id = 0){
        if($id = (int) $id){
			if(!$detail = D('CommunityAccessUser')->find($id)){
				$this->ajaxReturn(array('code'=>'0','msg'=>'不存在'));
			}
			if($detail['user_id'] != $this->uid){
				$this->ajaxReturn(array('code'=>'0','msg'=>'非法操作'));
			}
			if($detail['bg_date'] >= TODAY){
				$this->ajaxReturn(array('code'=>'0','msg'=>'门禁还没开始生效'));
			}
			if($detail['end_date'] <= TODAY){
				$this->ajaxReturn(array('code'=>'0','msg'=>'门禁已过有效期'));
			}
			if(false == D('CommunityAccessUserOpen')->user_access_open($type = '0',$id,$this->uid)){
				$this->ajaxReturn(array('code'=>'0','msg'=>D('CommunityAccessUserOpen')->getError()));
			}else{
				$this->ajaxReturn(array('code'=>'1','msg'=>'开门成功','url'=>U('community/user')));
			}
        }else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'非法错误'));
		}
    }
    public function community_load() {
		import('ORG.Util.Page');
		$Communityusers = D('Communityusers')->where(array('user_id' => $this->uid))->order(array('join_id' => 'desc'))->limit(0,20)-> select();	
		foreach ($Communityusers as $val) {
			$community_ids[$val['community_id']] = $val['community_id'];
		}
		$map = array('community_id' => array('IN',$community_ids));
		$count = D('Community')->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
		$var = C('VAR_PAGE')?C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		$list = D('Community')->order(array('community_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $v){
			if($owner = D('Communityowner')->where(array('community_id'=>$v['community_id'],'user_id' => $this->uid))->find()){
				$list[$k]['owner'] = $owner;
			}
		}
		$this->assign('list', $list);
        $this->assign('page', $show);	
        $this->display();

    }
	public function tongzhi(){
         $this->display(); 

    }
	public function tongzhi_load() {
		$obj = D('Communitynews');
		import('ORG.Util.Page'); 
		$map = array('user_id' => $this->uid);
		$joined = D('Communityusers')->where($map)->order(array('join_id' => 'desc'))->select();
		foreach ($joined as $val) {
			$cmm_ids[$val['community_id']] = $val['community_id'];
		}
		$maps['community_id']  = array('in',$cmm_ids);
		$count = $obj->where($maps)->count(); 
        $Page = new Page($count, 5); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE')?C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		$news = $obj->where($maps)->order(array('news_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$community_ids = array();
        foreach ($news as $k => $val) {
            if ($val['community_id']) {
                $community_ids[$val['community_id']] = $val['community_id'];
            }
        }
        if ($community_ids) {
            $this->assign('communitys', D('Community')->itemsByIds($community_ids));
        }
		$this->assign('list', D('Community')->itemsByIds($cmm_ids));
		$this->assign('news', $news);
		$this->assign('page', $show); 
		$this->display();


    }

	

	public function newsdetail($news_id) {
        $news_id = (int)$news_id;
        if(!$detail = D('Communitynews')->find($news_id)){
            $this->error('该问题不存在');
        }

        if($detail['closed'] != 0){
            $this->error('该问题已被删除');
        }

		$new_id = $detail['community_id'];
        $community = D('Community')->find($new_id);
		$this->assign('community', $community);
        $this->assign('detail',$detail);
        $this->display();

    }

	public function feedback(){
		 $this->assign('nextpage', LinkTo('community/feedback_load', array('t' => NOW_TIME, 'community_id' => $this->community_id, 'p' => '0000')));
         $this->display(); 
    }

	public function feedback_load() {
		$feedback = D('Feedback');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'user_id' => $this->uid);
        $count = $feedback->where($map)->count(); 
        $Page = new Page($count, 5); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE')?C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $feedback->order(array('feed_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$community_ids = array();
        foreach ($list as $k => $val) {
            if ($val['community_id']) {
                $community_ids[$val['community_id']] = $val['community_id'];
            }
        }
        if ($community_ids) {
            $this->assign('communitys', D('Community')->itemsByIds($community_ids));
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

	public function feedbackdetail($feed_id) {
        $feed_id = (int)$feed_id;
        if(!$detail = D('Feedback')->find($feed_id)){
            $this->error('该问题不存在');
        }
        if($detail['closed'] != 0){
            $this->error('该问题已被删除');
        }
        if($detail['user_id'] != $this->uid){
            $this->error('请不要查看他人的问题反馈');
        }
        $this->assign('detail',$detail);
        $this->display();
    }

	public function order(){
		$bg_time = strtotime(TODAY);
	    $list = D('Communityorder')->order()->where(array('user_id' => $this->uid))->select();
        $order_ids = array();
        foreach ($list as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
        }
		$counts['order'] = (int) D('Communityorderproducts')->where(array('order_id' => array('IN', $order_ids)))->sum('money');
		$counts['order_0'] = (int) D('Communityorderproducts')->where(array(
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');

		
		$counts['order_1'] = (int) D('Communityorderproducts')->where(array(
			'order_id' => array('IN', $order_ids),
			'is_pay'=>1
		))->sum('money');
			
		//小区账单
		$counts['order_type_1'] = (int) D('Communityorderproducts')->where(array('order_id' => array('IN', $order_ids)))->sum('money');
		
		$counts['order_type_1_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>1,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
			
		$counts['order_type_2'] = (int) D('Communityorderproducts')->where(array('type'=>2,'user_id' => $this->uid))->sum('money');
		$counts['order_type_2_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>2,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
		
		$counts['order_type_3'] = (int) D('Communityorderproducts')->where(array('type'=>3,'user_id' => $this->uid))->sum('money');
		$counts['order_type_3_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>3,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
			
		$counts['order_type_4'] = (int) D('Communityorderproducts')->where(array('type'=>4,'user_id' => $this->uid))->sum('money');
		$counts['order_type_4_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>4,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
			
		$counts['order_type_5'] = (int) D('Communityorderproducts')->where(array('type'=>5,'user_id' => $this->uid))->sum('money');
		$counts['order_type_5_is_pay'] = (int) D('Communityorderproducts')->where(array(
			'type'=>5,
			'order_id' => array('IN', $order_ids),
			'is_pay'=>0
		))->sum('money');
		$this->assign('nextpage', LinkTo('community/order_load', array('t' => NOW_TIME, 'user_id' => $this->uid, 'p' => '0000')));
		$this->assign('counts', $counts);
        $this->display(); // 输出模板;
    }
       
		
	public function order_load() {
		$orders = D('Communityorder');
        import('ORG.Util.Page'); 
        $map = array('user_id' => $this->uid);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $map['order_date'] = array(array('ELT', $end_date), array('EGT', $bg_date));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $this->assign('bg_date', $bg_date);
                $map['order_date'] = array('EGT', $bg_date);
            }

            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $this->assign('end_date', $end_date);
                $map['order_date'] = array('ELT', $end_date);
            }
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $this->assign('user_id', $user_id);
        }
        $count = $orders->where($map)->count(); 
        $Page = new Page($count, 5); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE')?C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $orders->order(array('order_date' => 'desc'))->where($map)->select();
        $user_ids = $order_ids  = $community_ids= array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
			$community_ids[$val['community_id']] = $val['community_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('communitys', D('Community')->itemsByIds($community_ids));
        $products = D('Communityorderproducts')->where(array('order_id' => array('IN', $order_ids)))->select();
        foreach ($list as $k => $val) {
            foreach ($products as $kk => $v) {
                if ($v['order_id'] == $val['order_id']) {
                    $list[$k]['type' . $v['type']] = $v;
                }
            }
        }
		
		
        $this->assign('list', $list);
        $this->assign('page', $show); 
        $this->display();

    }

	 public function tieba() {
        $this->display(); 
    }

    public function tieba_load() {
       $Post = D('Communityposts');
		import('ORG.Util.Page');
		$map = array('user_id' => $this->uid, 'closed' => 0); 
		$count = $Post->where($map)->count();
		$Page = new Page($count, 5); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		$list = $Post->where($map)->order(array('post_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
			$ids = array();
			if ($val['user_id']) {
				$ids[$val['user_id']] = $val['user_id'];
				$ids[$val['last_id']] = $val['last_id'];
			}
			$list[$k] = $val;
		}
		$this->assign('users', D('Users')->itemsByIds($ids));
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->display();

    }

	 public function tiebadelete($post_id = 0) {
            $obj = D('Communityposts');
            $obj->save(array('post_id' => $post_id, 'closed' => 1));
            $this->success('删除成功', U('community/tieba'));
    }

   

}