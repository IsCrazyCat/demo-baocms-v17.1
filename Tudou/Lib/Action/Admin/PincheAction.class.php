<?php 
class PincheAction extends CommonAction{
	

	
   protected function _initialize() {
        parent::_initialize();
        $getPincheCate = D('Pinche')->getPincheCate();
        $this->assign('getPincheCate', $getPincheCate);
    }
	
    public function index(){
        $obj = D('Pinche');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['toplace|middleplace'] = array('LIKE', '%' . $keyword . '%');
        }
        $area_id = (int) $this->_param('area_id');
        if ($area_id) {
            $map['area_id'] = $area_id;
        }
        $cate_id = (int) $this->_param('cate_id');
        if ($cate_id) {
            $map['cate_id'] = $cate_id;
        }
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
   
	
	
	//删除
	public function delete($pinche_id = 0){
        if(is_numeric($pinche_id) && ($pinche_id = (int) $pinche_id)){
            $obj = D('Pinche');
            $obj->save(array('pinche_id' => $pinche_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('pinche/index'));
        }else{
            $pinche_id = $this->_post('pinche_id', false);
            if(is_array($pinche_id)){
                $obj = D('Pinche');
                foreach ($pinche_id as $id){
                    $obj->save(array('pinche_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('pinche/index'));
            }
            $this->tuError('请选择要删除的拼车');
        }
    }
}