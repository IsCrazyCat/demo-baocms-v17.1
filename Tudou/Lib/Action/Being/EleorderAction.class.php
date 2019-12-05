<?php
class EleorderAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        $this->getCfg = D('Eleorder')->getCfg();
        $this->city = D('City')->fetchAll();
        $this->area = D('Area')->fetchAll();
        $this->business = D('Business')->fetchAll();
    }
    public function index(){
        $Eleorder = D('Eleorder');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
		$map['shop_id'] = $this->shop_ids;
        if($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
        if($shop_id = (int) $this->_param('shop_id')) {
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
        if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $Eleorder->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Eleorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($order_ids)) {
            $products = D('Eleorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $product_ids = array();
            foreach ($products as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }
            $this->assign('products', $products);
            $this->assign('eleproducts', D('Eleproduct')->itemsByIds($product_ids));
        }
        session('ele_order_map', $map);
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('cfg', D('Eleorder')->getCfg());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
   

	
	//详情
	public function detail($order_id = 0,$st = 0){
        if($order_id = (int) $order_id){
            $obj = D('Eleorder');
			if(!($detail = $obj->find($order_id))) {
				$this->tuError('订单不存在');
			}
			if(!in_array($detail['shop_id'],$this->shop_ids['1'])){
				$this->tuError('非法操作');
			}
			$EleOrderProduct = D('EleOrderProduct')->where(array('order_id' => $detail['order_id']))->select();
            if($EleOrderProduct){
                $product_ids = array();
                foreach($EleOrderProduct as $k => $v){
                    $product_ids[$v['product_id']] = $v['product_id'];
                }
                $Product = D('EleProduct')->where(array('product_id' => array('in', $product_ids)))->select();
                $products = array();
                foreach($Product as $kk => $vv){
                    $products[$vv['product_id']] = $vv;
                }
                $this->assign('eleorderproduct', $EleOrderProduct);
                $this->assign('products', $products);
                $this->assign('addr',$addr = D('UserAddr')->find($detail['addr_id']));
                $DeliveryOrder = D('DeliveryOrder')->where(array('type' => 1, 'type_order_id' =>$detail['order_id']))->find();
                if ($DeliveryOrder) {
                    if($DeliveryOrder['delivery_id'] > 0){
                        $delivery = D('Delivery')->find($DeliveryOrder['delivery_id']);
                        $this->assign('delivery', $delivery);
                    }
                    $this->assign('deliveryorder', $DeliveryOrder);
                }
            }
			$this->assign('st',$st);
			$this->assign('cfg', D('Eleorder')->getCfg());
            $this->assign('detail', $detail);
            $this->display();
        }else{
            $this->tuError('请选择要操作的订单');
        }
    }
	
	
	//分站打印
	public function order_print($order_id = 0,$st = 0){
        if($order_id = (int) $order_id){
            $obj = D('Eleorder');
			if(!($detail = $obj->find($order_id))) {
				$this->tuError('订单不存在');
			}
			if(!in_array($detail['shop_id'],$this->shop_ids['1'])){
				$this->tuError('非法操作');
			}
			$obj->combination_ele_print($order_id, $detail['addr_id']);
			$obj->updateCount($order_id, 'is_print');
            $this->tuSuccess('操作成功', U('eleorder/index',array('st'=>$st)));
        }else{
            $this->tuError('请选择要操作的订单');
        }
    }
	
	
	//点击接单
	public function delivery($order_id = 0,$st = 0){
        if($order_id = (int) $order_id){
            $obj = D('Eleorder');
			if(!($detail = $obj->find($order_id))) {
				$this->tuError('订单不存在');
			}
			if(!in_array($detail['shop_id'],$this->shop_ids['1'])){
				$this->tuError('非法操作');
			}
			if($this->isPost()){
				$id = (int) $this->_post('id');
				if(empty($id)){
					$this->tuError('必须选择配送员');
				}
				$order_id = (int) $this->_post('order_id');
				if(empty($order_id)){
					$this->tuError('没找到订单ID');
				}
				$st = (int) $this->_post('st');
				$obj->combination_ele_print($order_id, $detail['addr_id']);
				$obj->updateCount($order_id, 'is_print');
				if($obj->where(array('order_id'=>$order_id))->save(array('status'=>2,'orders_time' => NOW_TIME))){
					D('DeliveryOrder')->where(array('type' => 1,'type_order_id' =>$order_id))->save(array('delivery_id'=>$id,'update_time'=>NOW_TIME));
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 1,$status = 2);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 1,$status = 2);
					$this->tuSuccess('恭喜您选择配送员发货成功', U('eleorder/index',array('st'=>$st)));
				}else{
					$this->tuError('发货失败');
				}
			}else{
				import('ORG.Util.Page');
				$map = array();
				if($keyword = $this->_param('keyword','htmlspecialchars')){
					$map['name|mobile|addr|user_id'] = array('LIKE','%'.$keyword.'%');
					$this->assign('keyword',$keyword);
				}
				$count = D('Delivery')->where($map)->count(); 
				$Page = new Page($count,8); 
				$pager = $Page->show();
				$list = D('Delivery')->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
				foreach ($list as $k => $val) {
					$list[$k]['order_have'] = D('DeliveryOrder')->where(array('delivery_id'=>$val['id'],'status'=>2,'closed'=>0))->count();
					$list[$k]['order_ok'] = D('DeliveryOrder')->where(array('delivery_id'=>$val['id'],'status'=>8,'closed'=>0))->count();
				}
				$this->assign('list', $list);
				$this->assign('page', $pager); 
				$this->assign('order_id', $order_id);
				$this->assign('st',$st);
				$this->display(); 
			}
        }else{
            $this->tuError('请选择要操作的订单');
        }
    }
	
    public function delete($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Eleorder');
			if(!($detail = $obj->find($order_id))) {
				$this->tuError('不存在订单');
			}
			if(!in_array($detail['shop_id'],$this->shop_ids)){
				$this->tuError('非法操作');
			}
            $obj->save(array('order_id' => $order_id, 'closed' => 1));
            $this->tuSuccess('取消订单成功', U('eleorder/index'));
        }else{
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Eleorder');
                foreach ($order_id as $id) {
                    $detail = $obj->find($id);
                    if ($detail['status'] >= 1) {
                        $obj->save(array('order_id' => $id, 'closed' => 1));
                    }
                }
                $this->tuSuccess('取消订单成功', U('eleorder/index'));
            }
            $this->tuError('请选择要取消的订单');
        }
    }
    public function tui($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $detail = D('Eleorder')->find($order_id);
            if ($detail['status'] != 3) {
                $this->tuError('订餐状态不正确');
            }
            if ($detail['status'] == 3) {
                if (D('Eleorder')->save(array('order_id' => $order_id, 'status' => 4))) {
                    $obj = D('Users');
                    if ($detail['need_pay'] > 0) {
                        D('Sms')->eleorder_refund_user($order_id);
                        D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 3,$status = 4);
					    D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 3,$status = 4);
                        $obj->addMoney($detail['user_id'], $detail['need_pay'], '订餐退款');
                    }
                }
            }
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Users');
                $eleorder = D('Eleorder');
                foreach ($order_id as $id) {
                    $detail = $eleorder->find($id);
                    if ($detail['status'] == 3) {
                        if (D('Eleorder')->save(array('order_id' => $order_id, 'status' => 4))) {
                            if ($detail['need_pay'] > 0) {
                                D('Sms')->eleorder_refund_user($order_id);
                                D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 3,$status = 4);
					    		D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 3,$status = 4);
                                $obj->addMoney($detail['user_id'], $detail['need_pay'], '订餐退款');
                            }
                        }
                    } else {
                        $this->tuError('退款失败');
                    }
                }
            }
        }
        $this->tuSuccess('退款成功', U('eleorder/index'));
    }
	
	
	public function getAddrs($order_id = 0){
        $data = $_POST;
        $order_id = $data['order_id'];
        if(!($detail = D('Eleorder')->find($order_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没有该订单'.$order_id));
        }
        if($detail['closed'] != 0){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该订单已经被删除'));
        }
		if(!in_array($detail['shop_id'],$this->shop_ids['1'])){
			$this->ajaxReturn(array('status' => 'error', 'msg' => '该订单不属于您管理'.$order_id));
		}
		if(!($UserAddr = D('UserAddr')->find($detail['addr_id']))){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '非常抱歉，收货地址不存在或者已经删除'));
        }else{
			$City = D('City')->find($detail['city_id']);
			$Area = D('Area')->find($detail['area_id']);
			$Business = D('Business')->find($detail['business_id']);
			$msg='地区：'.$City['name'].'->'.$Area['area_name'].'->'.$Business['business_name'].'姓名：'.$UserAddr['name'].'电话：'.$UserAddr['mobile'].'详细地址：'.$UserAddr['addr']; 
			$this->ajaxReturn(array('status' => 'success', 'msg' =>$msg));
		}
		
		
	}
   
}
