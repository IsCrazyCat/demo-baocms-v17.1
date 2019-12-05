<?php
class MarketorderAction extends CommonAction{
    protected $status = 0;
    protected $market;
	
    public function _initialize(){
        parent::_initialize();
        $getMarketCate = D('Market')->getMarketCate();
        $this->assign('getMarketCate', $getMarketCate);
        $this->market = D('Market')->find($this->shop_id);
        if(!empty($this->market) && $this->market['audit'] == 0){
            $this->error('亲，您的申请正在审核中');
        }
        if(empty($this->market) && ACTION_NAME != 'apply') {
            $this->error('您还没有入住菜市场频道', U('market/apply'));
        }
        $this->assign('market', $this->market);
		$this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
		$this->assign('types', D('Marketorder')->getCfg());
    }
	
    public function index(){
        $this->status = 1;
        $this->showdata();
        $this->display();
    }
    public function wait(){
        $this->status = 2;
        $this->showdata();
        $this->display();
    }
	public function wait_refunded(){
        $this->status = 3;
        $this->showdata();
        $this->display();
    }
	public function refunded(){
        $this->status = 4;
        $this->showdata();
        $this->display();
    }
    public function over(){
        $this->status = 8;
        $this->showdata();
        $this->display();
    }
    public function whole(){
        $obj = D('Marketorder');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $this->shop_id);
        if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        }else{
            if($bg_date = $this->_param('bg_date', 'htmlspecialchars')){
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if($end_date = $this->_param('end_date', 'htmlspecialchars')){
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
        }
        if (!empty($order_ids)) {
            $goods = D('Marketorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $goods_ids = array();
            foreach ($goods as $val) {
                $goods_ids[$val['product_id']] = $val['product_id'];
            }
            $this->assign('goods', $goods);
            $this->assign('products', D('Marketproduct')->itemsByIds($goods_ids));
        }
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function count(){
        $dvo = D('DeliveryOrder');
        $bg_date = strtotime(I('bg_date', 0, 'trim'));
        $end_date = strtotime(I('end_date', 0, 'trim'));
        $this->assign('btime', $bg_date);
        $this->assign('etime', $end_date);
        if ($bg_date && $end_date) {
            $pre_btime = date('Y-m-d H:i:s', $bg_date);
            $pre_etime = date('Y-m-d H:i:s', $end_date);
            $this->assign('pre_btime', $pre_btime);
            $this->assign('pre_etime', $pre_etime);
        }
        $map = array('shop_id' => $this->shop_id, 'type' => 3);
        if ($bg_date && $end_date) {
            $map['create_time'] = array('between', array($bg_date, $end_date));
        }
        import('ORG.Util.Page');
        $count = $dvo->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $dvo->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('addrs', D('Delivery')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
    function delivery_count(){
        $delivery_id = I('did', 0, 'intval,trim');
        $btime = I('btime', 0, 'trim');
        $etime = I('etime', 0, 'trim');
        $map = array();
        if($btime && $etime){
            $map['create_time'] = array('between', array($btime, $etime));
        }
        if(!$delivery_id || !$this->shop_id){
            $this->ajaxReturn(array('status' => 'error', 'message' => '错误'));
        }else{
            $map['delivery_id'] = $delivery_id;
            $map['shop_id'] = $this->shop_id;
            $map['type'] = 3;
            $count = D('DeliveryOrder')->where($map)->count();
            if($count){
                $this->ajaxReturn(array('status' => 'success', 'count' => $count));
            }else{
                $this->ajaxReturn(array('status' => 'error', 'message' => '错误'));
            }
        }
    }
	
	
    private function showdata(){
        $obj = D('Marketorder');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $this->shop_id, 'status' => $this->status);
        if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        }else{
            if($bg_date = $this->_param('bg_date', 'htmlspecialchars')){
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if($end_date = $this->_param('end_date', 'htmlspecialchars')){
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
        }
        if (!empty($order_ids)) {
            $goods = D('Marketorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
            $goods_ids = array();
            foreach ($goods as $val) {
                $goods_ids[$val['product_id']] = $val['product_id'];
            }
            $this->assign('goods', $goods);
            $this->assign('products', D('Marketproduct')->itemsByIds($goods_ids));
        }
		
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
    }
	
	
	//确认发货
    public function queren($order_id){
        $order_id = (int) $order_id;
        if(!($detail = D('Marketorder')->find($order_id))){
            $this->tuError('没有该订单');
        }
        if($detail['shop_id'] != $this->shop_id){
            $this->tuError('您无权管理该商家');
        }
        if($detail['status'] != 1){
            $this->tuError('该订单状态不正确');
        }
		
		if(!($shop = D('Shop')->find($detail['shop_id']))){
            $this->tuError('没有该商家');
        }
		if($shop['is_market_pei'] == 1){
			D('Marketorder')->market_delivery_order($order_id);//接单时候给配送
		}else{
			D('Marketorder')->save(array('order_id' => $order_id, 'status' => 2, 'orders_time' => NOW_TIME));
		}
		
		D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 2);
		D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 2);
        $this->tuSuccess('菜市场订单已确认', U('marketorder/index'));
    }
	
	
	
    //确认完成
    public function send($order_id){
        $order_id = (int) $order_id;
        $config = D('Setting')->fetchAll();
        $h = isset($config['site']['market']) ? (int) $config['site']['market'] : 6;
        $t = NOW_TIME - $h * 3600;
        if(!($detail = D('Marketorder')->find($order_id))) {
            $this->tuError('没有该订单');
        }
        if($detail['shop_id'] != $this->shop_id){
            $this->tuError('您无权管理该商家');
        }
        $shop = D('Shop')->find($detial['shop_id']);
        if($shop['is_market_pei'] == 1){
            $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id'=>$order_id,'type' =>3))->find();
            if(!empty($DeliveryOrder)){
                $this->tuError('您开通了配送员配货，无权管理');
            }
        }else{
            if($detail['create_time'] < $t && $detail['status'] == 2){
                D('Marketorder')->overOrder($order_id);
                $this->tuSuccess('确认完成，资金已经结算到账户', U('marketorder/wait'));
            }else{
                $this->tuError('操作失败');
            }
        }
        
    }
	
	//退款
	public function refund($order_id = 0){
            $order_id = (int)$order_id;
            $detail = D('Marketorder')->find($order_id);
			if($detail['status'] != 3){
                $this->tuError('菜市场状态不正确');               
            }
			if ($detail['shop_id'] != $this->shop_id) {
				$this->tuError('您无权管理该商家');
			}
            if($detail['status'] == 3){
                if(D('Marketorder')->save(array('order_id'=>$order_id,'status'=>4))){ //将内容变成
                    $obj = D('Users');
                    if($detail['need_pay'] >0){
						D('Sms')->marketorder_refund_user($order_id); //菜市场退款短信通知用户
                        $obj->addMoney($detail['user_id'],$detail['need_pay'],'菜市场退款,订单号：'.$order_id);
						D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 4);
						D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 4);
						$this->tuSuccess('退款成功', U('marketorder/refunded'));
                    }
                }else{
					$this->tuError('未知错误');	
				}              
            }else{
				$this->tuError('状态不正确');	
			}  
    }
	

	  public function detail(){
        $order_id = I('order_id', '', 'intval,trim');
        if(!($order = D('MarketOrder')->find($order_id))){
            $this->error('错误');
        }else{
            $op = D('MarketOrderProduct')->where('order_id =' . $order['order_id'])->select();
            if($op){
                $ids = array();
                foreach ($op as $k => $v){
                    $ids[$v['product_id']] = $v['product_id'];
                }
                $ep = D('MarketProduct')->where(array('product_id' => array('in', $ids)))->select();
                $ep2 = array();
                foreach ($ep as $kk => $vv) {
                    $ep2[$vv['product_id']] = $vv;
                }
                $this->assign('ep', $ep2);
                $this->assign('op', $op);
                $addr = D('UserAddr')->find($order['addr_id']);
                $this->assign('addr', $addr);
                $do = D('DeliveryOrder')->where(array('type' =>3, 'type_order_id' => $order['order_id']))->find();
                if($do){
                    if($do['delivery_id'] > 0){
                        $delivery = D('Delivery')->find($do['delivery_id']);
                        $this->assign('delivery', $delivery);
                    }
                    $this->assign('do', $do);
                }
            }
			$this->assign('users', D('Users')->find($order['user_id']));
            $this->assign('order', $order);
            $this->display();
        }
    }
}