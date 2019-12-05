<?php
class HotelbrandAction extends CommonAction {
    public function index() {
        $list = D('Hotelbrand')->fetchAll();
        $this->assign('list', $list); 
        $this->display(); 
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Hotelbrand');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('hotelbrand/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'orderby'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('品牌名称不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function edit($type = 0) {
        if ($type = (int) $type) {
            $obj = D('Hotelbrand');
            if (!$detail = $obj->find($type)) {
                $this->tuError('请选择要编辑的酒店品牌');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['type'] = $type;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('hotelbrand/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的酒店品牌');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'orderby'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('酒店品牌不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function delete($type = 0) {
        if (is_numeric($type) && ($type = (int) $type)) {
            $obj = D('Hotelbrand');
            $obj->delete($type);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('hotelbrand/index'));
           
        } 
    }
    
    public function update() {
        $orderby = $this->_post('orderby', false);
        $obj = D('Hotelbrand');
        foreach ($orderby as $key => $val) {
            $data = array(
                'type' => (int) $key,
                'orderby' => (int) $val
            );
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->tuSuccess('更新成功', U('hotelbrand/index'));
    }


}
