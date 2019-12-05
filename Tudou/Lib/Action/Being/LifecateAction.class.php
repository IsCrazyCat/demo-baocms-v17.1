<?php
class LifecateAction extends CommonAction{
	
    private $create_fields = array('channel_id','cate_name','price','text1','text2','text3','text4','text5','num1','num2','unit1','unit2','select1','select2','select3','select4','select5','orderby');
    private $edit_fields = array('channel_id','cate_name','price','text1','text2','text3','text4','text5','num1','num2','unit1','unit2','select1','select2','select3','select4','select5','orderby');
    public function index() {
        $Lifecate = D('Lifecate');
        import('ORG.Util.Page');
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $map = array();
        if ($keyword) {
            $map['cate_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);
        $count = $Lifecate->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = (int) $_GET[$var];
        $this->assign('p', $p);
        $list = $Lifecate->where($map)->order(array('cate_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
        $this->display();
    }
	
	
    public function hots($cate_id){
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = (int) $_GET[$var];
        if ($cate_id = (int) $cate_id) {
            $obj = D('Lifecate');
            if (!($detail = $obj->find($cate_id))) {
                $this->tuError('请选择分类');
            }
            $detail['is_hot'] = $detail['is_hot'] == 0 ? 1 : 0;
            $obj->save(array('cate_id' => $cate_id, 'is_hot' => $detail['is_hot']));
            $obj->cleanCache();
            $this->tuSuccess('操作成功', U('lifecate/index', array('p' => $p)));
        } else {
            $this->tuError('请选择分类');
        }
    }
	
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Lifecate');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('lifecate/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['channel_id'] = (int) $data['channel_id'];
        if (empty($data['channel_id'])) {
            $this->tuError('所属频道不能为空');
        }
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->tuError('分类名称不能为空');
        }
		$data['price'] = (int) ($data['price'] * 100);
        $data['text1'] = htmlspecialchars($data['text1']);
        $data['text2'] = htmlspecialchars($data['text2']);
        $data['text3'] = htmlspecialchars($data['text3']);
		$data['text4'] = htmlspecialchars($data['text4']);
		$data['text5'] = htmlspecialchars($data['text5']);
        $data['num1'] = htmlspecialchars($data['num1']);
        $data['num2'] = htmlspecialchars($data['num2']);
        $data['unit1'] = htmlspecialchars($data['unit1']);
        $data['unit2'] = htmlspecialchars($data['unit2']);
        $data['select1'] = htmlspecialchars($data['select1']);
        $data['select2'] = htmlspecialchars($data['select2']);
        $data['select3'] = htmlspecialchars($data['select3']);
        $data['select4'] = htmlspecialchars($data['select4']);
        $data['select5'] = htmlspecialchars($data['select5']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
	
	
    public function edit($cate_id = 0){
        if($cate_id = (int) $cate_id){
            $obj = D('Lifecate');
            if (!($detail = $obj->find($cate_id))){
                $this->tuError('请选择要编辑的分类管理');
            }
            if($this->isPost()){
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)){
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('lifecate/index'));
                }
                $this->tuError('操作失败');
            }else{
                $this->assign('detail', $detail);
                $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的分类管理');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['channel_id'] = (int) $data['channel_id'];
        if(empty($data['channel_id'])){
            $this->tuError('所属频道不能为空');
        }
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if(empty($data['cate_name'])) {
            $this->tuError('分类名称不能为空');
        }
		$data['price'] = (int) ($data['price'] * 100);
        $data['text1'] = htmlspecialchars($data['text1']);
        $data['text2'] = htmlspecialchars($data['text2']);
        $data['text3'] = htmlspecialchars($data['text3']);
		$data['text4'] = htmlspecialchars($data['text4']);
		$data['text5'] = htmlspecialchars($data['text5']);
        $data['num1'] = htmlspecialchars($data['num1']);
        $data['num2'] = htmlspecialchars($data['num2']);
        $data['unit1'] = htmlspecialchars($data['unit1']);
        $data['unit2'] = htmlspecialchars($data['unit2']);
        $data['select1'] = htmlspecialchars($data['select1']);
        $data['select2'] = htmlspecialchars($data['select2']);
        $data['select3'] = htmlspecialchars($data['select3']);
        $data['select4'] = htmlspecialchars($data['select4']);
        $data['select5'] = htmlspecialchars($data['select5']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
	
    public function delete($cate_id = 0){
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Lifecate');
			if($res = D('Life')->where(array('cate_id'=>$cate_id,'closed'=>'0'))->find()){
				$this->tuError('信息ID【'.$res['life_id'].'】正在使用当前分类暂时无法删除');
			}
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('lifecate/index'));
        } else {
            $cate_id = $this->_post('cate_id', false);
            if (is_array($cate_id)) {
                $obj = D('Lifecate');
                foreach ($cate_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('lifecate/index'));
            }
            $this->tuError('请选择要删除的分类管理');
        }
    }
	
	
    public function delattr($attr_id){
        if (empty($attr_id)) {
            $this->tuError('操作失败');
        }
        if (!($detail = D('Lifecateattr')->find($attr_id))) {
            $this->tuError('操作失败');
        }
        D('Lifecateattr')->delete($attr_id);
        $this->tuSuccess('删除成功', U('lifecate/setting', array('cate_id' => $detail['cate_id'])));
    }
	
	
	
    public function ajax($cate_id, $life_id = 0){
        if (!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if (!($detail = D('Lifecate')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        $this->assign('cate', $detail);
        $this->assign('attrs', D('Lifecateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
		$this->assign('tags', D('LifeCateTag')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
        if($life_id){
            $this->assign('detail', D('Life')->find($life_id));
            $this->assign('maps', D('LifeCateattr')->getAttrs($life_id));
			$this->assign('tag', D('LifeCateTag')->getTags($life_id));
        }
        $this->display();
    }
	
	
	
	public function setting($cate_id){
        if(!($cate_id = (int) $cate_id)){
            $this->error('请选择正确的分类');
        }
        if(!($detail = D('Lifecate')->find($cate_id))){
            $this->error('请选择正确的分类');
        }
        if($this->isPost()){
            $obj = D('Lifecateattr');
            $data = $this->_post('data', false);
            foreach($data as $key => $val){
                foreach($val as $k => $v){
                    if(!empty($v['attr_name'])){
                        $obj->add(array('cate_id' => $cate_id, 'type' => htmlspecialchars($key), 'attr_name' => htmlspecialchars($v['attr_name']), 'orderby' => (int) $v['orderby']));
                    }
                }
            }
            $old = $this->_post('old', false);
            foreach($old as $key => $val){
                $obj->save(array('attr_id' => (int) $key, 'attr_name' => htmlspecialchars($val['attr_name']), 'orderby' => (int) $val['orderby']));
            }
            $this->tuSuccess('操作成功', U('lifecate/setting', array('cate_id' => $cate_id)));
        }else{
            $this->assign('detail', $detail);
            $this->assign('attrs', D('Lifecateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
            $this->display();
        }
    }
	
	
	public function tag_delete($tag_id){
        if (empty($tag_id)) {
            $this->tuError('操作失败');
        }
        if(!($detail = D('LifeCateTag')->find($tag_id))) {
            $this->tuError('操作失败');
        }
        D('LifeCateTag')->delete($tag_id);
        $this->tuSuccess('删除成功', U('lifecate/tag', array('cate_id' => $detail['cate_id'])));
    }
	
	
    public function tag($cate_id){
        if(!($cate_id = (int) $cate_id)){
            $this->error('请选择正确的分类');
        }
        if(!($detail = D('Lifecate')->find($cate_id))){
            $this->error('请选择正确的分类');
        }
        if($this->isPost()){
            $obj = D('LifeCateTag');
            $data = $this->_post('data', false);
            foreach ($data as $key => $val){
                foreach ($val as $k => $v){
                    if (!empty($v['tag_name'])){
                        $obj->add(array('cate_id' => $cate_id, 'type' => htmlspecialchars($key), 'tag_name' => htmlspecialchars($v['tag_name']), 'orderby' => (int) $v['orderby']));
                    }
                }
            }
            $old = $this->_post('old', false);
            foreach($old as $key => $val){
                $obj->save(array('tag_id' => (int) $key, 'tag_name' => htmlspecialchars($val['tag_name']), 'orderby' => (int) $val['orderby']));
            }
            $this->tuSuccess('操作成功', U('lifecate/tag', array('cate_id' => $cate_id)));
        }else{
            $this->assign('detail', $detail);
            $this->assign('tags', D('LifeCateTag')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
            $this->display();
        }
    }
}