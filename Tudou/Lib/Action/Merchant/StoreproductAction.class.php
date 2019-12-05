<?php
class StoreproductAction extends CommonAction{
    private $create_fields = array('product_name', 'desc', 'cate_id', 'photo', 'cost_price', 'price','tableware_price', 'is_new', 'is_hot', 'is_tuijian', 'create_time', 'create_ip');
    private $edit_fields = array('product_name', 'desc', 'cate_id', 'photo', 'cost_price', 'price', 'tableware_price','is_new', 'is_hot', 'is_tuijian');
    public function _initialize(){
        parent::_initialize();
        $getStoreCate = D('Store')->getStoreCate();
        $this->assign('getStoreCate', $getStoreCate);
        $this->store = D('Store')->find($this->shop_id);
        if(!empty($this->store) && $this->store['audit'] == 0) {
            $this->error('亲，您的申请正在审核中');
        }
        if(empty($this->store) && ACTION_NAME != 'apply'){
            $this->error('您还没有入住便利店频道', U('store/apply'));
        }
        $this->assign('store', $this->store);
        $this->storecates = D('Storecate')->where(array('shop_id' => $this->shop_id, 'closed' => 0))->select();
        $this->assign('storecates', $this->storecates);
    }
    public function index(){
        $obj = D('Storeproduct');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['product_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = $this->shop_id) {
            $map['shop_id'] = $shop_id;
            $this->assign('shop_id', $shop_id);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('product_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $cate_ids = array();
        foreach ($list as $k => $val) {
            if ($val['cate_id']) {
                $cate_ids[$val['cate_id']] = $val['cate_id'];
            }
        }
        if ($cate_ids) {
            $this->assign('cates', D('Storecate')->itemsByIds($cate_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Storeproduct');
            if ($obj->add($data)) {
                D('Storecate')->updateNum($data['cate_id']);
                $this->tuSuccess('添加成功', U('storeproduct/index'));
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
            $this->tuError('商品名不能为空');
        }
        $data['desc'] = htmlspecialchars($data['desc']);
        if (empty($data['desc'])) {
            $this->tuError('商品介绍不能为空');
        }
        $data['shop_id'] = $this->shop_id;
		
        $data['cate_id'] = (int) $data['cate_id'];
        if(empty($data['cate_id'])) {
            $this->tuError('分类不能为空');
        }
		$res = D('Storecate')->where(array('cate_id'=>$data['cate_id']))->find();
		if($res['parent_id'] == 0){
			$this->tuError('请选择二级分类');
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
		$data['tableware_price'] = (int) ($data['tableware_price'] * 100);
        $data['settlement_price'] = (int) ($data['price'] - $data['price'] * $this->store['rate'] / 1000);

		if(false == D('Storeproduct')->gauging_tableware_price($data['tableware_price'],$data['settlement_price'])){
			$this->tuError(D('Storeproduct')->getError());//检测餐具费合理性
		}
		
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 0;
        return $data;
    }
	
	
    public function edit($product_id = 0){
        if ($product_id = (int) $product_id) {
            $obj = D('Storeproduct');
            if (!($detail = $obj->find($product_id))) {
                $this->tuError('请选择要编辑的商品管理');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->tuError('请不要操作其他商家的商品管理');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['product_id'] = $product_id;
                if (false !== $obj->save($data)) {
                    D('Storecate')->updateNum($data['cate_id']);
                    $this->tuSuccess('操作成功', U('storeproduct/index'));
                }
                $this->tuError('操作失败');
            }else{
				$this->assign('parent_id',D('Storecate')->getParentsId($detail['cate_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的商品管理');
        }
    }
	
	
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['product_name'] = htmlspecialchars($data['product_name']);
        if (empty($data['product_name'])) {
            $this->tuError('商品名不能为空');
        }
        $data['desc'] = htmlspecialchars($data['desc']);
        if (empty($data['desc'])) {
            $this->tuError('商品介绍不能为空');
        }
		
        $data['cate_id'] = (int) $data['cate_id'];
        if(empty($data['cate_id'])) {
            $this->tuError('分类不能为空');
        }
		$res = D('Storecate')->where(array('cate_id'=>$data['cate_id']))->find();
		if($res['parent_id'] == 0){
			$this->tuError('请选择二级分类');
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
		$data['tableware_price'] = (int) ($data['tableware_price'] * 100);
        $data['settlement_price'] = (int) ($data['price'] - $data['price'] * $this->store['rate'] / 1000);
		if(false == D('Storeproduct')->gauging_tableware_price($data['tableware_price'],$data['settlement_price'])){
			$this->tuError(D('Storeproduct')->getError());//检测餐具费合理性
		}
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        return $data;
    }
    public function dstorete($product_id = 0){
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            $obj = D('Storeproduct');
            if (!($detail = $obj->where(array('shop_id' => $this->shop_id, 'product_id' => $product_id))->find())) {
                $this->tuError('请选择要删除的商品管理');
            }
            D('Storecate')->updateNum($detail['cate_id']);
            $obj->save(array('product_id' => $product_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('storeproduct/index'));
        }
        $this->tuError('请选择要删除的商品管理');
    }
}