<?php
class NavigationAction extends CommonAction{
    private $create_fields = array('nav_name', 'ioc', 'url', 'title', 'photo', 'status', 'closed', 'colour', 'target', 'is_new', 'orderby');
    private $edit_fields = array('nav_name', 'ioc', 'url', 'title', 'photo', 'status', 'closed', 'colour', 'target', 'is_new', 'orderby');
    public function main(){
        $this->display();
    }
    public function index(){
        $Navigation = D('Navigation');
        $map = array();
        $aready = (int) $this->_param('aready');
        if ($aready == 0) {
            $map['status'] = 4;
        } elseif ($aready == 2) {
            $map['status'] = 2;
        } elseif ($aready == 3) {
            $map['status'] = 3;
        } elseif ($aready == 4) {
            $map['status'] = 4;
        } elseif ($aready == 5) {
            $map['status'] = 5;
        } elseif ($aready == 6) {
            $map['status'] = 6;
        } elseif ($aready == 1) {
            $map['status'] = 1;
        } else {
            $map['status'] = 4;
        }
        $list = $Navigation->where($map)->order(array('orderby' => 'asc'))->select();
        $this->assign('list', $list);
        $this->assign('aready', $aready);
        $this->assign('page', $show);
        $this->display();
    }
    public function create($parent_id = 0){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Navigation');
            $data['parent_id'] = $parent_id;
			$data['status'] = (int) $data['status'];
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('操作成功', U('navigation/index',array('aready'=>$data['status'])));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
    public function child($parent_id = 0){
        $datas = D('Navigation')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['nav_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['nav_id']) {
                        $str .= '<option value="' . $var2['nav_id'] . '">' . $var2['nav_name'] . '</option>' . "\n\r";
                        foreach ($datas as $var3) {
                            if ($var3['parent_id'] == $var2['nav_id']) {
                                $str .= '<option value="' . $var3['nav_id'] . '">  --' . $var3['nav_name'] . '</option>' . "\n\r";
                            }
                        }
                    }
                }
            }
        }
        echo $str;
        die;
    }
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['nav_name'] = htmlspecialchars($data['nav_name']);
        if (empty($data['nav_name'])) {
            $this->tuError('导航名字不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('别名不能为空');
        }
        $data['ioc'] = htmlspecialchars($data['ioc']);
        $data['url'] = htmlspecialchars($data['url']);
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->tuError('ioc图标不正确');
        }
        $data['status'] = (int) $data['status'];
        if (empty($data['status'])) {
            $this->tuError('类型不能为空');
        }
        $data['closed'] = (int) $data['closed'];
        $data['colour'] = htmlspecialchars($data['colour']);
        $data['target'] = (int) $data['target'];
		$data['is_new'] = (int) $data['is_new'];
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($nav_id = 0,$status = 0){
		$status = (int) $this->_param('status', false);
        if ($nav_id = (int) $nav_id) {
            $obj = D('Navigation');
            if (!($detail = $obj->find($nav_id))) {
                $this->tuError('请选择要编辑的手机底部导航');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['nav_id'] = $nav_id;
				$data['status'] = (int) $data['status'];
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('navigation/index',array('aready'=>$data['status'])));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('status', $status);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的商家分类');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['nav_name'] = htmlspecialchars($data['nav_name']);
        if (empty($data['nav_name'])) {
            $this->tuError('导航名字不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('别名不能为空');
        }
        $data['status'] = (int) $data['status'];
        if (empty($data['status'])) {
            $this->tuError('类型不能为空');
        }
        $data['ioc'] = htmlspecialchars($data['ioc']);
        $data['url'] = htmlspecialchars($data['url']);
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->tuError('ioc图标不正确');
        }
        $data['closed'] = (int) $data['closed'];
        $data['colour'] = htmlspecialchars($data['colour']);
        $data['target'] = (int) $data['target'];
		$data['is_new'] = (int) $data['is_new'];
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
	
    public function delete($nav_id = 0,$aready = 0){
		$aready = (int) $aready;
        if (is_numeric($nav_id) && ($nav_id = (int) $nav_id)) {
            $obj = D('Navigation');
            $navigation = $obj->fetchAll();
            foreach ($navigation as $val) {
                if ($val['parent_id'] == $nav_id) {
                    $this->tuError('该菜单下还有其他子菜单');
                }
            }
            $obj->delete($nav_id);
            $obj->cleanCache();
			$this->tuSuccess('删除成功', U('navigation/index',array('aready'=>$aready)));
        } else {
            $cate_id = $this->_post('nav_id', false);
            if (is_array($nav_id)) {
                $obj = D('Navigation');
                foreach ($nav_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('navigation/index',array('aready'=>$aready)));
            }
            $this->tuError('请选择要删除的商家分类');
        }
    }
    public function update() {
        $orderby = $this->_post('orderby', false);
        $obj = D('Navigation');
        foreach ($orderby as $key => $val) {
            $data = array('nav_id' => (int) $key, 'orderby' => (int) $val);
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->tuSuccess('更新成功', U('navigation/index'));
    }
	
	 public function reset($nav_id = 0,$status = 0) {
        $nav_id = (int) $nav_id;
		$status = (int) $status;
		if(!empty($nav_id)){
			D('Navigation')->save(array('nav_id' => $nav_id, 'click' => 0));
        	$this->tuSuccess('更新点击量成功', U('navigation/index',array('aready'=>$status)));
		}else{
			$this->tuError('请选择要重置的导航点击量');
		}
        
    }
}