<?php
class StoreAction extends CommonAction{
	
	protected function _initialize(){
        parent::_initialize();
		if(!$this->_CONFIG['operation']['store']){
            $this->error('此功能已关闭');die;
        }
		D('Storeorder')->past_due_store_order($shop_id = '0',$this->uid);//删除过期订单
    }
	
	
    public function index(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
	
	
    public function loading(){
        $obj = D('Storeorder');
        import('ORG.Util.Page');
		
		$map = array('user_id' => $this->uid, 'closed' => 0);
		
        $aready = I('aready', '', 'trim,intval');
		if($aready == 999){
			$map['status'] = array('in',array(0,1,2,3,4,5,6,7,8));
		}elseif($aready == 0 || $aready == ''){
			$map['status'] = 0;
		}else{
			$map['status'] = $aready;
		}
		$this->assign('aready', $aready);
		
		
		
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			if($delivery_order = D('DeliveryOrder')->where(array('type_order_id'=>$val['order_id'],'type'=>4,'closed'=>0))->find()){
               $list[$k]['delivery_order'] = $delivery_order;
            }
        }
        $this->assign('shopss', D('Shop')->itemsByIds($shop_ids));
        if(!empty($order_ids)){
            $products = D('Storeorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $product_ids = $shop_ids = array();
            foreach ($products as $val) {
                $product_ids[$val['product_id']] = $val['product_id'];
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $this->assign('products', $products);
            $this->assign('storeproducts', D('Storeproduct')->itemsByIds($product_ids));
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('cfg', D('Storeorder')->getCfg());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
    public function detail($order_id){
        $order_id = (int) $order_id;
        if(empty($order_id) || !($detail = D('Storeorder')->find($order_id))){
            $this->error('该订单不存在');
        }
        if($detail['user_id'] != $this->uid){
            $this->error('请不要操作他人的订单');
        }
		
        $store_products = D('Storeorderproduct')->where(array('order_id' => $order_id))->select();
        $product_ids = array();
        foreach($store_products as $k => $val){
            $product_ids[$val['product_id']] = $val['product_id'];
        }
        if(!empty($product_ids)){
            $this->assign('products', D('Storeproduct')->itemsByIds($product_ids));
        }
        $detail['store'] = D('Store')->where(array('shop_id' => $detail['shop_id']))->find();
        $detail['shop'] = D('Shop')->where(array('shop_id' => $detail['shop_id']))->find();
		$detail['delivery_order'] = D('DeliveryOrder')->where(array('type_order_id'=>$order_id,'type'=>4,'closed'=>0))->find();
        $this->assign('storeproducts', $store_products);
        $this->assign('addr', D('Useraddr')->find($detail['addr_id']));
        $this->assign('cfg', D('Storeorder')->getCfg());
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
	//新版配送状态
	public function state($order_id){
        $order_id = (int) $order_id;
        if(empty($order_id) || !($detail = D('Storeorder')->find($order_id))){
            $this->error('该订单不存在');
        }
        if($detail['user_id'] != $this->uid){
            $this->error('请不要操作他人的订单');
        }
        
        $product_ids = array();
        foreach ($store_products as $k => $val){
            $product_ids[$val['product_id']] = $val['product_id'];
        }
        if(!empty($product_ids)){
            $this->assign('products', D('Storeproduct')->itemsByIds($product_ids));
        }
        $detail['store'] = D('Store')->where(array('shop_id' => $detail['shop_id']))->find();
        $detail['shop'] = D('Shop')->where(array('shop_id' => $detail['shop_id']))->find();
		
		$detail['DeliveryOrder'] = D('DeliveryOrder')->where(array('type_order_id'=>$order_id,'type'=>4,'closed'=>0))->find();
		if($detail['DeliveryOrder']){
			$this->assign('status',1);//1代表配送员
		}else{
			$this->assign('status',2);//2代表商家配送
		}
        $this->assign('storeproducts', $store_products = D('Storeorderproduct')->where(array('order_id' => $order_id))->select());;
        $this->assign('addr', D('Useraddr')->find($detail['addr_id']));
        $this->assign('cfg', D('Storeorder')->getCfg());
        $this->assign('detail', $detail);
        $this->display();
    }
	
    //确认订单
    public function yes($order_id = 0){
        if(is_numeric($order_id) && ($order_id = (int) $order_id)){
            if(!($detial = D('Storeorder')->find($order_id))){
                $this->tuMsg('您确认收货的订单不存在');
            }
			
            if($detial['user_id'] != $this->uid){
                $this->tuMsg('请不要操作别人的订单');
            }
			
            $shop = D('Shop')->find($detial['shop_id']);
            if($shop['is_store_pei'] == 1){
                $do = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 4))->find();
                if($do['status'] == 2){
                    $this->tuMsg('配送员还未完成订单');
                }
            }else{
                if($detial['status'] != 2) {
                    $this->tuMsg('当前状态不能确认收货');
                }
            }
            D('Storeorder')->overOrder($order_id);
            D('Storeorder')->save(array('order_id' => $order_id, 'status' => 8,'end_time' => NOW_TIME));
            $this->tuMsg('确认收货成功', U('store/index',array('aready' =>8)));
        }else{
            $this->tuMsg('请选择要确认收货的订单');
        }
    }
	
	
	//最新删除订单
    public function del(){
        $order_id = I('order_id', 0, 'trim,intval');
        $obj = D('Storeorder');
        $detail = $obj->where('order_id =' . $order_id)->find();
        $Shop = D('Shop')->find($f['shop_id']);
		
        if($Shop['is_store_pei'] == 1){
            $do = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' =>4))->find();
            if($do['status'] == 2){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '配送员已经抢单，无法删除'));
            }elseif($do['status'] == 8){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '配送员都已经确认了，无法删除'));
            }
        }
		
