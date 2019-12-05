<?php

class NavAction extends CommonAction{

    private $create_fields = array('cate_name','photo','type','url','orderby');
    private $edit_fields = array('cate_name','photo','type','url','orderby');
	
	
	public function _initialize(){
        parent::_initialize();
        $this->assign('types',D('NavCate')->getType());//订单状态
    }

    public function index(){
        $obj = D('NavCate');
        $list = $obj->fetchAll();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();

    }

    public function create($parent_id = 0){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('NavCate');
            $data['parent_id'] = $parent_id;
			
			if($parent_id == 0){
				if($data['type'] <= 0) {
					$this->tuError('必须选择类型');
				}
			}
			
			
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('nav/index'));
            }
            $this->tuError('操作失败！');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }

    }

   

    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if(empty($data['cate_name'])) {
            $this->tuError('分类不能为空');
        }
		$data['type'] = (int) $data['type'];
		$data['photo'] = htmlspecialchars($data['photo']);
		$data['url'] = htmlspecialchars($data['url']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;

    }

    public function edit($cate_id = 0){
        if ($cate_id = (int) $cate_id) {
            $obj = D('NavCate');
            if (!($detail = $obj->find($cate_id))) {
                $this->tuError('请选择要编辑的分类');
            }
            if($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
				
				if($detail['parent_id'] == 0){
					if($data['type'] <= 0) {
						$this->tuError('必须选择类型');
					}
				}
			
			
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('nav/index'));
                }
                $this->tuError('操作失败');
            }else{
                $this->assign('detail', $detail);
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的分类');
        }

    }

    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('分类不能为空');
        }
		$data['type'] = (int) $data['type'];
		$data['photo'] = htmlspecialchars($data['photo']);
		$data['url'] = htmlspecialchars($data['url']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;

    }

  
    public function delete($cate_id = 0){
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('NavCate');
			if(false == $obj->check_parent_id($cate_id)){
				$this->tuError($obj->getError());
			}
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功！', U('nav/index'));
        }else{
            $cate_id = $this->_post('cate_id', false);
            if (is_array($cate_id)) {
                $obj = D('NavCate');
                foreach ($cate_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功！', U('nav/index'));
            }
            $this->tuError('请选择要删除的分类');
        }
    }

  

    public function update(){
        $orderby = $this->_post('orderby', false);
        $obj = D('NavCate');
        foreach ($orderby as $key => $val) {
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->tuSuccess('更新成功', U('nav/index'));

    }

}