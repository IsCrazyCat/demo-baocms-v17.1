<?php
class EleorderAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        $this->getCfg = D('Eleorder')->getCfg();
    }
    public function index(){
        $Eleorder = D('Eleorder');
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
			$ShopMoney = D('Shopmoney')->where(array('type'=>'ele','order_id'=>$val['order_id']))->find();
			if(!empty($ShopMoney)){
				$list[$k]['actual_settlement_amount'] = $ShopMoney['money'];
			}
			$list[$k]['do'] = M('DeliveryOrder')->where(array('type'=>'1','type_order_id'=>$val['order_id']))->getField('status');
        }
		
        if(!empty($order_ids)){
            $products = D('Eleorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $product_ids = array();
            foreach ($products as $val){
                $product_ids[$val['product_id']] = $val['product_id'];
            }
            $this->assign('products', $products);
            $this->assign('eleproducts', D('Eleproduct')->itemsByIds($product_ids));
        }
        session('ele_order_map', $map);
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('cfg', D('Eleorder')->getCfg());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
    //外卖订单列表导出
    public function export(){
        $orders = D('Eleorder')->where($_SESSION['ele_order_map'])->order(array('order_id' => 'asc'))->select();
        $date = date("Y_m_d", time());
        $filetitle = "外卖订单列表";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array('aa' => '订单编号', 'bb' => '年', 'cc' => '月', 'dd' => '日', 'ee' => '下单时间', 'ff' => '订单类型', 'gg' => '商家名称', 'hh' => '会员昵称', 'ii' => '菜品列表', 'jj' => '菜品总数量', 'kk' => '商品价格（元）', 'll' => '配送费用（元）', 'mm' => '实际支付（元）', 'nn' => '是否打印', 'oo' => '是否点评', 'pp' => '支付时间', 'ss' => '收货地址', 'tt' => '省', 'uu' => '市', 'vv' => '县', 'ww' => '姓名', 'xx' => '电话', 'yy' => '订单留言');
        foreach ($filter as $key => $title) {
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach ($orders as $k => $v) {
            $Shop = D('Shop')->find($v['shop_id']);
            $Users = D('Users')->find($v['user_id']);
            $status = D('Eleorder')->get_export_ele_order_status($v['order_id']);
            //订单状态
            $ele_order_product = D('Eleorder')->get_export_ele_order_product($v['order_id']);
            //订单状态
            $addr = D('Useraddr')->find($v['addr_id']);
            $ele_create_time = date('H:i:s', $v['create_time']);
            $ele_order_create_time_year = date('Y', $v['create_time']);
            $ele_order_create_time_month = date('m', $v['create_time']);
            $ele_order_create_time_day = date('d', $v['create_time']);
            if ($v['is_print'] == 1) {
                $is_print = '已打印';
            } else {
                $is_print = '未打印';
            }
            if ($v['is_dianping'] == 1) {
                $is_dianping = '已点评';
            } else {
                $is_dianping = '未点评';
            }
            $filter = array('aa' => '订单编号', 'bb' => '年', 'cc' => '月', 'dd' => '日', 'ee' => '下单时间', 'ff' => '订单类型', 'gg' => '商家名称', 'hh' => '会员昵称', 'ii' => '菜品列表', 'jj' => '菜品总数量', 'kk' => '商品价格（元）', 'll' => '配送费用（元）', 'mm' => '实际支付（元）', 'nn' => '是否打印', 'oo' => '是否点评', 'pp' => '支付时间', 'ss' => '收货地址', 'tt' => '省', 'uu' => '市', 'vv' => '县', 'ww' => '姓名', 'xx' => '电话', 'yy' => '订单留言');
            $orders[$k]['aa'] = $v['order_id'];
            $orders[$k]['bb'] = $ele_order_create_time_year;
            $orders[$k]['cc'] = $ele_order_create_time_month;
            $orders[$k]['dd'] = $ele_order_create_time_day;
            $orders[$k]['ee'] = $ele_create_time;
            $orders[$k]['ff'] = $status;
            $orders[$k]['gg'] = $Shop['shop_name'];
            $orders[$k]['hh'] = $Users['nickname'];
            $orders[$k]['ii'] = $ele_order_product;
            $orders[$k]['jj'] = $v['num'];
            $orders[$k]['kk'] = $v['total_price'] / 100;
            $orders[$k]['ll'] = $v['logistics'] / 100;
            $orders[$k]['mm'] = $v['need_pay'] / 100;
            $orders[$k]['nn'] = $status;
            $orders[$k]['oo'] = $is_print;
            $orders[$k]['pp'] = $is_dianping;
            $orders[$k]['ss'] = $addr['addr'];
            $orders[$k]['tt'] = $this->city[$addr['city_id']]['name'];
            $orders[$k]['uu'] = $this->area[$addr['area_id']]['area_name'];
            $orders[$k]['vv'] = $this->business[$addr['business_id']]['business_name'];
            $orders[$k]['ww'] = $addr['name'];
            $orders[$k]['xx'] = $addr['mobile'];
            $orders[$k]['yy'] = $v['message'];
            foreach ($filter as $key => $title) {
                $html .= $orders[$k][$key] . "\t,";
            }
            $html .= "\n";
        }
        /* 输出CSV文件 */
        ob_end_clean();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename={$fileName}.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $html;
        exit;
    }
    //外卖详情
    public function detail($order_id = 0){
        $order_id = I('order_id', '', 'intval,trim');
        if (!($detail = D('EleOrder')->find($order_id))) {
            $this->error('订单不存在');
        } else {
            $addr = D('Useraddr')->where(array('addr_id' => $detail['addr_id']))->find();
            $detail['addr'] = $addr;
            $user = D('Users')->where(array('user_id' => $detail['user_id']))->find();
            $detail['user'] = $user;
            $EleOrderProduct = D('EleOrderProduct')->where(array('order_id' => $detail['order_id']))->select();
            if ($EleOrderProduct) {
                $product_ids = array();
                foreach ($EleOrderProduct as $k => $v) {
                    $product_ids[$v['product_id']] = $v['product_id'];
                }
                $Product = D('EleProduct')->where(array('product_id' => array('in', $product_ids)))->select();
                $products = array();
                foreach ($Product as $kk => $vv) {
                    $products[$vv['product_id']] = $vv;
                }
                $this->assign('eleorderproduct', $EleOrderProduct);
                $this->assign('products', $products);
                $addr = D('UserAddr')->find($detail['addr_id']);
                $this->assign('addr', $addr);
                $DeliveryOrder = D('DeliveryOrder')->where(array('type' => 1, 'type_order_id' => $order['order_id']))->find();
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
            $obj = D('Eleorder');
            $obj->save(array('order_id' => $order_id, 'closed' => 1));
            $this->tuSuccess('取消订单成功', U('eleorder/index'));
        } else {
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
    //外卖催单列表
    public function reminder(){
        $Elereminder = D('Elereminder');
        import('ORG.Util.Page');
        if ($reminder_id = (int) $this->_param('reminder_id')) {
            $map['reminder_id'] = $reminder_id;
            $this->assign('reminder_id', $reminder_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
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
        $count = $Elereminder->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Elereminder->where($map)->order(array('reminder_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shop', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //催单列表删除
    public function reminder_delete($reminder_id = 0){
        if (is_numeric($reminder_id) && ($reminder_id = (int) $reminder_id)) {
            $obj = D('Elereminder');
            $obj->delete($reminder_id);
            $this->tuSuccess('删除成功', U('eleorder/reminder'));
        } else {
            $reminder_id = $this->_post('activity_id', false);
            if (is_array($reminder_id)) {
                $obj = D('Elereminder');
                foreach ($reminder_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('eleorder/reminder'));
            }
            $this->tuError('请选择要删除的催单');
        }
    }
	//获取状态
	public function getAccounts($order_id = 0){
        $data = $_POST;
        $order_id = $data['order_id'];
        if(!($detail = D('Eleorder')->find($order_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没有该订单'.$order_id));
        }
        if($detail['closed'] != 0){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该订单已经被删除'));
        }
		if(!($ShopMoney = D('Shopmoney')->where(array('type'=>'ele','order_id'=>$order_id))->find())){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没找到结算详情'));
        }else{
			$msg .='结算ID【'.$ShopMoney['money_id'].'】';
			$msg .='结算金额:'.round($ShopMoney['money']/100,2).'元';
			$this->ajaxReturn(array('status' => 'success', 'msg' =>$msg));
		}
		
		
	}
}