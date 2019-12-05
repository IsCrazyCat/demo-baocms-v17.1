<?php
class PayAction extends CommonAction{
    protected function ele_success($message, $detail){
        $order_id = $detail['order_id'];
        $eleorder = D('Eleorder')->find($order_id);
        $detail['single_time'] = $eleorder['create_time'];
        $detail['settlement_price'] = $eleorder['settlement_price'];
        $detail['new_money'] = $eleorder['new_money'];
        $detail['fan_money'] = $eleorder['fan_money'];
        $addr_id = $eleorder['addr_id'];
        $product_ids = array();
        $ele_goods = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        foreach ($ele_goods as $k => $val) {
            if (!empty($val['product_id'])) {
                $product_ids[$val['product_id']] = $val['product_id'];
            }
        }
        $addr = D('Useraddr')->find($addr_id);
        $this->assign('addr', $addr);
        $this->assign('ele_goods', $ele_goods);
        $this->assign('products', D('Eleproduct')->itemsByIds($product_ids));
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('ele');
    }
    protected function goods_success($message, $detail){
        $order_ids = array();
        if (!empty($detail['order_id'])) {
            $order_ids[] = $detail['order_id'];
        } else {
            $order_ids = explode(',', $detail['order_ids']);
        }
        $goods = $good_ids = $paddress = array();
        $use_integral = 0;
        foreach ($order_ids as $k => $val) {
            if (!empty($val)) {
                $order = D('Order')->find($val);
                $paddress = D('Paddress')->find($order['address_id']);
                $ordergoods = D('Ordergoods')->where(array('order_id' => $val))->select();
                foreach ($ordergoods as $a => $v) {
                    $good_ids[$v['goods_id']] = $v['goods_id'];
                    $use_integral += $v['use_integral'];
                }
            }
            $goods[$k] = $ordergoods;
            $paddress[$k] = $paddress;
        }
        $this->assign('use_integral', $use_integral);
        $this->assign('paddress', $paddress[0]);
        $this->assign('goods', $goods);
        $this->assign('good', D('Goods')->itemsByIds($good_ids));
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('goods');
    }
    protected function hotel_success($message, $detail){
        $order_id = (int) $detail['order_id'];
        $order = D('Hotelorder')->find($order_id);
        $detail['single_time'] = $order['create_time'];
        $room = D('Hotelroom')->find($order['room_id']);
        $hotel = D('Hotel')->find($room['hotel_id']);
        $this->assign('hotel', $hotel);
        $this->assign('order', $order);
        $this->assign('room', $room);
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('hotel');
    }
    protected function farm_success($message, $detail){
        $order_id = (int) $detail['order_id'];
        $order = D('FarmOrder')->find($order_id);
        $f = D('FarmPackage')->find($order['pid']);
        $shop = D('Shop')->find($farm['shop_id']);
        $farm = D('Farm')->where(array('farm_id' => $f['farm_id']))->find();
        $this->assign('farm', $farm);
        $this->assign('order', $order);
        $this->assign('f', $f);
        $this->assign('shop', $shop);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('farm');
    }
	//众筹支付成功
	 protected function crowd_success($message, $detail){
        $order_id = (int) $detail['order_id'];
        $order = D('CrowdOrder')->find($order_id);
        $Crowdtype = D('Crowdtype')->find($order['type_id']);//获取众筹类型
        $Crowd = D('Crowd')->find($order['goods_id']);//获取众筹商品
        $this->assign('crowdtype', $Crowdtype);
        $this->assign('order', $order);
        $this->assign('crowd', $crowd);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('crowd');
    }
	
	//家政支付成功
	 protected function appoint_success($message, $detail){
        $order_id = (int) $detail['order_id'];
        $order = D('Appointorder')->find($order_id);
        $Appoint = D('Appoint')->find($order['appoint_id']);//获取众筹商品
        $this->assign('order', $order);
        $this->assign('appoint', $Appoint);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('appoint');
    }
	//云购支付成功
	 protected function cloud_success($message, $detail){
        $log_id = (int) $detail['order_id'];
        $cloudlogs = D('Cloudlogs')->find($log_id);
        $cloudgoods = D('Cloudgoods')->find($cloudlogs['goods_id']);//获取商品
        $this->assign('cloudlogs', $cloudlogs);
        $this->assign('cloudgoods', $cloudgoods);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('cloud');
    }

