<?php
class AccessAction extends CommonAction{
	
	public function _initialize() {
        parent::_initialize();
        $this->assign('owners', D('Communityowner')->where(array('community_id' => $this->community_id,'audit'=>1,'closed'=>0))->select());
    }
	
	
	 public function index(){
        $this->display();
    }
    public function lists(){
        $obj = D('CommunityAccessList');
        import('ORG.Util.Page');
        $map = array('community_id' => $this->community_id);
        if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['lock_sn|sim_no'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $obj->order(array('list_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->checkCreate();
			$data['community_id'] = $this->community_id;
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
        $data = $this->checkFields($this->_post('data', false), array('lock_sn','sim_no','intro'));
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
			if($detail['community_id'] != $this->community_id){
				$this->tuError('非法操作');
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
	//设备用户列表
	public function user($list_id = 0){
		$list_id = (int) $list_id;
		$detail = D('CommunityAccessList')->find($list_id);
        $obj = D('CommunityAccessUser');
        import('ORG.Util.Page');
        $map = array('community_id' => $this->community_id,'list_id'=>$list_id);
        if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['owner_id|user_id'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->order(array('id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $v){
            if($user = D('Users')->where(array('user_id'=>$v['user_id']))->find()){
                $list[$k]['user'] = $user;
            }
			if($owner = D('Communityowner')->where(array('owner_id'=>$v['owner_id']))->find()){
                $list[$k]['owner'] = $owner;
            }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('detail', $detail);
        $this->display();
		
    }
	//设备添加用户
	 public function user_create($list_id = 0){
		$list_id = (int) $list_id;
		if($list_id){
			if($this->isPost()) {
				$data = $this->user_validate($list_id,$type = 1);
				$data['list_id'] = $list_id;
				$data['community_id'] = $this->community_id;
				$obj = D('CommunityAccessUser');
				if($obj->add($data)){
					$this->tuSuccess('添加成功', U('access/user',array('list_id'=>$list_id)));
				}else{
					$this->tuError('操作失败');
				}
			} else {
				$this->assign('list_id', $list_id);
				$this->display();
			}
		}else{
			$this->tuError('非法错误');
		}
        
    }
	//编辑设备用户
	 public function user_edit($list_id = 0,$id = 0){
		$list_id = (int) $list_id;
		$id = (int) $id;
		if($list_id && $id){
			$obj = D('CommunityAccessUser');
			if(!$detail = $obj->find($id)){
				$this->tuError('内容不存在');
			}
			if($detail['community_id'] != $this->community_id){
				$this->tuError('非法操作');
			}
			if($this->isPost()) {
				$data = $this->user_validate($list_id,$type = 2);
				$data['id'] = $id;
				$data['list_id'] = $list_id;
				if($obj->save($data)){
					$this->tuSuccess('编辑成功', U('access/user',array('list_id'=>$list_id)));
				}else{
					$this->tuError('编辑失败');
				}
			} else {
				$this->assign('detail', $detail);
				$this->assign('list_id', $list_id);
				$this->display();
			}
		}else{
			$this->tuError('错误');
		}
        
    }

	//删除模块用户
    public function user_delete($list_id = 0,$id = 0){
		$list_id = (int) $list_id;
		$id = (int) $id;
        if($list_id && $id){
            $obj = D('CommunityAccessUser');
			if(!$detail = $obj->find($id)){
				$this->tuError('内容不存在');
			}
			if($detail['community_id'] != $this->community_id){
				$this->tuError('非法操作');
			}
			if($obj->delete($id)){
				$this->tuSuccess('删除成功', U('access/user',array('list_id'=>$list_id)));
			}else{
				$this->tuError('删除失败');
			}
        }else{
			$this->tuError('ID不存在');
		}
    }
	
	
	//添加编辑设备用户验证
    public function user_validate($list_id,$type){
        $data = $this->checkFields($this->_post('data', false), array('owner_id','user_id','bg_date','end_date'));
        $data['owner_id'] = htmlspecialchars($data['owner_id']);
        if(empty($data['owner_id'])){
            $this->tuError('业主ID不能为空');
        }
		if(!$Communityowner = D('Communityowner')->find($data['owner_id'])){
            $this->tuError('业主信息不存在');
        }
		if(!$Communityowner['user_id']){
			$this->tuError('业主信息错误');
		}
		$data['user_id'] = $Communityowner['user_id'];
		if($type ==1){
			if(D('CommunityAccessUser')->where(array('list_id'=>$list_id,'owner_id'=>$data['owner_id']))->find()){
				$this->tuError('请不要重复添加业主【'.$Communityowner['name'].'】');
			}
		}
		$data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->tuError('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->tuError('开始时间格式不正确');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuError('结束时间格式不正确');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	//用户开门记录列表
	public function open($list_id = 0,$id = 0){
		$list_id = (int) $list_id;
		$id = (int) $id;
        if($list_id && $id){
		    $obj = D('CommunityAccessUserOpen');
			import('ORG.Util.Page');
			$map = array('community_id' => $this->community_id,'list_id'=>$list_id,'id'=>$id);
			if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
				$map['owner_id|user_id'] = array('LIKE', '%' . $keyword . '%');
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
			}
			$this->assign('list', $list);
			$this->assign('page', $show);
			$this->assign('list_id', $list_id);
			$this->assign('id', $id);
        $this->display();
		}else{
			$this->tuError('非法错误');
		}
     
		
    }
}