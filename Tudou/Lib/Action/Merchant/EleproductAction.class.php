<?php
class EleproductAction extends CommonAction{
    private $create_fields = array('product_name', 'desc', 'cate_id', 'photo', 'cost_price', 'price','tableware_price', 'is_new', 'is_hot', 'is_tuijian', 'create_time', 'create_ip');
    private $edit_fields = array('product_name', 'desc', 'cate_id', 'photo', 'cost_price', 'price', 'tableware_price','is_new', 'is_hot', 'is_tuijian');
    public function _initialize(){
        parent::_initialize();
        $getEleCate = D('Ele')->getEleCate();
        $this->assign('getEleCate', $getEleCate);
        $this->ele = D('Ele')->find($this->shop_id);
        if (!empty($this->ele) && $this->ele['audit'] == 0){
            $this->error("亲，您的申请正在审核中！");
        }
        if (empty($this->ele) && ACTION_NAME != 'apply'){
            $this->error('您还没有入住外卖频道', U('ele/apply'));
        }
        $this->assign('ele', $this->ele);
        $this->elecates = D('Elecate')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->select();
        $this->assign('elecates', $this->elecates);
    }
	
	
    public function index(){
        $Eleproduct = D('Eleproduct');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['product_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = $this->shop_id) {
            $map['shop_id'] = $shop_id;
            $this->assign('shop_id', $shop_id);
        }
        $count = $Eleproduct->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Eleproduct->where($map)->order(array('product_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $cate_ids = array();
        foreach ($list as $k => $val) {
            if ($val['cate_id']) {
                $cate_ids[$val['cate_id']] = $val['cate_id'];
            }
        }
        if ($cate_ids) {
            $this->assign('cates', D('Elecate')->itemsByIds($cate_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Eleproduct');
            if ($obj->add($data)) {
                D('Elecate')->updateNum($data['cate_id']);
                $this->tuSuccess('添加成功', U('eleproduct/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
	
	
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['product_name'] = htmlspecialchars($data['product_name']);
        if (empty($data['product_name'])) {
            $this->tuError('菜名不能为空');
        }
        $data['desc'] = htmlspecialchars($data['desc']);
        if (empty($data['desc'])) {
            $this->tuError('菜单介绍不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('分类不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        }
		$data['cost_price'] = (int) ($data['cost_price'] * 100);
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->tuError('价格不能为空');
        }
		if($data['cost_price']){
			if($data['price'] >= $data['cost_price']){
				$this->tuError('售价不能高于原价');
			}
		}
		
		//添加
		$data['tableware_price'] = (int) ($data['tableware_price'] * 100);
        $data['settlement_price'] = (int) ($data['price'] - $data['price'] * $this->ele['rate'] / 1000);
		if(false == D('Eleproduct')->gauging_tableware_price($data['tableware_price'],$data['settlement_price'])){
			$this->tuError(D('Eleproduct')->getError());//检测餐具费合理性
		}
		
		
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
    public function edit($product_id = 0){
        if ($product_id = (int) $product_id) {
            $obj = D('Eleproduct');
            if (!($detail = $obj->find($product_id))) {
                $this->tuError('请选择要编辑的菜单管理');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->tuError('请不要操作其他商家的菜单管理');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['product_id'] = $product_id;
                if (false !== $obj->save($data)) {
                    D('Elecate')->updateNum($data['cate_id']);
                    $this->tuSuccess('操作成功', U('eleproduct/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的菜单管理');
        }
    }
	
	
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['product_name'] = htmlspecialchars($data['product_name']);
        if (empty($data['product_name'])) {
            $this->tuError('菜名不能为空');
        }
        $data['desc'] = htmlspecialchars($data['desc']);
        if (empty($data['desc'])) {
            $this->tuError('菜单介绍不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('分类不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        }
		$data['cost_price'] = (int) ($data['cost_price'] * 100);
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->tuError('价格不能为空');
        }
		if($data['cost_price']){
			if($data['price'] >= $data['cost_price']){
				$this->tuError('售价不能高于原价');
			}
		}
		
		//编辑
		$data['tableware_price'] = (int) ($data['tableware_price'] * 100);
        $data['settlement_price'] = (int) ($data['price'] - $data['price'] * $this->ele['rate'] / 1000);
		if(false == D('Eleproduct')->gauging_tableware_price($data['tableware_price'],$data['settlement_price'])){
			$this->tuError(D('Eleproduct')->getError());//检测餐具费合理性
		}
		
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        return $data;
    }
	
		
	//上架下架更新
    public function update($product_id = 0){
        if($product_id = (int) $product_id){
			if(!($detail = D('EleProduct')->find($product_id))){
				$this->tuError('请选择要编辑的菜品');
			}
			$data = array('closed' =>0,'product_id' => $product_id);
			$intro = '上架菜品成功';
			if($detail['closed'] == 0) {
				$data['closed'] = 1;
				$intro = '下架菜品成功';
			}
			if(D('EleProduct')->save($data)){
				$this->tuSuccess($intro, U('eleproduct/index'));
			}
        }else{
            $this->tuError('请选择要删除的菜品管理');
        }
    }
	
    public function delete($product_id = 0){
        if ($product_id = (int) $product_id){
            $obj = D('Eleproduct');
            if (!($detail = $obj->where(array('shop_id' => $this->shop_id, 'product_id' => $product_id))->find())) {
                $this->tuError('请选择要删除的菜单管理');
            }
            D('Elecate')->updateNum($detail['cate_id']);
            $obj->delete($product_id);
            $this->tuSuccess('删除成功', U('eleproduct/index'));
        }
        $this->tuError('请选择要删除的菜单管理');
    }
}