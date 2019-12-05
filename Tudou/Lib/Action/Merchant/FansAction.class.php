<?php

class FansAction extends CommonAction {

	public function index() {
		$fans = D('Shopfavorites'); 
		import('ORG.Util.Page'); 
		$map = array('shop_id' => $this->shop_id); 
		if ($keyword = $this->_post('keyword', 'htmlspecialchars')) {
			$maps['user_id|nickname|mobile|account'] = trim($keyword);
			$Users = D('Users');
			$user = $Users->where($maps)->find();
			if (!empty($user)) {
				$map['user_id'] = $user['user_id'];
			}
			$this->assign('keyword', $keyword);
		}
		$count = $fans->where($map)->count(); 
		$Page = new Page($count, 15); 
		$show = $Page->show(); 
		$list = $fans->where($map)->order(array('favorites_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
		foreach ($list as $k => $val) {
			if ($val['user_id']) {
				$user_ids[$val['user_id']] = $val['user_id'];
			}
		}
		if ($user_ids) {
			$this->assign('users', D('Users')->itemsByIds($user_ids));
		}
		$this->assign('list', $list); 
		$this->assign('page', $show); 
		$this->display(); 
	}

	public function add($user_id=0) {
		$fans=D('Shopfavorites');
		$uid=(int)($user_id);
		$user = D('Users')->find($user_id);
		$shop=D('shop')->find($this->shop_id);
		if ($this->isPost()){
			$integral=(int)($_POST['integral']);
			if($integral <= 0){
				$this->tuError('请输入正确的积分');
			}
			if($this->member['integral'] < $integral){
				$this->tuError('您的账户积分不足');
			}
			D('Users')->addIntegral($this->uid,-$integral,'赠送会员积分');
			D('Users')->addIntegral($user_id,$integral,'获得商家赠送积分');
			$this->tuSuccess('赠送积分成功!',U('fans/add',array('user_id'=>$user_id)));
		} else {
			$this->assign('shop', $shop);
			$this->assign('jifen',$this->member['integral']);
			$this->assign('user', $user);
			$this->display();
		}
	}
}
