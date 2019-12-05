<?php
class AllAction extends CommonAction{
    private $create_worker_fields = array('user_id','name', 'photo', 'village_id', 'job','orderby');
	private $edit_worker_fields = array('user_id','name', 'photo', 'village_id', 'job','orderby');
	//乡村通知
	 public function notice(){
        $obj = D('Village_notice');
        import('ORG.Util.Page');
        $map = array('village_id'=>$this->village_id);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	public function notice_create(){
        if ($this->isPost()) {
            $data = $this->_post('data', false);
            $data['title'] = htmlspecialchars($data['title']);
            if(empty($data['title'])) {
                $this->tuMsg('标题不能为空');
            }
			if(empty($data['type'])) {
                $this->tuMsg('类型必须选择');
            }
            if(empty($data['context'])) {
                $this->tuMsg('内容不能为空');
            }
			$data['village_id'] = $this->village_id;
			$data['user_id'] = $this->uid;
            $data['addtime'] = NOW_TIME;
            $obj = D('Village_notice');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuMsg('添加成功', U('all/notice'));
            }
            $this->tuMsg('操作失败');
        } else {
            $this->display();
        }
    }
	
   public function notice_detail($id = 0){
        $id = (int) $this->_param('id');
        $obj = D('Village_notice');
        if (!($detail = $obj->find($id))) {
            $this->error('该通知不存在');
        }
        if ($detail['closed'] != 0) {
            $this->error('该通知已被删除');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
	//删除通知
   public function notice_delete($id = 0){
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Village_notice');
            $detail = $obj->find($id);
            if (empty($detail)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
            }
            if ($detail['village_id'] != $this->village_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '您没有权限访问！'));
            }
            $obj->delete($id);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功', U('all/notice')));
        }
    }
	
    public function suggestion(){
        $Village = D('Village_suggestion');
        import('ORG.Util.Page');
        $map = array('village_id'=>$this->village_id);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $Village->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	 public function suggestion_edit($id = 0){
        if ($id = (int) $id) {
            $obj = D('Village_suggestion');
            if (!($detail = $obj->find($id))) {
                $this->error('请选择要编辑的意见');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['id'] = $id;
				$data['reply'] = htmlspecialchars($data['reply']);
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuMsg('操作成功', U('all/suggestion'));
                }
                $this->tuMsg('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的意见');
        }
    }
	
	//删除反馈
   public function suggestion_delete($id = 0){
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Village_suggestion');
            $detail = $obj->find($id);
            if (empty($detail)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
            }
            if ($detail['village_id'] != $this->village_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '您没有权限访问！'));
            }
            $obj->delete($id);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功', U('all/suggestion')));
        }
    }
	
    public function bbs(){
        $Village = D('Village_bbs');
        import('ORG.Util.Page');
        $map = array('village_id'=>$this->village_id);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $Village->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
   //删除社区帖子
   public function bbs_delete($post_id = 0){
        if (is_numeric($post_id) && ($post_id = (int) $post_id)) {
            $obj = D('Village_bbs');
            $detail = $obj->find($post_id);
            if (empty($detail)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
            }
            if ($detail['village_id'] != $this->village_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '您没有权限访问！'));
            }
            $obj->delete($post_id);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功', U('all/worker')));
        }
    }
	
	//审核帖子主题
	public function bbs_audit(){
        if (IS_AJAX) {
            $post_id = (int) $_POST['post_id'];
            $obj = D('Village_bbs');
            if (empty($post_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '帖子不存在'));
            }
            if (!($detail = $obj->find($post_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '帖子不存在'));
            }
            if ($detail['village_id'] != $this->village_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该帖不是您乡村的'));
            }
            if (false !== $obj->save(array('post_id' => $post_id, 'audit' => 1))) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '审核成功'));
            }
        }
    }
	
	//审核帖子回复内容
	public function bbs_replys_audit(){
        if (IS_AJAX) {
            $reply_id = (int) $_POST['reply_id'];
            $obj = D('Villagebbsreplys');
            if (empty($reply_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '帖子不存在'));
            }
            if (!($detail = $obj->find($reply_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '帖子不存在'));
            }
            if ($detail['village_id'] != $this->village_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该贴吧回复不是您乡村的'));
            }
            if (false !== $obj->save(array('reply_id' => $reply_id, 'audit' => 1))) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '审核成功'));
            }
        }
    }
  
   //工作人员
    public function worker(){
        $Village = D('Village_worker');
        import('ORG.Util.Page');
        $map = array('village_id'=>$this->village_id);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|job'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $Village->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
  
    public function worker_create(){
        if ($this->isPost()) {
            $data = $this->worker_createCheck();
			$data['village_id'] = $this->village_id;
            $obj = D('Village_worker');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuMsg('添加成功', U('all/worker'));
            }
            $this->tuMsg('操作失败');
        } else {
            $this->display();
        }
    }
	private function worker_createCheck(){
            $data = $this->checkFields($this->_post('data', false), $this->create_worker_fields);
			$data['photo'] = htmlspecialchars($data['photo']);
            if (empty($data['photo'])) {
                $this->tuMsg('请上传头像');
            }
            if (!isImage($data['photo'])) {
                $this->tuMsg('工作人员头像图格式不正确');
            }
			$data['user_id'] = (int)($data['user_id']);
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->tuMsg('姓名不能为空');
            }
            $data['job'] = htmlspecialchars($data['job']);
            if (empty($data['job'])) {
                $this->tuMsg('职务不能为空');
            }
        return $data;
    }
	
	
    public function worker_edit($id = 0){
        if ($id = (int) $id) {
            $obj = D('Village_worker');
            if (!($detail = $obj->find($id))) {
                $this->error('请选择要编辑的工作人员');
            }
            if ($this->isPost()) {
                $data = $this->worker_editCheck();
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuMsg('操作成功', U('all/worker'));
                }
                $this->tuMsg('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的工作人员');
        }
    }
	
	 private function worker_editCheck($iswork = 0){
            $data = $this->checkFields($this->_post('data', false), $this->edit_worker_fields);
			$data['photo'] = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
                $this->tuMsg('请上传头像');
            }
            if (!isImage($data['photo'])) {
                $this->tuMsg('工作人员头像图格式不正确');
            }
			$data['user_id'] = (int)($data['user_id']);
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->tuMsg('姓名不能为空');
            }
            $data['job'] = htmlspecialchars($data['job']);
            if (empty($data['job'])) {
                $this->tuMsg('职务不能为空');
            }
        return $data;
    }
	 
   //删除社区工作人员
   public function worker_delete($id = 0){
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Village_worker');
            $detail = $obj->find($id);
            if (empty($detail)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
            }
            if ($detail['village_id'] != $this->village_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '您没有权限访问！'));
            }
            $obj->delete($id);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功', U('all/worker')));
        }
    }
 
    public function reply($id = 0){
        if ($id = (int) $id) {
            $obj = D('Village_suggestion');
            if (!($detail = $obj->find($id))) {
                $this->error('请选择要回复的意见');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['id'] = $id;
                $data['replytime'] = NOW_TIME;
                $data['type'] = 1;
                if (false !== $obj->save($data)) {
                    $this->tuMsg('回复成功', U('all/suggestion'));
                }
                $this->tuMsg('回复成功');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要回复的意见');
        }
    }
    
    public function hots($business_id){
        if ($business_id = (int) $business_id) {
            $obj = D('Business');
            if (!($detail = $obj->find($business_id))) {
                $this->tuMsg('请选择商圈');
            }
            $detail['is_hot'] = $detail['is_hot'] == 0 ? 1 : 0;
            $obj->save(array('business_id' => $business_id, 'is_hot' => $detail['is_hot']));
            $obj->cleanCache();
            $this->tuMsg('操作成功', U('business/index'));
        } else {
            $this->error('请选择商圈');
        }
    }
  
  
    //乡村论坛回复删除AJAX
	public function bbs_replys_delete(){
        if (IS_AJAX) {
            $reply_id = (int) $_POST['reply_id'];
			$post_id = (int) $_POST['post_id'];
            $obj = D('Villagebbsreplys');
            if (empty($post_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '没有提交成功帖子编号'));
            }
            if (!($detail = $obj->find($reply_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '回复不存在'));
            }
            if ($detail['village_id'] != $this->village_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该帖不是您乡村的'));
            }
            if (false !== $obj->delete($village_id)) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功'));
            }
        }
    }
	
	//乡村论坛回复删除AJAX
	public function reply_delete(){
        if (IS_AJAX) {
            $id = (int) $_POST['id'];
            $obj = D('Village_suggestion');
            if (empty($post_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '没有提交成功帖子编号'));
            }
            if (!($detail = $obj->find($reply_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '回复不存在'));
            }
            if ($detail['village_id'] != $this->village_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该帖不是您乡村的'));
            }
            if (false !== $obj->delete($village_id)) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功'));
            }
        }
    }
	
   
    
}