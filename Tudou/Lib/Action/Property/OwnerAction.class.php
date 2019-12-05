<?php
class OwnerAction extends CommonAction{
    public function index(){
        $obj = D('Communityowner');
        import('ORG.Util.Page');
        $map = array('community_id' => $this->community_id,'closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['number|location'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $obj->order(array('owner_id' => 'desc'))->where($map)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
	public function create(){
        if ($this->isPost()) {
			$data['community_id'] = $this->community_id;
			$data['user_id'] = (int) $_POST['user_id'];
			if(empty($data['user_id'])){
                $this->tuError('必须选择会员');
            }
			if($res = D('Communityowner')->where(array('community_id'=>$this->community_id,'user_id'=>$data['user_id'],'closed'=>'0'))->find()){
                $this->tuError('请不要重复添加当前会员');
            }
            $data['name'] = htmlspecialchars($_POST['name']);
            if(empty($data['name'])){
                $this->tuError('称呼不能为空');
            }
			$data['number'] = htmlspecialchars($_POST['number']);
            if(empty($data['number'])){
                $this->tuError('户号不能为空');
            }
			$data['location'] = htmlspecialchars($_POST['location']);
            if(empty($data['location'])){
                $this->tuError('地址不能为空');
            }
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            if ($owner_id = D('Communityowner')->add($data)){
                $this->tuSuccess('添加成功', U('owner/index'));
            }
            $this->tuError('操作失败');
        }else{
            $this->display();
        }
    }
	
	public function edit($owner_id){
		
		$owner_id = (int) $owner_id;
        if(empty($owner_id)) {
            $this->error('该业主不存在');
        }
        if(!($detail = D('Communityowner')->find($owner_id))){
            $this->error('该业主不存在');
        }
        if($detail['community_id'] != $this->community_id){
            $this->error('不能操作其他小区业主');
        }
		
		
        if ($this->isPost()) {
            $data['name'] = htmlspecialchars($_POST['name']);
            if(empty($data['name'])){
                $this->tuError('称呼不能为空');
            }
			$data['number'] = htmlspecialchars($_POST['number']);
            if(empty($data['number'])){
                $this->tuError('户号不能为空');
            }
			$data['location'] = htmlspecialchars($_POST['location']);
            if(empty($data['location'])){
                $this->tuError('地址不能为空');
            }
            if ($owner_id = D('Communityowner')->save($data)){
                $this->tuSuccess('编辑成功', U('owner/index'));
            }
            $this->tuError('编辑失败');
        }else{
			$this->assign('detail', $detail);
            $this->display();
        }
    }
	
	
	public function select(){
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['account|nickname|mobile|user_id|email|ext0'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = D('Users')->where($map)->count();
        $Page = new Page($count, 8);
        $pager = $Page->show();
        $list = D('Users')->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        $this->display();
    }
	
    public function audit($owner_id){
        $owner_id = (int) $owner_id;
        if (empty($owner_id)) {
            $this->error('该业主不存在');
        }
        if (!($detail = D('Communityowner')->find($owner_id))) {
            $this->error('该业主不存在');
        }
        if ($detail['community_id'] != $this->community_id) {
            $this->error('不能操作其他小区业主');
        }
        if ($this->isPost()) {
            $data['number'] = (int) $_POST['number'];
            if (empty($data['number'])) {
                $this->tuError('户号不能为空');
            }
            $data['owner_id'] = $owner_id;
            $data['audit'] = 1;
            $obj = D('Communityowner');
            if (false !== $obj->save($data)) {
                $this->tuSuccess('审核成功', U('owner/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function delete() {
        $owner_id = (int) $this->_get('owner_id');
        $obj = D('Communityowner');
        $detail = $obj->find($owner_id);
        if (!empty($detail) && $detail['community_id'] == $this->community_id) {
			if(D('Communityorder')->where(array('user_id'=>$detail['user_id']))->find()){
				$this->error('该用户还有账单无法删除');	
			}
			if($obj->save(array('owner_id'=>$owner_id,'closed'=>1))){
				$this->success('删除成功', U('owner/index'));	
			}else{
				 $this->error('操作失败');
			}
        }
        $this->error('操作失败');
    }
}