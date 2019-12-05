<?php
class MarketproductAction extends CommonAction{
    private $create_fields = array('product_name', 'shop_id', 'cate_id', 'photo', 'cost_price','price','tableware_price','is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num', 'create_time', 'create_ip');
    private $edit_fields = array('product_name', 'shop_id', 'cate_id', 'photo', 'cost_price','price','tableware_price', 'settlement_price','is_new', 'is_hot', 'is_tuijian', 'sold_num', 'month_num');
    public function index(){
        $obj = D('Marketproduct');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['product_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('product_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $cate_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            if ($val['cate_id']) {
                $cate_ids[$val['cate_id']] = $val['cate_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        if ($cate_ids) {
            $this->assign('cates', D('Marketcate')->itemsByIds($cate_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Marketproduct');
            if ($obj->add($data)) {
                D('Marketcate')->updateNum($data['cate_id']);
                $this->tuSuccess('添加成功', U('marketproduct/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['product_name'] = htmlspecialchars($data['product_name']);
        if (empty($data['product_name'])) {
            $this->tuError('商品不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->tuError('商家不能为空');
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
		$data['tableware_price'] = (int) ($data['tableware_price'] * 100);
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        $data['sold_num'] = (int) $data['sold_num'];
        $data['month_num'] = (int) $data['month_num'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($product_id = 0){
        if ($product_id = (int) $product_id) {
            $obj = D('Marketproduct');
            if (!($detail = $obj->find($product_id))) {
                $this->tuError('请选择要编辑的菜单管理');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['product_id'] = $product_id;
                if (false !== $obj->save($data)) {
                    D('Marketcate')->updateNum($data['cate_id']);
                    $this->tuSuccess('操作成功', U('marketproduct/index'));
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
            $this->tuError('商品不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->tuError('商家不能为空');
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
        if(empty($data['price'])) {
            $this->tuError('价格不能为空');
        }
		if($data['cost_price']){
			if($data['price'] >= $data['cost_price']){
				$this->tuError('售价不能高于原价');
			}
		}
		$data['tableware_price'] = (int) ($data['tableware_price'] * 100);
		$data['settlement_price'] = (int) ($data['settlement_price'] * 100);
        if (empty($data['settlement_price'])) {
            $this->tuError('结算价格不能为空');
        }
		if(false == D('Marketproduct')->gauging_tableware_price($data['tableware_price'],$data['settlement_price'])){
			$this->tuError(D('Marketproduct')->getError());//检测餐具费合理性
		}
        $data['is_new'] = (int) $data['is_new'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_tuijian'] = (int) $data['is_tuijian'];
        $data['sold_num'] = (int) $data['sold_num'];
        $data['month_num'] = (int) $data['month_num'];
        return $data;
    }
    public function delete($product_id = 0){
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            $obj = D('Marketproduct');
            $obj->delete($product_id);
            $this->tuSuccess('删除成功', U('marketproduct/index'));
        } else {
            $product_id = $this->_post('product_id', false);
            if (is_array($product_id)) {
                $obj = D('Marketproduct');
                foreach ($product_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('marketproduct/index'));
            }
            $this->tuError('请选择要删除的菜单管理');
        }
    }
    public function audit($product_id = 0)
    {
        if (is_numeric($product_id) && ($product_id = (int) $product_id)) {
            $obj = D('MarketProduct');
            $r = $obj->where('product_id =' . $product_id)->find();
            $obj->save(array('product_id' => $product_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('marketproduct/index'));
        } else {
            $product_id = $this->_post('product_id', false);
            if (is_array($product_id)) {
                $obj = D('MarketProduct');
                foreach ($product_id as $id) {
                    $r = $obj->where('product_id =' . $id)->find();
                    $obj->save(array('product_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('marketproduct/index'));
            }
            $this->tuError('请选择要审核的商品');
        }
    }
	
	
	public function logs(){
        $obj = D('Marketorderproduct');
        import('ORG.Util.Page');
        $map = array();
        if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
       
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
		$this->assign('num', $num = $obj->where($map)->sum('num'));
		$this->assign('total_price', $total_price = $obj->where($map)->sum('total_price'));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}