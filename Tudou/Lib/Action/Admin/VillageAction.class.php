<?php
class VillageAction extends CommonAction{
    private $create_fields = array('cate','name', 'addr', 'tel', 'pic', 'user_id', 'city_id', 'area_id', 'lng', 'lat','profiles', 'thread_id','orderby', 'info', 'is_bbs');
    private $create_worker_fields = array('user_id','name', 'photo', 'village_id', 'job','orderby');
    private $edit_fields = array('cate','name', 'addr', 'tel', 'pic', 'user_id', 'city_id', 'area_id', 'lng', 'lat','thread_id', 'orderby', 'info', 'profiles','is_bbs');
    private $look = 0;
	
    protected function _initialize(){
		$getVillageCate = D('Village')->getVillageCate();
        $this->assign('getVillageCate', $getVillageCate);
    }
	
	  public function index() {
        $Village = D('Village');
        import('ORG.Util.Page');
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|addr'] = array('LIKE', '%' . $keyword . '%');
        }
        if ($this->look) {
            $map['user_id'] = $_SESSION['admin']['admin_id'];
        }
        $count = $Village->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$thread_ids = array();
        foreach ($list as $k => $val) {
			$thread_ids[$val['thread_id']] = $val['thread_id'];
            $list[$k] = $Village->_format($val);
            $list[$k]['username'] = D('Users')->where('user_id=' . $val['user_id'])->getField('nickname');
        }
        $this->assign('threads', D('Thread')->itemsByIds($thread_ids));
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Village');
			$cate = $this->_post('cate', false);
            $cate = implode(',', $cate);
            $data['cate'] = $cate;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('village/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('areas', D('Area')->fetchAll());
            $this->display();
        }
    }
	
	  private function createCheck($iswork = 0){
        if ($iswork) {
            $data = $this->checkFields($this->_post('data', false), $this->create_worker_fields);
			$data['user_id'] = (int) $data['user_id'];
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->tuError('姓名不能为空');
            }
            $data['job'] = htmlspecialchars($data['job']);
            if (empty($data['job'])) {
                $this->tuError('职务不能为空');
            }
        } else {
            $data = $this->checkFields($this->_post('data', false), $this->create_fields);
            $data['info'] = $data['info'];
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->tuError('社区村名称不能为空');
            }
            $data['addr'] = htmlspecialchars($data['addr']);
            if (empty($data['addr'])) {
                $this->tuError('地址不能为空');
            }
            $data['city_id'] = (int) $data['city_id'];
            $data['area_id'] = (int) $data['area_id'];
            if (empty($data['area_id'])) {
                $this->tuError('所在区域不能为空');
            }
            $data['user_id'] = (int) $data['user_id'];
            if(empty($data['user_id'])) {
                $this->tuError('管理员不能为空');
            }
			if (!D('Village')->check_user_id_occupy($data['user_id'])) {
				$this->tuError('当前管理员已被其他乡村占用');
			}
			$data['profiles'] = htmlspecialchars($data['profiles']);
			$data['thread_id'] = (int) $data['thread_id'];
            $data['orderby'] = (int) $data['orderby'];
            $data['lng'] = htmlspecialchars($data['lng']);
            $data['lat'] = htmlspecialchars($data['lat']);
            $data['is_bbs'] = (int) $data['is_bbs'];
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
        }
        return $data;
    }
	
	 public function edit($village_id = 0){
        if ($village_id = (int) $village_id) {
            $obj = D('Village');
            if (!($detail = $obj->find($village_id))) {
                $this->tuError('请选择要编辑的社区村');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['village_id'] = $village_id;
				if (!D('Village')->check_user_id_neq_village($data['user_id'],$data['village_id'])) {
					$this->tuError('当前管理员已被其他乡村占用');
				}
				$cate = $this->_post('cate', false);
                $cate = implode(',', $cate);
                $data['cate'] = $cate;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('Village/index'));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('users', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
				$cate = explode(',', $detail['cate']);
				$this->assign('cate', $cate);
                $this->assign('areas', D('Area')->fetchAll());
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的社区村');
        }
    }
	
	
	 private function editCheck($iswork = 0){
        if ($iswork) {
            $data = $this->checkFields($this->_post('data', false), $this->create_worker_fields);
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->tuError('姓名不能为空');
            }
            $data['job'] = htmlspecialchars($data['job']);
            if (empty($data['job'])) {
                $this->tuError('职务不能为空');
            }
        } else {
            $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
            $data['name'] = htmlspecialchars($data['name']);
            if (empty($data['name'])) {
                $this->tuError('名称不能为空');
            }
            $data['addr'] = htmlspecialchars($data['addr']);
            if (empty($data['addr'])) {
                $this->tuError('地址不能为空');
            }
            $data['city_id'] = (int) $data['city_id'];
            $data['area_id'] = (int) $data['area_id'];
            if (empty($data['area_id'])) {
                $this->tuError('所在区域不能为空');
            }
            $data['user_id'] = (int) $data['user_id'];
            if (empty($data['user_id'])) {
                $this->tuError('管理员不能为空');
            }
			$data['profiles'] = htmlspecialchars($data['profiles']);
			$data['thread_id'] = (int) $data['thread_id'];
            $data['orderby'] = (int) $data['orderby'];
            $data['is_bbs'] = (int) $data['is_bbs'];
            $data['lng'] = htmlspecialchars($data['lng']);
            $data['lat'] = htmlspecialchars($data['lat']);
        }
        return $data;
    }
	
    public function suggestion(){
        $Village = D('Village_suggestion');
        import('ORG.Util.Page');
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('village_id', $_GET['village_id']);
        $map['village_id'] = $_GET['village_id'];
        $count = $Village->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function bbs(){
        $Village = D('Village_bbs');
        import('ORG.Util.Page');
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('village_id', $_GET['village_id']);
        $map['village_id'] = $_GET['village_id'];
        $count = $Village->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Village->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function bbs_delete($post_id = 0){
        $village_id = $post_id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village_bbs');
            $obj->delete($village_id);
            D('Villagebbsreplys')->where('post_id=' . $village_id)->delete();
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('village/index'));
        } else {
            $village_id = $this->_post('post_id', false);
            if (is_array($village_id)) {
                $obj = D('Village_bbs');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                    D('Villagebbsreplys')->where('post_id=' . $id)->delete();
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('village/index'));
            }
            $this->tuError('请选择要删除的帖子');
        }
    }
    public function bbs_view($post_id = 0){
        if ($id = (int) $post_id) {
            $obj = D('Village_bbs');
            if (!($detail = $obj->find($id))) {
                $this->tuError('请选择要查看的帖子');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['post_id'] = $post_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('Village/bbs_view', array('post_id' => $data['post_id'])));
                }
                $this->tuError('操作失败');
            } else {
                import('ORG.Util.Page');
                $map = array('post_id' => $post_id);
                $replys = D('Villagebbsreplys');
                $count = $replys->where($map)->count();
                $Page = new Page($count, 5);
                $show = $Page->show();
                $list = $replys->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
                foreach ($list as $l => $k) {
                    $list[$l]['user_name'] = D('users')->where('user_id = ' . $k['user_id'])->getField('nickname');
                }
                $this->assign('list', $list);
                $this->assign('page', $show);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要查看的帖子');
        }
    }
    public function bbs_audit($post_id = 0){
        if (is_numeric($post_id) && ($post_id = (int) $post_id)) {
            $obj = D('Village_bbs');
            $detail = $obj->find($post_id);
            $obj->save(array('post_id' => $post_id, 'audit' => 1));
            $this->tuSuccess('审核社区帖子成功', U('village/bbs', array('village_id' => $detail['village_id'])));
        } else {
            $post_id = $this->_post('post_id', false);
            if (is_array($post_id)) {
                $obj = D('Village_bbs');
                $detail = $obj->find($post_id);
                foreach ($post_id as $id) {
                    $obj->save(array('post_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('批量审核成功', U('village/bbs', array('village_id' => $detail['village_id'])));
            }
            $this->tuError('请选择要审核社区的帖子');
        }
    }
    public function bbs_replys_audit($reply_id = 0){
        if (is_numeric($reply_id) && ($reply_id = (int) $reply_id)) {
            $obj = D('Villagebbsreplys');
            $obj->save(array('reply_id' => $reply_id, 'audit' => 1));
            $this->tuSuccess('审回复成功', U('village/bbs_view', array('post_id' => $reply_id)));
        } else {
            $reply_id = $this->_post('reply_id', false);
            if (is_array($reply_id)) {
                $obj = D('Villagebbsreplys');
                foreach ($reply_id as $id) {
                    $obj->save(array('reply_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('批量审核成功', U('village/bbs_view', array('post_id' => $reply_id)));
            }
            $this->tuError('请选择要审核社区回复');
        }
    }
    public function notice(){
        $obj = D('Village_notice');
        import('ORG.Util.Page');
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('village_id', $_GET['village_id']);
        if ($_GET['type']) {
            $map['type'] = $_GET['type'];
        }
        $map['village_id'] = $_GET['village_id'];
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
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
    public function worker(){
        $obj = D('Village_worker');
        import('ORG.Util.Page');
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|job'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('village_id', $_GET['village_id']);
        $map['village_id'] = $_GET['village_id'];
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
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
  
   
  
    public function worker_create(){
        if ($this->isPost()) {
            $data = $this->createCheck(1);
            $obj = D('Village_worker');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('village/worker', array('village_id' => $data['village_id'])));
            }
            $this->tuError('操作失败');
        } else {
            if ($_GET['village_id']) {
                $this->assign('village_id', $_GET['village_id']);
                $this->display();
            }
        }
    }
   
    public function suggestion_edit($id = 0){
        if ($id = (int) $id) {
            $obj = D('Village_suggestion');
            if (!($detail = $obj->find($id))) {
                $this->tuError('请选择要编辑的意见');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('Village/suggestion', array('village_id' => $data['village_id'])));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的意见');
        }
    }
    public function worker_edit($id = 0){
        if ($id = (int) $id) {
            $obj = D('Village_worker');
            if (!($detail = $obj->find($id))) {
                $this->tuError('请选择要编辑的工作人员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck(1);
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('Village/worker', array('village_id' => $data['village_id'])));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的工作人员');
        }
    }
    public function reply($id = 0){
        if ($id = (int) $id) {
            $obj = D('Village_suggestion');
            if (!($detail = $obj->find($id))) {
                $this->tuError('请选择要回复的意见');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['id'] = $id;
                $data['replytime'] = NOW_TIME;
                $data['type'] = 1;
                $data['user'] = $_SESSION['admin']['username'];
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('回复成功', U('Village/suggestion', array('village_id' => $data['village_id'])));
                }
                $this->tuError('回复成功');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要回复的意见');
        }
    }
	
	 public function notice_create($village_id = 0){
		$village_id = (int) $village_id;
		if(!$detail = D('Village')->find()){
			$this->tuError('内容不存在');
		}
        if($this->isPost()) {
            $data = $this->_post('data', false);
            $data['title'] = htmlspecialchars($data['title']);
            if (empty($data['title'])) {
                $this->tuError('标题不能为空');
            }
            if (empty($data['context'])) {
                $this->tuError('内容不能为空');
            }
			$data['village_id'] = (int) $data['village_id'];
			$data['user_id'] = (int) $detail['user_id'];
            $data['addtime'] = NOW_TIME;
            $obj = D('Village_notice');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('village/notice', array('village_id' => $data['village_id'])));
            }
            $this->tuError('操作失败');
        } else {
            if ($_GET['village_id']) {
                $this->assign('village_id', $_GET['village_id']);
                $this->assign('type', $_GET['type']);
                $this->display();
            }
        }
    }
	
	
    public function notice_edit($id = 0){
        if ($id = (int) $id) {
            $obj = D('Village_notice');
            if (!($detail = $obj->find($id))) {
                $this->tuError('请选择要编辑的通知或是展示');
            }
            if ($this->isPost()) {
                $data = $this->_post('data', false);
                $data['title'] = htmlspecialchars($data['title']);
                if (empty($data['title'])) {
                    $this->tuError('标题不能为空');
                }
                if (empty($data['context'])) {
                    $this->tuError('内容不能为空');
                }
				$data['user_id'] = (int) $data['user_id'];
                $data['addtime'] = NOW_TIME;
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('Village/notice', array('village_id' => $data['village_id'])));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的通知或是展示');
        }
    }
    public function hots($business_id){
        if ($business_id = (int) $business_id) {
            $obj = D('Business');
            if (!($detail = $obj->find($business_id))) {
                $this->tuError('请选择商圈');
            }
            $detail['is_hot'] = $detail['is_hot'] == 0 ? 1 : 0;
            $obj->save(array('business_id' => $business_id, 'is_hot' => $detail['is_hot']));
            $obj->cleanCache();
            $this->tuSuccess('操作成功', U('business/index'));
        } else {
            $this->tuError('请选择商圈');
        }
    }
   
    public function delete($village_id = 0){
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village');
            $obj->delete($village_id);
            D('Village_worker')->where('village_id = ' . $village_id)->delete();
            D('Village_notice')->where('village_id = ' . $village_id)->delete();
            D('Village_suggestion')->where('village_id = ' . $village_id)->delete();
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('village/index'));
        } else {
            $village_id = $this->_post('village_id', false);
            if (is_array($village_id)) {
                $obj = D('Village');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                D('Village_worker')->where('village_id = ' . $id)->delete();
                D('Village_notice')->where('village_id = ' . $id)->delete();
                D('Village_suggestion')->where('village_id = ' . $id)->delete();
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('village/index'));
            }
            $this->tuError('请选择要删除的社区村');
        }
    }
    public function worker_delete($id = 0){
        $village_id = $id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village_worker');
            $obj->delete($village_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('village/index'));
        } else {
            $village_id = $this->_post('id', false);
            if (is_array($village_id)) {
                $obj = D('Village_worker');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('village/index'));
            }
            $this->tuError('请选择要删除的工作人员');
        }
    }
    public function bbs_replys_delete($reply_id = 0, $post_id){
        $village_id = $reply_id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Villagebbsreplys');
            $obj->delete($village_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('village/bbs_view', array('post_id' => $post_id)));
        } else {
            $village_id = $this->_post('reply_id', false);
            if (is_array($village_id)) {
                $obj = D('Villagebbsreplys');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('village/index'));
            }
            $this->tuError('请选择要删除的评论');
        }
    }
    public function notice_delete($id = 0){
        $village_id = $id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village_notice');
            $obj->delete($village_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('village/index'));
        } else {
            $village_id = $this->_post('id', false);
            if (is_array($village_id)) {
                $obj = D('Village_notice');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('village/index'));
            }
            $this->tuError('请选择要删除的工作人员');
        }
    }
    public function reply_delete($id = 0){
        $village_id = $id;
        if (is_numeric($village_id) && ($village_id = (int) $village_id)) {
            $obj = D('Village_suggestion');
            $obj->delete($village_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('village/index'));
        } else {
            $village_id = $this->_post('id', false);
            if (is_array($village_id)) {
                $obj = D('Village_suggestion');
                foreach ($village_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('village/index'));
            }
            $this->tuError('请选择要删除的工作人员');
        }
    }
    public function child($area_id = 0){
        $datas = D('Village')->fetchAll();
        $str = '<option value="0">请选择</option>';
        foreach ($datas as $val) {
            if ($val['area_id'] == $area_id) {
                $str .= '<option value="' . $val['village_id'] . '">' . $val['name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
    // 新增选择村镇社区代理
    public function select(){
        $User = D('Village');
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
        if ($account = $this->_param('name', 'htmlspecialchars')) {
            $map['name'] = array('LIKE', '%' . $account . '%');
            $this->assign('name', $name);
        }
        if ($nickname = $this->_param('addr', 'htmlspecialchars')) {
            $map['addr'] = array('LIKE', '%' . $addr . '%');
            $this->assign('addr', $addr);
        }
        $count = $User->where($map)->count();
        $Page = new Page($count, 8);
        $pager = $Page->show();
        $list = $User->where($map)->order(array('village_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        $this->display();
    }
	//入驻
	 public function enter($village_id = 0){
		$village_id = (int) $village_id;
        $obj = D('VillageEnter');
        import('ORG.Util.Page');
        $map = array('village_id'=>$village_id,'closed'=>0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['id'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$val['goods_name'] = $obj->get_goods_name($val['enter_id']);
			$val['village_name'] = $obj->get_village_name($val['enter_id']);
			$list[$k] = $val;
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('village_id', $village_id);
        $this->display();
    }
	//入驻信息删除
	public function enter_delete($enter_id = 0,$village_id = 0){
        $enter_id = $enter_id;
		$village_id = (int) $village_id;
        if (is_numeric($enter_id) && ($enter_id = (int) $enter_id)) {
            $obj = D('VillageEnter');
            $obj->save(array('enter_id' => $enter_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('village/enter',array('village_id'=>$village_id)));
        } else {
            $enter_id = $this->_post('id', false);
            if (is_array($enter_id)) {
                $obj = D('VillageEnter');
                foreach ($enter_id as $id) {
                    $obj->save(array('enter_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('批量删除成功', U('village/enter',array('village_id'=>$village_id)));
            }
            $this->tuError('请选择要删除的入驻信息');
        }
    }
	//入驻信息审核
	public function enter_audit($enter_id = 0,$village_id = 0){
        $enter_id = $enter_id;
		$village_id = (int) $village_id;
        if (is_numeric($enter_id) && ($enter_id = (int) $enter_id)) {
            $obj = D('VillageEnter');
            $obj->save(array('enter_id' => $enter_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('village/enter',array('village_id'=>$village_id)));
        } else {
            $enter_id = $this->_post('id', false);
            if (is_array($enter_id)) {
                $obj = D('VillageEnter');
                foreach ($enter_id as $id) {
                    $obj->save(array('enter_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('批量审核成功', U('village/enter',array('village_id'=>$village_id)));
            }
            $this->tuError('请选择要审核的入驻信息');
        }
    }
  
}