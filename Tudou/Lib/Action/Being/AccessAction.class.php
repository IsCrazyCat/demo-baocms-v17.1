<?php
class AccessAction extends CommonAction{
	
	public function _initialize() {
        parent::_initialize();
    }

    public function lists(){
        $obj = D('CommunityAccessList');
        import('ORG.Util.Page');
        $map = array();
        if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['lock_sn|sim_no'] = array('LIKE', '%' . $keyword . '%');
        }
		if($community_id = (int) $this->_param('community_id')) {
            $map['community_id'] = $community_id;
            $community = D('Community')->find($community_id);
            $this->assign('name', $community['name']);
            $this->assign('community_id', $community_id);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $obj->order(array('list_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $v){
			if($community = D('Community')->where(array('community_id'=>$v['community_id']))->find()){
				$list[$k]['community'] = $community;
			}
		}
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->checkCreate();
            $obj = D('CommunityAccessList');
			$res = $obj->addAccessList($data['lock_sn']);
			$data['state'] = $res['state'];
			$data['state_code'] = $res['state_code'];
			if($res['state'] == 1){
				if($obj->add($data)){
					$this->tuSuccess('添加成功', U('access/lists'));
				}else{
					$this->tuError('操作失败');
				}
			}else{
				$this->tuError($res['state_code'].'--'.$res['state_msg']);
			}
        } else {
            $this->display();
        }
    }
    public function checkCreate(){
        $data = $this->checkFields($this->_post('data', false), array('community_id','lock_sn','sim_no','intro'));
		$data['community_id'] = (int) $data['community_id'];
        if (empty($data['community_id'])) {
            $this->tuError('小区不能为空');
        }
        $data['lock_sn'] = htmlspecialchars($data['lock_sn']);
        if(empty($data['lock_sn'])){
            $this->tuError('序列号不能为空');
        }
		$data['sim_no'] = htmlspecialchars($data['sim_no']);
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('备注不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('备注含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	//删除模块
    public function delete($list_id = 0){
        if($list_id = (int) $list_id){
            $obj = D('CommunityAccessList');
			if(!$detail = $obj->find($list_id)){
				$this->tuError('内容不存在');
			}
			$res = $obj->Dellock($list_id);
			if($res['state'] == 1){
				$obj->delete($list_id);
				$obj->delete_user_open($list_id);
				$this->tuSuccess('删除成功', U('access/lists'));
			}else{
				$this->tuError($res['state_code'].'--'.$res['state_msg']);
			}
        }else{
			$this->tuError('ID不存在');
		}
    }
	//查询模块状态
	 public function state($list_id = 0){
        if($list_id = (int) $list_id){
            $obj = D('CommunityAccessList');
			$res = $obj->Lockstate($list_id);
			if($res['state'] == 1){
				$obj->save(array('list_id'=>$list_id,'online'=>$res['data']['online'],'query_time'=>NOW_TIME));
				$this->tuSuccess($res['state_msg'], U('access/lists'));
			}else{
				$this->tuSuccess($res['state_msg'], U('access/lists'));
			}
        }else{
			$this->tuError('ID不存在');
		}
    }
	
	//用户开门记录列表
	public function open(){
		$obj = D('CommunityAccessUserOpen');
		import('ORG.Util.Page');
		$map = array();
		if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
			$map['owner_id|user_id'] = array('LIKE', '%' . $keyword . '%');
		}
		if($community_id = (int) $this->_param('community_id')) {
            $map['community_id'] = $community_id;
            $community = D('Community')->find($community_id);
            $this->assign('name', $community['name']);
            $this->assign('community_id', $community_id);
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
		$Page = new Page($count, 25);
		$show = $Page->show();
		$list = $obj->order(array('open_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $v){
			if($access = D('CommunityAccessList')->where(array('list_id'=>$v['list_id']))->find()){
				$list[$k]['access'] = $access;
			}
			if($user = D('Users')->where(array('user_id'=>$v['user_id']))->find()){
				$list[$k]['user'] = $user;
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
       	$this->display();
    }
	
	
}