        if(!$detail){
            $this->ajaxReturn(array('status' => 'error', 'msg' => '错误'));
        }else{
            if($detail['user_id'] != $this->uid){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '非法操作用'));
            }
            if($detial['status'] != 0 && $detial['status'] != 8 && $detial['status'] != 4){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '当前状态不允许取消订单'));
            }
            $obj->where('order_id =' . $order_id)->setField('closed', 1);
            $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' =>4))->setField('closed', 1);
            D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 10,$status = 11);
			D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 10,$status = 11);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除订单成功', U('store/index')));
        }
    }
	
	
	//最新封装退款
    public function storetui(){
        $order_id = I('order_id', 0, 'trim,intval');
        $obj = D('Storeorder');
		if(!$detail = $obj->where('order_id =' . $order_id)->find()){
           $this->tuMsg('错误');
        }elseif($detail['user_id'] != $this->uid){
           $this->tuMsg('请不要操作他人的订单');
        }elseif($detail['status'] != 1) {
           $this->tuMsg('当前订单状态不正确1');
        }else{
			if(false == $obj->store_user_refund($order_id)){
				$this->tuMsg($obj->getError());
			}else{
				$this->tuMsg('申请退款成功', U('store/index',array('aready' =>3)));
			}
		}
    }
   
	//最新取消外卖订单退款
    public function qx(){
        $order_id = I('order_id', 0, 'trim,intval');
        $obj = D('Storeorder');
        $detail = $obj->where('order_id =' . $order_id)->find();
        $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 4))->setField('closed', 0);
        if(!$detail){
            $this->tuMsg('错误');
        }else{
            if($detail['user_id'] != $this->uid){
                $this->tuMsg('请不要操作他人的订单');
            }
            $obj->where('order_id =' . $order_id)->setField('status', 1);
            $this->tuMsg('取消退款成功', U('store/index',array('aready' =>1)));
        }
    }
	
	
    public function dianping($order_id){
        $order_id = (int) $order_id;
        if(!($detail = D('Storeorder')->find($order_id))){
            $this->error('没有该订单');
        }else{
            if($detail['user_id'] != $this->uid){
                $this->error('不要评价别人的订餐订单');
                exit;
            }
        }
        if(D('Storedianping')->check($order_id, $this->uid)){
            $this->error('已经评价过了');
        }
        if($this->_Post()){
            $data = $this->checkFields($this->_post('data', FALSE), array('score', 'speed', 'cost', 'contents'));
            $data['user_id'] = $this->uid;
            $data['shop_id'] = $detail['shop_id'];
            $data['order_id'] = $order_id;
            $data['score'] = (int) $data['score'];
            if(empty($data['score'])){
                $this->tuMsg('评分不能为空');
            }
            if(5 < $data['score'] || $data['score'] < 1){
                $this->tuMsg('评分为1-5之间的数字');
            }
            $data['cost'] = (int) $data['cost'];
            if(empty($data['cost'])){
                $this->tuMsg('平均消费金额不能为空');
            }
            $data['speed'] = (int) $data['speed'];
            if(empty($data['speed'])){
                $this->tuMsg('送餐时间不能为空');
            }
            $data['contents'] = htmlspecialchars($data['contents']);
            if(empty($data['contents'])){
                $this->tuMsg('评价内容不能为空');
            }
            if($words = D('Sensitive')->checkWords($data['contents'])){
                $this->tuMsg('评价内容含有敏感词：' . $words);
            }
            $data['show_date'] = date('Y-m-d', NOW_TIME);
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            if (D('Storedianping')->add($data)) {
                $photos = $this->_post('photos', FALSE);
                $local = array();
                foreach ($photos as $val){
                    if(isimage($val)){
                        $local[] = $val;
                    }
                }
                if(!empty($local)){
                    D('Storedianpingpics')->upload($order_id, $local);
                }
                D('Users')->updateCount($this->uid, 'ping_num');
                D('Storeorder')->updateCount($order_id, 'is_dianping');
                $this->tuMsg('恭喜您点评成功', U('store/index',array('aready' =>8)));
            }
            $this->tuMsg('点评失败');
        }else{
            $this->assign('detail', $detail);
            $details = D('Shop')->find($detail['shop_id']);
            $this->assign('details', $details);
            $this->assign('order_id', $order_id);
            $this->display();
        }
    }
}