<?php
class MarketorderAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        $this->getCfg = D('Marketorder')->getCfg();
        $this->city = D('City')->fetchAll();
        $this->area = D('Area')->fetchAll();
        $this->business = D('Business')->fetchAll();
    }
    public function index(){
        $obj = D('Marketorder');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
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
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$ShopMoney = D('Shopmoney')->where(array('type'=>'ele','order_id'=>$val['order_id']))->find();
			if(!empty($ShopMoney)){
				$list[$k]['actual_settlement_amount'] = $ShopMoney['money'];
			}
			
        }
        if (!empty($order_ids)) {
            $products = D('Marketorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $product_ids = array();
            foreach ($products as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }
            $this->assign('products', $products);
            $this->assign('eleproducts', D('Marketproduct')->itemsByIds($product_ids));
        }
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('cfg', D('Marketorder')->getCfg());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
   
    //菜市场详情
    public function detail($order_id = 0){
        $order_id = I('order_id', '', 'intval,trim');
        if (!($detail = D('MarketOrder')->find($order_id))) {
            $this->error('订单不存在');
        } else {
            $addr = D('Useraddr')->where(array('addr_id' => $detail['addr_id']))->find();
            $detail['addr'] = $addr;
            $user = D('Users')->where(array('user_id' => $detail['user_id']))->find();
            $detail['user'] = $user;
            $MarketOrderProduct = D('MarketOrderProduct')->where(array('order_id' => $detail['order_id']))->select();
            if ($MarketOrderProduct) {
                $product_ids = array();
                foreach ($MarketOrderProduct as $k => $v) {
                    $product_ids[$v['product_id']] = $v['product_id'];
                }
                $Product = D('MarketProduct')->where(array('product_id' => array('in', $product_ids)))->select();
                $products = array();
                foreach ($Product as $kk => $vv) {
                    $products[$vv['product_id']] = $vv;
                }
                $this->assign('eleorderproduct', $MarketOrderProduct);
                $this->assign('products', $products);
                $addr = D('UserAddr')->find($detail['addr_id']);
                $this->assign('addr', $addr);
                $DeliveryOrder = D('DeliveryOrder')->where(array('type' =>3, 'type_order_id' => $order['order_id']))->find();
                if ($DeliveryOrder) {
                    if ($do['delivery_id'] > 0) {
                        $delivery = D('Delivery')->find($DeliveryOrder['delivery_id']);
                        $this->assign('delivery', $delivery);
                    }
                    $this->assign('deliveryorder', $DeliveryOrder);
                }
            }
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function delete($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Marketorder');
            $obj->save(array('order_id' => $order_id, 'closed' => 1));
            $this->tuSuccess('取消订单成功', U('marketorder/index'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Marketorder');
                foreach ($order_id as $id) {
                    $detail = $obj->find($id);
                    if ($detail['status'] >= 1) {
                        $obj->save(array('order_id' => $id, 'closed' => 1));
                    }
                }
                $this->tuSuccess('取消订单成功', U('marketorder/index'));
            }
            $this->tuError('请选择要取消的订单');
        }
    }
    public function tui($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $detail = D('Marketorder')->find($order_id);
            if ($detail['status'] != 3) {
                $this->tuError('菜市场状态不正确');
            }
            if ($detail['status'] == 3) {
                if (D('Marketorder')->save(array('order_id' => $order_id, 'status' => 4))) {
                    $obj = D('Users');
                    if ($detail['need_pay'] > 0) {
                        D('Sms')->marketorder_refund_user($order_id);
                        D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 4);
					    D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 4);
                        $obj->addMoney($detail['user_id'], $detail['need_pay'], '菜市场退款');
                    }
                }
            }
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Users');
                $eleorder = D('Marketorder');
                foreach ($order_id as $id) {
                    $detail = $eleorder->find($id);
                    if ($detail['status'] == 3) {
                        if (D('Marketorder')->save(array('order_id' => $order_id, 'status' => 4))) {
                            if ($detail['need_pay'] > 0) {
                                D('Sms')->marketorder_refund_user($order_id);
                                D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 4);
					    		D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 4);
                                $obj->addMoney($detail['user_id'], $detail['need_pay'], '菜市场退款');
                            }
                        }
                    } else {
                        $this->tuError('退款失败');
                    }
                }
            }
        }
        $this->tuSuccess('退款成功', U('marketorder/index'));
    }
   
	//获取状态
	public function getAccounts($order_id = 0){
        $data = $_POST;
        $order_id = $data['order_id'];
        if(!($detail = D('Marketorder')->find($order_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没有该订单'.$order_id));
        }
        if($detail['closed'] != 0){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该订单已经被删除'));
        }
		if(!($ShopMoney = D('Shopmoney')->where(array('type'=>'market','order_id'=>$order_id))->find())){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没找到结算详情'));
        }else{
			$msg .='结算ID【'.$ShopMoney['money_id'].'】';
			$msg .='结算金额:'.round($ShopMoney['money']/100,2).'元';
			$this->ajaxReturn(array('status' => 'success', 'msg' =>$msg));
		}
		
		
	}
}