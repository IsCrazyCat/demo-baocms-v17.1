<?php
class EleorderAction extends CommonAction{
	
	protected function _initialize(){
        parent::_initialize();
		if(!$this->_CONFIG['operation']['ele']){
            $this->error('此功能已关闭');die;
        }
		D('Eleorder')->past_due_ele_order($shop_id = '0',$this->uid);//删除过期订单
    }
	
	
    public function index(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
    public function loading(){
        $Eleorder = D('Eleorder');
		$map = array('user_id' => $this->uid,'closed'=>'0'); 
        import('ORG.Util.Page');
		
		
        $aready = I('aready', '', 'trim,intval');
		if($aready == 999){
			$map['status'] = array('in',array(0,1,2,3,4,5,6,7,8));
		}elseif($aready == 0 || $aready == ''){
			$map['status'] = 0;
		}else{
			$map['status'] = $aready;
		}
		$this->assign('aready', $aready);
      
		
		
		
        $count = $Eleorder->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Eleorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			if($delivery_order = D('DeliveryOrder')->where(array('type_order_id'=>$val['order_id'],'type'=>1,'closed'=>0))->find()){
				$comment = D('DeliveryComment')->where(array('order_id'=>$delivery_order['order_id'],'type'=>1,'delivery_id'=>$delivery_order['delivery_id']))->find();//配送订单点评
                $list[$k]['delivery_order'] = $delivery_order;
			    $list[$k]['comment'] = $comment;
            }
        }
        $this->assign('shopss', D('Shop')->itemsByIds($shop_ids));
        if (!empty($order_ids)) {
            $products = D('Eleorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $product_ids = $shop_ids = array();
            foreach ($products as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $this->assign('products', $products);
            $this->assign('eleproducts', D('Eleproduct')->itemsByIds($product_ids));
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('cfg', D('Eleorder')->getCfg());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function detail($order_id){
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Eleorder')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error('请不要操作他人的订单');
        }
        $ele_products = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        $product_ids = array();
        foreach ($ele_products as $k => $val) {
            $product_ids[$val['product_id']] = $val['product_id'];
        }
        if (!empty($product_ids)) {
            $this->assign('products', D('Eleproduct')->itemsByIds($product_ids));
        }
        $detail['ele'] = D('Ele')->where(array('shop_id' => $detail['shop_id']))->find();
        $detail['shop'] = D('Shop')->where(array('shop_id' => $detail['shop_id']))->find();
		$detail['delivery_order'] = D('DeliveryOrder')->where(array('type_order_id'=>$order_id,'type'=>1,'closed'=>0))->find();
        $this->assign('wait_time', D('Eleorder')->get_wait_time($order_id));
		$this->assign('wait_time_minutes', D('Eleorder')->get_wait_time_minutes($order_id));
        $this->assign('eleproducts', $ele_products);
        $this->assign('addr', D('Useraddr')->find($detail['addr_id']));
        $this->assign('cfg', D('Eleorder')->getCfg());
        $this->assign('detail', $detail);
        $this->display();
    }
	//新版配送状态
	public function state($order_id){
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Eleorder')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error('请不要操作他人的订单');
        }
        
        $product_ids = array();
        foreach ($ele_products as $k => $val) {
            $product_ids[$val['product_id']] = $val['product_id'];
        }
        if (!empty($product_ids)) {
            $this->assign('products', D('Eleproduct')->itemsByIds($product_ids));
        }
        $detail['ele'] = D('Ele')->where(array('shop_id' => $detail['shop_id']))->find();
        $detail['shop'] = D('Shop')->where(array('shop_id' => $detail['shop_id']))->find();
		
		$detail['DeliveryOrder'] = D('DeliveryOrder')->where(array('type_order_id'=>$order_id,'type'=>1,'closed'=>0))->find();
		if($detail['DeliveryOrder']){
			$this->assign('status',1);//1代表配送员
		}else{
			$this->assign('status',2);//2代表商家配送
		}
        $this->assign('eleproducts', $ele_products = D('Eleorderproduct')->where(array('order_id' => $order_id))->select());;
        $this->assign('addr', D('Useraddr')->find($detail['addr_id']));
        $this->assign('cfg', D('Eleorder')->getCfg());
        $this->assign('detail', $detail);
        $this->display();
    }
    //确认订单
    public function yes($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            if (!($detial = D('Eleorder')->find($order_id))) {
                $this->tuMsg('您确认收货的订单不存在');
            }
            if ($detial['user_id'] != $this->uid) {
                $this->tuMsg('请不要操作别人的订单');
            }
            $shop = D('Shop')->find($detial['shop_id']);
            if ($shop['is_ele_pei'] == 1) {
                $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->find();
                if ($DeliveryOrder['status'] == 2) {
                    $this->tuMsg('配送员还未完成订单');
                }
            } else {
                //不走配送
                if ($detial['status'] != 2) {
                    $this->tuMsg('当前状态不能确认收货');
                }
            }
            $obj = D('Eleorder');
            D('Eleorder')->overOrder($order_id);
            //确认资金到账
            $obj->save(array('order_id' => $order_id, 'status' => 8,'end_time' => NOW_TIME));
            //更改为已完成
            $this->tuMsg('确认收货成功', U('eleorder/index', array('s' => 1)));
        } else {
            $this->tuMsg('请选择要确认收货的订单');
        }
    }
	//最新删除订单
    public function del(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Eleorder = D('Eleorder');
        $detail = $Eleorder->where('order_id =' . $order_id)->find();
        $Shop = D('Shop')->find($f['shop_id']);
        if ($Shop['is_ele_pei'] == 1) {
            $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->find();
            if ($DeliveryOrder['status'] == 2) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '配送员已经抢单，无法删除'));
            } elseif ($DeliveryOrder['status'] == 8) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '配送员都已经确认了，无法删除'));
            }
        }
        if (!$detail) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '错误'));
        } else {
            if ($detail['user_id'] != $this->uid) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '非法操作'));
            }
            if ($detial['status'] != 0 && $detial['status'] != 8 && $detial['status'] != 4) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '当前状态不允许取消订单'));
            }
            $Eleorder->where('order_id =' . $order_id)->setField('closed', 1);
            $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->setField('closed', 1);
            D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 1,$status = 11);
			D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 1,$status = 11);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除订单成功', U('eleorder/index')));
        }
    }
	//最新封装退款
    public function eletui(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Eleorder = D('Eleorder');
		if(!$detail = $Eleorder->where('order_id =' . $order_id)->find()) {
           $this->tuMsg('错误');
        }elseif($detail['user_id'] != $this->uid) {
           $this->tuMsg('请不要操作他人的订单');
        }elseif($detail['status'] != 1) {
           $this->tuMsg('当前订单状态不正确');
        }else{
			if(false == $Eleorder-> ele_user_refund($order_id)) {//更新什么什么的
				$this->tuMsg($Eleorder->getError());
			}else{
				$this->tuMsg('申请退款成功', U('eleorder/index', array('s' => 3)));
			}
		}
    }
    //催单功能
    public function reminder(){
        $order_id = I('order_id', 0, 'trim,intval');
        if (!($detail = D('Eleorder')->find($order_id))) {
            $this->tuMsg('当前订单不存在');
        }
        if ($detail['status'] != 2) {
            $this->tuMsg('状态不正确');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->tuMsg('请不要操作他人的订单');
        }
        $wait_time = time() - $detail['pay_time'];
        if ($wait_time <= 45 * 60) {
            $this->tuMsg('付款后45分钟才能催单哦');
        }
        $reminder = D('Elereminder')->where(array('order_id' => $detail['order_id']))->find();
        if ($reminder = D('Elereminder')->where(array('order_id' => $detail['order_id']))->find()) {
            $this->tuMsg('请不要重复催单');
        }
        $data['order_id'] = $order_id;
        $data['user_id'] = $this->uid;
        $data['shop_id'] = $detail['shop_id'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        if ($reminder_id = D('Elereminder')->add($data)) {
            D('Sms')->sms_ele_reminder_shop($order_id);
            $this->tuMsg('催单成功', U('eleorder/detail', array('order_id' => $detail['order_id'])));
        }
    }
	//最新取消外卖订单退款
    public function qx(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Eleorder = D('Eleorder');
        $detail = $Eleorder->where('order_id =' . $order_id)->find();
        $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 1))->setField('closed', 0);
        if (!$detail) {
            $this->tuMsg('错误');
        } else {
            if ($detail['user_id'] != $this->uid) {
                $this->tuMsg('请不要操作他人的订单');
            }
            $Eleorder->where('order_id =' . $order_id)->setField('status', 1);
            $this->tuMsg('取消退款成功', U('eleorder/index'));
        }
    }
    public function dianping($order_id){
        $order_id = (int) $order_id;
        if (!($detail = D("Eleorder")->find($order_id))) {
            $this->error("没有该订单");
        } else {
            if ($detail['user_id'] != $this->uid) {
                $this->error("不要评价别人的订餐订单");
                exit;
            }
        }
        if (D("Eledianping")->check($order_id, $this->uid)) {
            $this->error("已经评价过了");
        }
        if ($this->_Post()) {
            $data = $this->checkFields($this->_post('data', FALSE), array('score', 'speed', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['shop_id'] = $detail['shop_id'];
            $data['order_id'] = $order_id;
            $data['score'] = (int) $data['score'];
            if (empty($data['score'])) {
                $this->tuMsg("评分不能为空");
            }
            if (5 < $data['score'] || $data['score'] < 1) {
                $this->tuMsg("评分为1-5之间的数字");
            }
            $data['cost'] = (int) $data['cost'];
            if (empty($data['cost'])) {
                $this->tuMsg("平均消费金额不能为空");
            }
            $data['speed'] = (int) $data['speed'];
            if (empty($data['speed'])) {
                $this->tuMsg("送餐时间不能为空");
            }
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->tuMsg("评价内容不能为空");
            }
            if ($words = D("Sensitive")->checkWords($data['contents'])) {
                $this->tuMsg("评价内容含有敏感词：" . $words);
            }
            $data_waimai_dianping = $this->_CONFIG['mobile']['data_waimai_dianping'];
            $data['show_date'] = date('Y-m-d', NOW_TIME + $data_waimai_dianping * 86400);
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            if (D("Eledianping")->add($data)) {
                $photos = $this->_post("photos", FALSE);
                $local = array();
                foreach ($photos as $val) {
                    if (isimage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D("Eledianpingpics")->upload($order_id, $local);
                }
                D("Users")->updateCount($this->uid, "ping_num");
                D("Eleorder")->updateCount($order_id, "is_dianping");
                $this->tuMsg("恭喜您点评成功!", u("eleorder/index"));
            }
            $this->tuMsg("点评失败！");
        } else {
            $this->assign("detail", $detail);
            $details = D("Shop")->find($detail['shop_id']);
            $this->assign("details", $details);
            $this->assign("order_id", $order_id);
            $this->display();
        }
    }
}