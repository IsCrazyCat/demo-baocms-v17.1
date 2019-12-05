<?php
class MessageAction extends CommonAction {
    private $create_fields = array('msg_id', 'parent_id', 'send_id', 'content');
    private $edit_fields = array('msg_id', 'parent_id', 'send_id', 'content');

    public function index(){
		$obj = D('Message');
		import('ORG.Util.Page'); 
        $map = array('parent_id' => 0);
		 if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['content'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order('msg_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['send_id']) {
                $ids[$val['send_id']] = $val['send_id'];
            }
        }
		$users = D('Users')->itemsByIds($ids);
        $this->assign('users',$users );
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

	
	public function detail($msg_id){
		$Message = D('Message');
		$detail = $Message->find($msg_id);
		
		 if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['content'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		$Message->save(array('read_time'=>time(),'msg_id'=>$msg_id));
		$this->assign('user', D('Users')->find($detail['send_id'])); 
		$this->assign('detail', $detail); 
		import('ORG.Util.Page'); 
        $map = array('parent_id'=>$msg_id);
        $count = $Message->where($map)->count();
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $Message->where($map)->order('msg_id asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['send_id']) {
                $ids[$val['send_id']] = $val['send_id'];
            }
        }
		$user = $this->uid;
		$users = D('Users')->itemsByIds($ids);
        $this->assign('users',$users);
		$this->assign('user',$user);
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
	}
	
	
	 public function edit($msg_id = 0) {
        if ($msg_id = (int) $msg_id) {
            $obj = D('Message');
            if (!$detail = $obj->find($msg_id)) {
                $this->tuError('请选择要编辑的邻居回复');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['msg_id'] = $msg_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('message/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的邻居回复');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
			$data['content'] = htmlspecialchars($data['content']);
			$data['create_time'] = time();
			if($data['content']==''){
				$this->tuError('信息内容不能为空1');
				die;
			}
        return $data;
    }

	
	public function delete($msg_id = 0) {
        if (is_numeric($msg_id) && ($msg_id = (int) $msg_id)) {
            $obj = D('Message');
			$obj->where(array('parent_id'=>$msg_id))->delete();
			$obj->where(array('msg_id'=>$msg_id))->delete();
            $this->tuSuccess('删除成功', U('message/index'));
        } else {
            $msg_id = $this->_post('msg_id', false);
            if (is_array($msg_id)) {
                $obj = D('Message');
                foreach ($msg_id as $id) {
					$obj->delete($id);
                    $obj->where(array('parent_id'=>$id))->delete();
                }
                $this->tuSuccess('删除成功', U('message/index'));
            }
            $this->tuError('请选择要删除的邻居交友');
        }
    }
	

	
	
}
