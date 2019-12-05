<?php 
class StockAction extends CommonAction{
    private $create_fields = array('shop_id','title','intro','photo','thumb','city_id','area_id','business_id','price', 'num','sold_num','orderby','views','is_local','prestige','details');
	private $edit_fields = array('shop_id','title','intro','photo','thumb','city_id','area_id','business_id','price', 'num','sold_num','orderby','views','is_local','prestige','details');
		
    public function _initialize(){
        parent::_initialize();
        $this->getCfg = D('Stock')->getCfg();
        $this->assign('getCfg', $this->getCfg);
    }
    public function index(){
        $Stock = D('Stock');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
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
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $Stock->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Stock->where($map)->order(array('stock_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
				$user_ids[$val['user_id']] = $val['user_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
			$this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $thumb = $this->_param('thumb', FALSE);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isimage($val)) {
                    unset($thumb[$k]);
                }
            }
            $data['thumb'] = serialize($thumb);
            $obj = D('Stock');
            if ($stock_id = $obj->add($data)) {
                $this->tuSuccess('添加成功', U('Stock/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', FALSE), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('股权名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('股权简介不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (!empty($data['shop_id'])) {
            $shop = D('Shop')->find($data['shop_id']);
            if (empty($shop)) {
                $this->tuError('请选择正确的商家');
            }
            $data['city_id'] = $shop['city_id'];
            $data['area_id'] = $shop['area_id'];
        } else {
            $data['city_id'] = $this->_CONFIG['site']['city_id'];
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isimage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price']*100);
        if (empty($data['price'])) {
            $this->tuError('价格不能为空');
        }
 		$data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->tuError('总库存不能为空');
        }
		if (($data['price']*$data['num']) % 100 != 0) {
            $this->tuError('总库存必须为单价*100,后100的整数');
        }
		$data['orderby'] = (int) $data['orderby'];
		$data['views'] = (int) $data['views'];
        $data['details'] = securityeditorhtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('股权详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('股权详情含有敏感词：' . $words);
        }
		
		$data['is_local'] = (int) $data['is_local'];
		$data['prestige'] = (int) $data['prestige'];
		
		//if(($data['prestige']*$this->_CONFIG['stock']['deduction']) > $data['price']){
			//$this->tuError('积分抵扣价格不能高于股权单价');
		//}

        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['audit'] = 1;
        return $data;
    }
   
    public function edit($stock_id = 0){
        if ($stock_id = (int) $stock_id) {
            $obj = D('Stock');
            if (!($detail = $obj->find($stock_id))) {
                $this->error('请选择要编辑的股权');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $thumb = $this->_param('thumb', FALSE);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isimage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['thumb'] = serialize($thumb);
                $data['stock_id'] = $stock_id;
                if (FALSE !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('stock/index'));
                }
                $this->tuError('操作失败');
            } else {
                $thumb = unserialize($detail['thumb']);
                $this->assign('thumb', $thumb);
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的股权');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', FALSE), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('股权名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('股权简介不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        if (!empty($data['shop_id'])) {
            $shop = D('Shop')->find($data['shop_id']);
            if (empty($shop)) {
                $this->tuError('请选择正确的商家');
            }
            $data['city_id'] = $shop['city_id'];
            $data['area_id'] = $shop['area_id'];
        } else {
            $data['city_id'] = $this->_CONFIG['site']['city_id'];
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isimage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price']*100);
        if (empty($data['price'])) {
            $this->tuError('价格不能为空');
        }
 		$data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->tuError('总库存不能为空');
        }
		if (($data['price']*$data['num']) % 100 != 0) {
            $this->tuError('总库存必须为单价*100,后100的整数');
        }
		$data['orderby'] = (int) $data['orderby'];
		$data['views'] = (int) $data['views'];
        $data['details'] = securityeditorhtml($data['details']);
        if (empty($data['details'])) {
            $this->tuError('股权详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->tuError('股权详情含有敏感词：' . $words);
        }
		$data['is_local'] = (int) $data['is_local'];
		$data['prestige'] = (int) $data['prestige'];
		
		//if(($data['prestige']*$this->_CONFIG['stock']['deduction']) > $data['price']){
			//$this->tuError('积分抵扣价格不能高于股权单价');
		//}

        return $data;
    }
    public function delete($stock_id = 0){
        if (is_numeric($stock_id) && ($stock_id = (int) $stock_id)) {
            $obj = D('Stock');
            $obj->save(array('stock_id' => $stock_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('stock/index'));
        } else {
            $stock_id = $this->_post('stock_id', FALSE);
            if (is_array($stock_id)) {
                $obj = D('Stock');
                foreach ($stock_id as $id) {
                    $obj->save(array('stock_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('stock/index'));
            }
            $this->tuError('请选择要删除的股权');
        }
    }
    public function audit($stock_id = 0){
        if (is_numeric($stock_id) && ($stock_id = (int) $stock_id)) {
            $obj = D('Stock');
            $obj->save(array('stock_id' => $stock_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('Stock/index'));
        } else {
            $stock_id = $this->_post('stock_id', FALSE);
            if (is_array($stock_id)) {
                $obj = D('Stock');
                $error = 0;
                foreach ($stock_id as $id) {
                    $obj->save(array('stock_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('stock/index'));
            }
            $this->tuError('请选择要审核的股权');
        }
    }
	
	//后台中心股权订单数据加载
	public function order(){
        $obj = D("Stockorder");
        import("ORG.Util.Page");
        $map = array();
        if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id|stock_number'] = $order_id;
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
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $obj->where($map)->count();
        $Page = new Page( $count, 10 );
        $show = $Page->show();
        $list = $obj->where($map)->order(array("order_id" => "desc" ))->limit( $Page->firstRow.",".$Page->listRows )->select();
        $shop_ids = $stock_ids = $user_ids = array( );
        foreach ($list as $k => $val ){
            $user_ids[$val['user_id']] = $val['user_id'];
			$stock_ids[$val['stock_id']] = $val['stock_id'];
			$shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign("users", D("Users")->itemsByIds($user_ids));
		$this->assign("stock", D("Stock")->itemsByIds($stock_ids));
		$this->assign("shops", D("Shop")->itemsByIds($shop_ids));
        $this->assign("list", $list);
        $this->assign("page", $show);
        $this->display();
    }

	//后台股权订单删除订单
	 public function order_delete($order_id){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D("Stockorder");
            if (!($detail = $obj->find($order_id))) {
                $this->tuError("股权订单不存在");
            }
            if ($detail['status'] != 0) {
                $this->tuError("该股权订单状态不允许被删除");
            }
			if ($obj->delete($order_id)) {
                $this->tuSuccess("删除成功", U("Stock/order"));
            }else{
				$this->tuError("删除失败");
			}
        }
    }
	
	
}