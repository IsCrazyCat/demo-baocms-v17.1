<?php
class ShopcateAction extends CommonAction{
    private $create_fields = array('cate_name', 'd1', 'd2', 'd3', 'title', 'orderby');
    private $edit_fields = array('cate_name', 'd1', 'd2', 'd3', 'title', 'orderby');
    public function index(){
        $Shopcate = D('Shopcate');
        $list = $Shopcate->fetchAll();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create($parent_id = 0){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Shopcate');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('shopcate/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('parent_id', $parent_id);
            $this->display();
        }
    }
    public function hots($cate_id) {
        if ($cate_id = (int) $cate_id) {
            $obj = D('Shopcate');
            if (!($detail = $obj->find($cate_id))) {
                $this->tuError('请选择要编辑的商家分类');
            }
            $detail['is_hot'] = $detail['is_hot'] == 0 ? 1 : 0;
            $obj->save(array('cate_id' => $cate_id, 'is_hot' => $detail['is_hot']));
            $obj->cleanCache();
            $this->tuSuccess('操作成功', U('shopcate/index'));
        } else {
            $this->tuError('请选择要编辑的商家分类');
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('分类不能为空');
        }
        $data['d1'] = htmlspecialchars($data['d1']);
        $data['d2'] = htmlspecialchars($data['d2']);
        $data['d3'] = htmlspecialchars($data['d3']);
        $data['title'] = htmlspecialchars($data['title']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($cate_id = 0){
        if ($cate_id = (int) $cate_id) {
            $obj = D('Shopcate');
            if (!($detail = $obj->find($cate_id))) {
                $this->tuError('请选择要编辑的商家分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('shopcate/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的商家分类');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('分类不能为空');
        }
        $data['d1'] = htmlspecialchars($data['d1']);
        $data['d2'] = htmlspecialchars($data['d2']);
        $data['d3'] = htmlspecialchars($data['d3']);
        $data['title'] = htmlspecialchars($data['title']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($cate_id = 0) {
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Shopcate');
			if(false == $obj->check_parent_id($cate_id)){
				$this->tuError('当前分类下面还有二级分类');
			}
			if(false == $obj->check_cate_id_shop($cate_id)){
				$this->tuError('当前分类下面还有商家');
			}
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('shopcate/index'));
        } else {
            $cate_id = $this->_post('cate_id', false);
            if (is_array($cate_id)) {
                $obj = D('Shopcate');
                foreach ($cate_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('shopcate/index'));
            }
            $this->tuError('请选择要删除的商家分类');
        }
    }
    public function update(){
        $orderby = $this->_post('orderby', false);
        $obj = D('Shopcate');
        foreach ($orderby as $key => $val) {
            $data = array('cate_id' => (int) $key, 'orderby' => (int) $val);
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->tuSuccess('更新成功', U('shopcate/index'));
    }
	//配置分类
	public function setting($cate_id)  {
        if(!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if(!($detail = D('Shopcate')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        if ($this->isPost()) {
            $obj = D('Shopcateattr');
            $data = $this->_post('data', false);
            foreach ($data as $key => $val) {
                foreach ($val as $k => $v) {
                    if (!empty($v['attr_name'])) {
                        $obj->add(array(
							'cate_id' => $cate_id, 
							'type' => htmlspecialchars($key), 
							'attr_name' => htmlspecialchars($v['attr_name']), 
							'attr_price' => (int) ($v['attr_price']*100),
							'attr_intro' => htmlspecialchars($v['attr_intro']), 
							'orderby' => (int) $v['orderby']
						));
                    }
                }
            }
            $old = $this->_post('old', false);
            foreach ($old as $key => $val) {
                $obj->save(array(
					'attr_id' => (int) $key, 
					'attr_name' => htmlspecialchars($val['attr_name']), 
					'attr_price' => (int) ($val['attr_price']*100),
					'attr_intro' => htmlspecialchars($val['attr_intro']), 
					'orderby' => (int) $val['orderby']
				));
            }
            $this->tuSuccess('操作成功', U('shopcate/setting', array('cate_id' => $cate_id)));
        } else {
            $this->assign('detail', $detail);
            $this->assign('attrs', D('Shopcateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
            $this->display();
        }
    }
	//删除分类
	public function delattr($attr_id){
		$obj = D('Shopcateattr');
        if(empty($attr_id)){
            $this->tuError('操作失败');
        }
        if(!$detail = $obj->find($attr_id)){
            $this->tuError('操作失败');
        }
		if($obj->delete($attr_id)){
			$this->tuSuccess('删除成功',U('shopcate/setting',array('cate_id'=>$detail['cate_id'])));
		}else{
			$this->tuError('删除失败');
		}
    }
}