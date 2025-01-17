<?php
class GoodsAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        if ($this->_CONFIG['operation']['mall'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
    public function index(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
        // 输出模板
    }
    public function goodsloaddata(){
        $Order = D('Order');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'user_id' => $this->uid);
		
		
		$aready = I('aready', '', 'trim,intval');
		if($aready == 999){
			$map['status'] = array('in',array(0,1,2,3,4,5,6,7,8));
		}elseif($aready == 0 || $aready == ''){
			$map['status'] = 0;
		}else{
			$map['status'] = $aready;
		}
		$this->assign('aready', $aready);
		
		
		
        $count = $Order->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Order->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = $addr_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
            $addr_ids[$val['addr_id']] = $val['addr_id'];
			if($delivery_order = D('DeliveryOrder')->where(array('type_order_id'=>$val['order_id'],'type'=>0,'closed'=>0))->find()){
				$comment = D('DeliveryComment')->where(array('order_id'=>$delivery_order['order_id'],'type'=>0,'delivery_id'=>$delivery_order['delivery_id']))->find();//配送订单点评
                $list[$key]['delivery_order'] = $delivery_order;
			    $list[$key]['comment'] = $comment;
            }
        }
		
		
		
        if (!empty($order_ids)) {
            $goods = D('Ordergoods')->where(array('order_id' => array('IN', $order_ids)))->select();
            $goods_ids = $shop_ids = array();
            foreach ($goods as $val) {
                $goods_ids[$val['goods_id']] = $val['goods_id'];
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $this->assign('goods', $goods);
            $this->assign('products', D('Goods')->itemsByIds($goods_ids));
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('addrs', D('Useraddr')->itemsByIds($addr_ids));
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Order')->getType());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
    public function detail($order_id){
        $order_id = (int) $order_id;
        if(empty($order_id) || !($detail = D('Order')->find($order_id))){
            $this->error('该订单不存在');
        }
        if($detail['user_id'] != $this->uid){
            $this->error('请不要操作他人的订单');
        }
        $order_goods = D('Ordergoods')->where(array('order_id' => $order_id))->select();
        $goods_ids = array();
        foreach ($order_goods as $k => $val){
            $goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        if(!empty($goods_ids)){
            $this->assign('goods', D('Goods')->itemsByIds($goods_ids));
        }
		$this->assign('data', $data = D('Logistics')->get_order_express($order_id));//查询清单物流
        $this->assign('ordergoods', $order_goods);
        $this->assign('types', D('Order')->getType());
        $this->assign('goodtypes', D('Ordergoods')->getType());
		
		$detail['shop'] = D('Shop')->where(array('shop_id' => $detail['shop_id']))->find();
		$detail['delivery_order'] = D('DeliveryOrder')->where(array('type_order_id'=>$order_id,'type'=>0,'closed'=>0))->find();
        $this->assign('detail', $detail);
        $this->display();
    }

	
	 public function queren($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Order');
            if (!($detial = $obj->find($order_id))) {
                $this->tuMsg('该订单不存在');
            }
            if ($detial['user_id'] != $this->uid) {
                $this->tuMsg('请不要操作他人的订单');
            }
			//检测配送状态
			$shop = D('Shop')->find($detial['shop_id']);
            if ($shop['is_goods_pei'] == 1) {
                $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 0))->find();
                if ($DeliveryOrder['status'] != 8) {
                    $this->tuMsg('配送员还未完成订单');
                }
            }
		    if($detial['is_daofu'] == 1) {
			   $into = '货到付款确认收货成功';
            }else{
				if ($detial['status'] != 2) {
                 	$this->tuMsg('该订单暂时不能确定收货');
				}
				$into = '确认收货成功';
			}
			if ($obj->save(array('order_id' => $order_id, 'status' => 3))) {
                D('Order')->overOrder($order_id); //确认到账入口
                $this->tuMsg($into, U('goods/index', array('aready' => 8)));
            }else{
				$this->tuMsg('操作失败');
			}
        } else {
            $this->tuMsg('请选择要确认收货的订单');
        }
    }
    //取消订单重做
    public function orderdel($order_id = 0){
        if ($order_id = (int) $order_id) {
            $obj = D('Order');
            if (!($detail = $obj->find($order_id))) {
                $this->tuMsg('该订单不存在');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->tuMsg('请不要操作他人的订单');
            }
			//检测配送状态
			$shop = D('Shop')->find($detail['shop_id']);
            if ($shop['is_ele_pei'] == 1) {
                $DeliveryOrder = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 0))->find();
                if ($DeliveryOrder['status'] == 2 || $DeliveryOrder['status'] == 8) {
                    $this->tuMsg('配送员都接单了无法取消订单');
                }else{
					D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' => 0))->setField('closed', 1);//没接单就关闭配送
				}
            }
			if ($detail['is_daofu'] == 1) {
				$into = '到付订单取消成功';
            }else{
				$into = '订单取消成功';
				if ($detail['status'] != 0) {
					$this->tuMsg('该订单暂时不能取消');
				}
			}
            if ($obj->save(array('order_id' => $order_id, 'closed' => 1))) {
				$obj-> del_order_goods_closed($order_id);//更新状态
				$obj-> del_goods_num($order_id);//取消后加库存
                if ($detail['use_integral']) {
                    D('Users')->addIntegral($detail['user_id'], $detail['can_use_integral'], '取消商城购物，订单号：' . $detail['order_id'] . '积分退还');
                }
				D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 2,$status = 11);
			    D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 2,$status = 11);
				$this->tuMsg($into, U('goods/index', array('aready' => 1)));
            }else{
				$this->tuMsg('操作失败');
			}
        } else {
            $this->tuMsg('请选择要取消的订单');
        }
    }
	
	public function refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Order = D('Order');
        $goods_order = $Order->where('order_id =' . $order_id)->find();
		
		//检测配送状态
		if(false == D('Order')->orderDelivery($order_id,$type ='4')){
			$this->tuMsg(D('Order')->getError());
		}
		
        if (!$goods_order) {
            $this->tuMsg('错误');
        } else {
		    if ($goods_order['is_daofu'] == 1) {
                if ($goods_order['status'] != 0) {
					$this->tuMsg('订单状态有误');
				 }
            }else{
				 if ($goods_order['status'] != 1) {
					$this->tuMsg('当前订单状态不正确');
				 }
			}
            if ($goods_order['user_id'] != $this->uid) {
                $this->tuMsg('请不要操作他人的订单');
            }
            $goods_order = $Order->where('order_id =' . $order_id)->setField('status', 4);
			D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 2,$status = 3);
			D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 2,$status = 3);
            $this->tuMsg('申请退款成功', U('goods/index', array('aready' => 4)));
        }		
		
    }
	
    public function cancel_refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Order = D('Order');
        $goods_order = $Order->where('order_id =' . $order_id)->find();
		
		//检测配送状态
		if(false == D('Order')->orderDelivery($order_id,$type ='5')){
			$this->tuMsg(D('Order')->getError());
		}
		
		  
        if (!$goods_order) {
            $this->tuMsg('错误');
        } else {
            if ($goods_order['user_id'] != $this->uid) {
                $this->tuMsg('请不要操作他人的订单');
            }
			if ($goods_order['is_daofu'] == 1) {
                $goods_order = $Order->where('order_id =' . $order_id)->setField('status', 0);
            }else{
				$goods_order = $Order->where('order_id =' . $order_id)->setField('status', 1);
			}
			$this->tuMsg('取消退款成功！',U('goods/index', array('aready' => 2)));
        }
    }
	
    public function dianping($order_id){
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D("Order")->find($order_id))) {
            $this->error("该订单不存在");
        }
        if ($detail['user_id'] != $this->uid) {
            $this->tuMsg("请不要操作他人的订单");
        }
        if ($detail['is_dianping'] != 0) {
            $this->tuMsg("您已经点评过了");
        }
        $goodss = D('Ordergoods')->where('order_id =' . $detail['order_id'])->find();
        $goods_id = $goodss['goods_id'];
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post("data", FALSE), array("score", "cost", "contents"));
            $data['user_id'] = $this->uid;
            $data['order_id'] = $detail['order_id'];
            $data['shop_id'] = $detail['shop_id'];
            $data['goods_id'] = $goods_id;
            $data['score'] = (int) $data['score'];
            if ($data['score'] <= 0 || 5 < $data['score']) {
                $this->tuMsg("请选择评分");
            }
            $data['contents'] = htmlspecialchars($data['contents']);
            if (empty($data['contents'])) {
                $this->tuMsg("不说点什么么");
            }
            $data['create_time'] = NOW_TIME;
            $data_mall_dianping = $this->_CONFIG['mobile']['data_mall_dianping'];
            $data['show_date'] = date('Y-m-d', NOW_TIME + $data_mall_dianping * 86400);
            $data['create_ip'] = get_client_ip();
            $obj = D("Goodsdianping");
            if ($dianping_id = $obj->add($data)) {
                $photos = $this->_post("photos", FALSE);
                $local = array();
                foreach ($photos as $val) {
                    if (isimage($val)) {
                        $local[] = $val;
                    }
                }
                if (!empty($local)) {
                    D("Goodsdianpingpics")->upload($order_id, $local, $goods_id);
                }
                D("Order")->save(array("order_id" => $order_id, "is_dianping" => 1));
                D("Shop")->updateCount($detail['shop_id'], "score_num");
                D("Users")->updateCount($this->uid, "ping_num");
                D("Users")->prestige($this->uid, "dianping");
                $this->tuMsg("评价成功", U("user/goods/index/"));
            }
            $this->tuMsg("操作失败！");
        } else {
            $this->assign("detail", $detail);
            $goods = D('Goods')->where('goods_id =' . $goods_id)->find();
            $this->assign("goods", $goods);
            $this->display();
        }
    }
}