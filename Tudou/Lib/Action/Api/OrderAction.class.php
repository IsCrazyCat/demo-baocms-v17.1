<?php



class OrderAction extends CommonAction{

	//订单列表
	public function orderList(){
		$rd_session = $this->_get('rd_session');
        $user = $this->checkLogin($rd_session);
        $this->uid = $user['uid'];

		$s = I('aready', '', 'trim,intval');
        $Eleorder = D('Eleorder');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'closed' => 0);
        if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if(isset($_GET['st']) || isset($_POST['st'])) {
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
               $list[$k]['delivery_order'] = $delivery_order;
            }
        }
        $shops = D('Shop')->itemsByIds($shop_ids);
        //产品
        $products = D('Eleorderproduct')->where(array('order_id' => array('IN', $order_ids)))->select();
        $product_ids = array();
        foreach ($products as $val) {
            $product_ids[$val['product_id']] = $val['product_id'];
        }
        $eleproducts = D('Eleproduct')->itemsByIds($product_ids);
        foreach($products as $k=>$v){
        	foreach($eleproducts as $e){
        		if($v['product_id'] == $e['product_id']){
        			$products[$k]['product_name'] = $e['product_name'];
        			$photo = $e['photo'];
            		$products[$k]['photo'] = strpos($photo,"http")===false ?  __HOST__.$val['photo'] : $photo ;
        		}
        	}
        }
        foreach($list as $k=>$v){
        	$list[$k]['shop_name'] = $shops[$v['shop_id']]['shop_name'];
        	$list[$k]['total_price'] = round($v['total_price']/100,2);
        	$list[$k]['pay_time'] = date("Y-m-d H:i:s",$v['pay_time']);
        	foreach($products as $p){
        		if($v['order_id']==$p['order_id']){
        			$list[$k]['products'][] = $p;
        		}
        	}
        }	
      	$json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$list);
        $json_str = json_encode($json_arr);
        exit($json_str); 
       
	}
	
    //订单详情
    public function detail(){
        $rd_session = $this->_get('rd_session');
        $user = $this->checkLogin($rd_session);
        $this->uid = $user['uid'];
        $order_id = $this->_get('order_id');
        if (empty($order_id) || !($detail = D('Eleorder')->find($order_id))) {
            exit(json_encode(array('status'=>-1,'msg'=>'订单不存在','data'=>'')));
        }
        if ($detail['user_id'] != $this->uid) {
            exit(json_encode(array('status'=>-1,'msg'=>'不要操作别人的订单','data'=>'')));
        }
        $ele_products = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        $product_ids = array();
        foreach ($ele_products as $k => $val) {
            $product_ids[$val['product_id']] = $val['product_id'];
        }
       
        $products = D('Eleproduct')->itemsByIds($product_ids);
        
        foreach ($ele_products as $k => $v) {
            $ele_products[$k]['total_price'] = round($v['total_price']/100,2);
            $ele_products[$k]['product_name'] = $products[$v['product_id']]['product_name'];
        }
        $detail['tableware_price']=round($detail['tableware_price']/100,2);
        $detail['full_reduce_price']=round($detail['full_reduce_price']/100,2);
        $detail['logistics']=round($detail['logistics']/100,2);
        $detail['total_price']=round($detail['total_price']/100,2);
        $detail['need_pay']=round($detail['need_pay']/100,2);
        $detail['create_time'] = date('Y-m-d H:i:s',$detail['create_time']);

        $detail['product'] = $ele_products;
        $detail['ele'] = D('Ele')->where(array('shop_id' => $detail['shop_id']))->find();
        $detail['shop'] = D('Shop')->where(array('shop_id' => $detail['shop_id']))->find();
        $detail['delivery_order'] = D('DeliveryOrder')->where(array('type_order_id'=>$order_id,'type'=>1,'closed'=>0))->find();
        $detail['wait_time_minutes'] = D('Eleorder')->get_wait_time_minutes($order_id);
        //优惠多少？
        $detail['cut_money_total'] = $detail['total_price'] - $detail['need_pay'];
        $detail['addr'] = D('Useraddr')->find($detail['addr_id']);
        $json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$detail);
        $json_str = json_encode($json_arr);
        exit($json_str); 
    }

}