    public function booking($order_id){
        $Bookingorder = D('Bookingorder');
        $Bookingyuyue = D('Bookingyuyue');
        $Bookingmenu = D('Bookingmenu');
        if (!($order = $Bookingdingorder->where('order_id = ' . $order_id)->find())) {
            $this->tuError('该订单不存在');
        } else {
            if (!($yuyue = $Bookingyuyue->where('ding_id = ' . $order['ding_id'])->find())) {
                $this->tuError('该订单不存在');
            } else {
                if ($yuyue['user_id'] != $this->uid) {
                    $this->error('非法操作');
                } else {
                    $arr = $Bookingorder->get_detail($this->shop_id, $order, $yuyue);
                    $menu = $Bookingmenu->shop_menu($this->shop_id);
                    $this->assign('yuyue', $yuyue);
                    $this->assign('order', $order);
                    $this->assign('order_id', $order_id);
                    $this->assign('arr', $arr);
                    $this->assign('menu', $menu);
                    $this->display();
                }
            }
        }
    }
	
	protected function booking_success($message, $detail) {
        $order_id = (int)$detail['order_id'];
        $order = D('Bookingorder')->find($order_id);
        $bookingordermenu = D('Bookingordermenu')->where(array('order_id'=>$order_id))->select();
        $menu_ids = array();
        foreach($bookingordermenu as $k=>$val){
            $menu_ids[$val['menu_id']] = $val['menu_id'];
        }
        $this->assign('menus',D('Bookingmenu')->itemsByIds($menu_ids));
        $this->assign('shop',D('Booking')->find($order['shop_id']));
        $this->assign('dingmenu',$dingmenu);
        $this->assign('order',$order);
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('booking');
    }
   
    protected function other_success($message, $detail){
        $tuanorder = D('Tuanorder')->find($detail['order_id']);
        if (!empty($tuanorder['branch_id'])) {
            $branch = D('Shopbranch')->find($tuanorder['branch_id']);
            $addr = $branch['addr'];
        } else {
            $shop = D('Shop')->find($tuanorder['shop_id']);
            $addr = $shop['addr'];
        }
        $this->assign('addr', $addr);
        $tuans = D('Tuan')->find($tuanorder['tuan_id']);
        $this->assign('tuans', $tuans);
        $this->assign('tuanorder', $tuanorder);
        $this->assign('message', $message);
        $this->assign('detail', $detail);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('other');
    }
	
	//五折卡支付成功
	 protected function zhe_success($message, $detail){
        $order_id = (int) $detail['order_id'];
        $Zheorder = D('Zheorder')->find($order_id);
        $Zhe = D('Zhe')->find($Zheorder['zhe_id']);
        $this->assign('zheorder', $Zheorder);
        $this->assign('zhe', $Zhe);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('zhe');
    }
	//课程支付成功
	 protected function edu_success($message, $detail){
        $order_id = (int) $detail['order_id'];
        $EduOrder = D('EduOrder')->find($order_id);
		$course = D('Educourse')->find($EduOrder['course_id']);
        $edu = D('Edu')->where(array('edu_id'=>$EduOrder['edu_id']))->find();
        $this->assign('eduorder', $EduOrder);
		$this->assign('course', $course);
        $this->assign('edu', $edu);
        $this->assign('detail', $detail);
        $this->assign('message', $message);
        $this->assign('paytype', D('Payment')->getPayments());
        $this->display('edu');
    }
	
