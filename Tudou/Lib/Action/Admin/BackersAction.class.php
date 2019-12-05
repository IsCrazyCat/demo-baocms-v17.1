<?php
class BackersAction extends CommonAction {
   public function _initialize() {
        parent::_initialize();
		$this->assign('ranks', D('Userrank')->fetchAll());
    }
    public function index() {
        $obj = D('Users');
        import('ORG.Util.Page');
        $map = array('is_backers' => array('IN', '1,2,3'));
        if($keyword = $this->_param('keyword','htmlspecialchars')){
            $map['user_id|account|nickname|mobile|email|ext0'] = array('LIKE','%'.$keyword.'%');
            $this->assign('keyword',$keyword);
        }
        if ($rank_id = (int) $this->_param('rank_id')) {
            $map['rank_id'] = $rank_id;
            $this->assign('rank_id', $rank_id);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$rank_ids = array();
        foreach ($list as $k => $val) {
			$rank_ids[$val['rank_id']] = $val['rank_id'];
			$list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('rank', D('Userrank')->itemsByIds($rank_ids));
        $this->display();
    }
	
    public function audit($user_id = 0) {
        $obj = D('Users');
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
			if($obj->where(array('user_id' => $user_id))->save(array('is_backers' => 2))){
				$this->tuSuccess('推手审核成功', U('backers/index'));
			}else{
				$this->tuError('推手审核失败');
			}
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                foreach ($user_id as $id) {
					$obj->where(array('user_id' => $id))->save(array('is_backers' => 2));
                }
                $this->tuSuccess('审核成功', U('backers/index'));
            }
            $this->tuError('请选择要审核的推手');
        }
    }
   
	//推手奖励日志
	public function reward(){
        $obj = D('UsersBackersRewardLog'); 
        import('ORG.Util.Page');
		$map = array();
		if ($log_id = (int) $this->_param('log_id')) {
            $map['log_id'] = $log_id;
            $this->assign('log_id', $log_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count,25);
        $show = $Page->show();
        $list = $obj->where($map)->order('log_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k => $v){
			if($v['order_id']){
				$order = D('Order')->where(array('order_id'=>$v['order_id']))->find();
            	$list[$k]['order'] = $order;
			}
			if($v['user_id']){
				$user = D('Users')->where(array('user_id'=>$v['user_id']))->find();
            	$list[$k]['user'] = $user;
			}
			if($v['shop_id']){
				$shop = D('Shop')->where(array('shop_id'=>$v['shop_id']))->find();
            	$list[$k]['shop'] = $shop;
			}
			if($v['good_id']){
				$good = D('Goods')->where(array('good_id'=>$v['good_id']))->find();
            	$list[$k]['good'] = $good;
			}
            
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
	
	   
	//推手折扣返还日志
	public function discount(){
        $obj = D('UsersBackersDiscountLog'); 
        import('ORG.Util.Page');
		$map = array();
		if ($log_id = (int) $this->_param('log_id')) {
            $map['log_id'] = $log_id;
            $this->assign('log_id', $log_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count,25);
        $show = $Page->show();
        $list = $obj->where($map)->order('log_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k => $v){
			if($v['order_id']){
				$order = D('Order')->where(array('order_id'=>$v['order_id']))->find();
            	$list[$k]['order'] = $order;
			}
			if($v['user_id']){
				$user = D('Users')->where(array('user_id'=>$v['user_id']))->find();
            	$list[$k]['user'] = $user;
			}
			if($v['shop_id']){
				$shop = D('Shop')->where(array('shop_id'=>$v['shop_id']))->find();
            	$list[$k]['shop'] = $shop;
			}
			if($v['good_id']){
				$good = D('Goods')->where(array('good_id'=>$v['good_id']))->find();
            	$list[$k]['good'] = $good;
			}
            
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
	
	
}
