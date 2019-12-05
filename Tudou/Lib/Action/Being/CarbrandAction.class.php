<?php
class CarbrandAction extends CommonAction{
    private $create_fields = array('name', 'photo', 'orderby');
    private $edit_fields = array('name', 'photo', 'orderby');
	
    public function index(){
        $obj = D('CarBrand');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('brand_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('CarBrand');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('carbrand/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('cates', D('Activitycate')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if(empty($data['name'])){
            $this->tuError('品牌名称不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if(!isImage($data['photo'])){
            $this->tuError('请上传正确的图片');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($brand_id = 0){
        if ($brand_id = (int) $brand_id) {
            $obj = D('CarBrand');
            if(!($detail = $obj->find($brand_id))) {
                $this->tuError('请选择要编辑的品牌');
            }
            if($this->isPost()){
                $data = $this->editCheck();
                $data['brand_id'] = $brand_id;
                if(false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('carbrand/index'));
                }
                $this->tuError('操作失败');
            }else{
                $this->assign('detail', $detail);
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的品牌');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if(empty($data['name'])){
            $this->tuError('品牌名称不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if(!isImage($data['photo'])){
            $this->tuError('请上传正确的图片');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($brand_id = 0){
        if (is_numeric($brand_id) && ($brand_id = (int) $brand_id)) {
            $obj = D('CarBrand');
            $obj->delete($brand_id);
            $this->tuSuccess('删除成功', U('carbrand/index'));
        } else {
            $brand_id = $this->_post('brand_id', false);
            if (is_array($brand_id)) {
                $obj = D('Activity');
                foreach ($brand_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('carbrand/index'));
            }
            $this->tuError('请选择要删除的品牌');
        }
    }
   
}