	public function pay($code= ''){
        $logs_id = (int) $this->_param('logs_id');
		$code = $this->_param('code', 'htmlspecialchars');
		
		$name = ($code =='money') ? '余额' : '积分';
		
		if(empty($this->uid)){
            $this->error('UID不存在', U('members/index/index'));
        }
        if(empty($logs_id)){
            $this->error('支付ID不存在', U('members/index/index'));
        }
		if(empty($code)){
            $this->error('支付方式不存在', U('members/index/index'));
        }
        if(!($detail = D('Paymentlogs')->find($logs_id))){
            $this->error('支付记录不存在', U('members/index/index'));
        }
        if($detail['code'] != $code){
            $this->error('支付方式不正确', U('members/index/index'));
        }
		
		if($detail['need_pay'] <= 0){
            $this->error('支付金额有误', U('members/index/index'));
        }
		
		
        $member = D('Users')->find($this->uid);
        if($detail['is_paid']){
            $this->error('支付日志状态错误', U('members/index/index'));
        }
		
		if($code == 'money' && $member['money'] < $detail['need_pay']){
			$this->error('余额不足无法支付', U('members/money/money'));
		}
		
        if($code == 'integral' && $member['integral'] < $detail['need_pay']){
			$this->error('您积分账户不足无法支付', U('members/money/money'));
		}
		
		$intro = '【'.$name.'】支付'.round($detail['need_pay']/100,2).'元，支付ID('.$logs_id.')，原始订单ID：【'.$detail['order_id'].'】';
		if($code == 'money'){
			$member['money'] -= $detail['need_pay'];
			if(D('Users')->save(array('user_id' => $this->uid,'money' => $member['money']))){
				D('Usermoneylogs')->add(array(
					'user_id' => $this->uid, 
					'money' => - $detail['need_pay'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'intro' => $intro
				));
			}
		}
		
		if($code == 'integral'){
			$member['integral'] -= $detail['need_pay'];
			if(D('Users')->save(array('user_id' =>$this->uid, 'integral' => $member['integral']))){
				D('Userintegrallogs')->add(array(
					'user_id' => $this->uid, 
					'integral' => - $detail['need_pay'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'intro' => $intro
				));
			}
			
		}
		
		D('Paymentlogs')->where(array('log_id'=>$logs_id))->save(array('code'=>$code));//更新支付方式
		D('Payment')->logsPaid($logs_id);//回调函数
		
        if($detail['type'] == 'ele'){
			$this->ele_success('恭喜您支付成功啦', $detail);
        }elseif($detail['type'] == 'booking') {
			$this->booking_success('恭喜您预订支付成功啦', $detail);
        }elseif($detail['type'] == 'farm') {
			$this->farm_success('恭喜您农家乐支付成功啦', $detail);
        }elseif($detail['type'] == 'appoint') {
			$this->appoint_success('恭喜您家政支付成功啦', $detail);
        }elseif($detail['type'] == 'tuan'){
			$this->other_success('恭喜您抢购支付成功啦', $detail);
        }elseif($detail['type'] == 'edu'){
			 $this->edu_success('恭喜您购买课程支付成功啦', $detail);
        }else{
              $this->success('恭喜您付款成功', U('members/index/index'));
        }
    }
	
	
    //微信支付成功通知
    private function remainMoneyNotify($pay, $remain, $type = 0){
        //余额变动,微信通知
        $openid = D('Connect')->getFieldByUid($this->uid, 'open_id');
        $order_id = $order['order_id'];
        $user_name = D('User')->getFieldByUser_id($this->uid, 'nickname');
        if ($type) {
            $words = "您的账户于" . date('Y-m-d H:i:s') . "收入" . $pay . "元,余额" . $remain . "元";
        } else {
            $words = "您的账户于" . date('Y-m-d H:i:s') . "支出" . $pay . "元,余额" . $remain . "元";
        }
        if ($openid) {
            $template_id = D('Weixintmpl')->getFieldByTmpl_id(4, 'template_id');
            //余额变动模板
            $tmpl_data = array(
			'touser' => $openid, 
			'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/user', 
			'template_id' => $template_id, 
			'topcolor' => '#2FBDAA', 
			'data' => array(
				'first' => array('value' => '尊敬的用户,您的账户余额有变动！', 'color' => '#2FBDAA'), 
				'keynote1' => array('value' => $user_name, 'color' => '#2FBDAA'), 
				'keynote2' => array('value' => $words, 'color' => '#2FBDAA'), 
				'remark' => array('value' => '详情请登录您的用户中心了解', 'color' => '#2FBDAA')
			));
            D('Weixin')->tmplmesg($tmpl_data);
        }
    }
}