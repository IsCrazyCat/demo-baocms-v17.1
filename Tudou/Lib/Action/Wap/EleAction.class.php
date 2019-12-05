<?php
class EleAction extends CommonAction{
    protected $cart = array();
    public function _initialize(){
        parent::_initialize();
        $this->cart = $this->getcart();
        $this->assign('cartnum', (int) array_sum($this->cart));
        $cate = D('Ele')->getEleCate();
        $this->assign('elecate', $cate);
		if(empty($this->_CONFIG['operation']['ele'])){
			$this->error('外卖功能已关闭');die;
		}
    }
    public function getcart(){
        $shop_id = (int) $this->_param('shop_id');
        $cart = (array) json_decode($_COOKIE['ele']);
        $carts = array();
        foreach ($cart as $kk => $vv) {
            foreach ($vv as $key => $v) {
                $carts[$kk][$key] = (array) $v;
            }
        }
        $ids = $nums = array();
        foreach ($carts[$shop_id] as $k => $val) {
            $ids[$val['product_id']] = $val['product_id'];
            $nums[$val['product_id']] = $val['num'];
        }
        $eleproducts = D('Eleproduct')->itemsByIds($ids);
        foreach ($eleproducts as $k => $val) {
            $eleproducts[$k]['cart_num'] = $nums[$val['product_id']];
            $eleproducts[$k]['total_price'] = $nums[$val['product_id']] * $val['price'];
        }
        return $eleproducts;
    }
  
  
   public function loadcart(){
        if($goods = cookie('ele')) {
            $total = array('num' => 0, 'money' => 0);
            $goods = (array) json_decode($goods);
            $ids = array();
            foreach ($goods as $shop_id => $items) {
                foreach ($items as $k2 => $item) {
                    $item = (array) $item;
                    $total['num'] += $item['num'];
                    $total['money'] += $item['price'] * $item['num'];
                    $ids[] = $item['product_id'];
                    $product_item_num[$item['product_id']] = $item['num'];
                }
            }
            $ids = implode(',', $ids);
            $products = D('Eleproduct')->where('closed=0')->select($ids);
            foreach ($products as $k => $val) {
                $products[$k]['cart_num'] = $product_item_num[$val['product_id']];
            }
            $this->assign('cartgoods', $products);
			$this->display();
        }
    }
	
	
    public function index(){
        $linkArr = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $linkArr['keyword'] = $keyword;
        $cate = $this->_param('cate', 'htmlspecialchars');
        $this->assign('cate', $cate);
        $linkArr['cate'] = $cate;
        $order = $this->_param('order', 'htmlspecialchars');
        $this->assign('order', $order);
        $linkArr['order'] = $order;
        $area = (int) $this->_param('area');
        $this->assign('area', $area);
        $linkArr['area'] = $area;
        $business = (int) $this->_param('business');
        $this->assign('business', $business);
        $linkArr['business'] = $business;
        $this->assign('nextpage', LinkTo('ele/loaddata', $linkArr, array('t' => NOW_TIME, 'p' => '0000')));
        $this->assign('linkArr', $linkArr);
		
		
		
		
		//获取IN
        $shops = D('Shop')->where(array('city_id'=>$this->city_id))->select();
        foreach($shops as $val){
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
		$lists = D('Eleproduct')->where(array('is_tuijian'=>1, 'audit' => 1, 'closed' =>0,'shop_id'=>array('in', $shop_ids),'cost_price' => array('neq','')))->order(array('sold_num' => 'desc','create_time' => 'desc'))->limit(0,6)->select();
		$this->assign('product', $list = second_array_unique_bykey($lists,'shop_id'));//去掉重复商家
        $this->display();
    }
	


	
	
    public function loaddata(){
        $ele = D('Ele');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'is_open'=>1, 'city_id' => $this->city_id);
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['shop_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $business = (int) $this->_param('business');
        if ($business) {
            $map['business_id'] = $business;
        }
        
		
		
		
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if(empty($lat) || empty($lng)){
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
		
		
		
		$order = $this->_param('order', 'htmlspecialchars');
        switch($order){
            case 'a':
                $orderby = array("(ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') )" => 'asc', 'orderby' => 'asc', 'month_num' => 'desc', 'distribution' => 'asc', 'since_money' => 'asc');
                break;
            case 'p':
                $orderby = array('since_money' => 'asc');
                break;
            case 'v':
                $orderby = array('distribution' => 'asc');
                break;
            case 'd':
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}')) asc ";
                break;
            case 's':
                $orderby = array('month_num' => 'desc');
                break;
			default:
                $orderby = array( 'orderby' => 'asc',"(ABS(lng - '{$lng}') +  ABS(lat - '{$lat}'))" => 'asc');
                break;
        }
		
        $cate = $this->_param('cate', 'htmlspecialchars');
        $lists = $ele->order($orderby)->where($map)->select();
		 
        foreach($lists as $k => $val){
			//if(!is_QQBrowser()){
				$lists[$k]['radius'] = $val['is_radius'];
				$lists[$k]['is_radius'] = getDistanceNone($lat, $lng, $val['lat'], $val['lng']);
				if (!empty($val['is_radius'])){ 
				   if ($lists[$k]['is_radius'] > $val['is_radius']*10000){ 
					   unset($lists[$k]);
					}
				}
			//}
            if($this->closeshopele($val['busihour'])){
                $lists[$k]['bsti'] = 1;
				unset($lists[$k]);//不要让打样店铺显示
            }
			//分类筛选
			$cates = explode(',',$val['cate']);
			$res = array_search($cate,$cates);
			if($cate && $res === false){
				unset($lists[$k]);
			}
			
			
        }
        $count = count($lists);
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		
        $p = $_GET[$var];
        if($Page->totalPages < $p){
            die('0');
        }
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        $shop_ids = array();
        foreach ($list as $k => $val){
            $shop_ids[$val['shop_id']] = $val['shop_id'];
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
			$list[$k]['score'] = D('Eledianping')->getShopScore($val['shop_id']);
            //新版筛选分类高于10个分类解决方案
        }
		
        if($shop_ids){
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
	
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }


    public function closeshopele($busihour) {
        $timestamp = time();
        $now = date('G.i', $timestamp);
        $close = true;
        if (empty($busihour)) {
            return false;
        }
        foreach (explode(',', str_replace(':', '.', $busihour)) as $period) {
            list($periodbegin, $periodend) = explode('-', $period);
            if ($periodbegin > $periodend && ($now >= $periodbegin || $now < $periodend) || $periodbegin < $periodend && $now >= $periodbegin && $now < $periodend) {
                $close = false;
            }
        }
        return $close;
    }
	//菜品列表
    public function shop($shop_id = 0){
        $shop_id = $this->_param('shop_id');
        if(!($detail = D('Ele')->find($shop_id))){
            $this->error('该餐厅不存在');
        }
        if(!($shop = D('Shop')->find($shop_id))){
            $this->error('该餐厅不存在');
        }
		if($this->closeshopele($detail['busihour'])){
           $detail['bsti'] = 1;
        }else{
           $detail['bsti'] = 0;
        }
		$obj = D('Eleproduct');
		$map = array('closed' => 0, 'audit' => 1, 'shop_id' => $shop_id);
		
		if($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['product_name|desc'] = array('LIKE', '%' . $keyword . '%');
			$this->assign('keyword', $keyword);	
        }
		$this->assign('tuijian', $tuijian = $obj->where(array('shop_id' => $shop_id,'is_tuijian'=>1, 'closed' =>0, 'audit' => 1, 'cost_price' => array('neq','')))->order(array('create_time' =>'desc'))->limit(0,2)->select());//推荐开始
		$list = $obj->where($map)->order(array('sold_num' =>'desc','price' => 'asc'))->select();
		
		
		foreach ($list as $k => $val){
			
			/*
			foreach ($tuijian as $kk => $val2){
				if($val2['product_id'] == $val['product_id']){
					unset($list[$k]);
				}
			}*/
			
			
            $list[$k]['cart_num'] = $this->cart[$val['product_id']]['cart_num'];
        }
		
		
		
        foreach($list as $k => $val){
            if($val['cate_id']) {
                $cate_ids[$val['cate_id']] = $val['cate_id'];
            }
            $list[$k] = $val;
        }
        if($cate_ids) {
            $cates = D('Elecate')->itemsByIds($cate_ids);
            $ids = array();
            foreach($cates as $k => $val){
                $ids[$d][] = $k;
            }
            ksort($ids);
            $showcates = array();
            foreach ($ids as $arr1) {
                foreach ($arr1 as $val) {
                    $showcates[$val] = $cates[$val];
                }
            }
            $this->assign('cate', $showcates);
        }
        
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->assign('cates', D('Elecate')->where(array('shop_id' => $shop_id, 'closed' => 0))->select());
        $this->assign('shop', $shop);
		
        $this->display();
    }

    public function order(){
        if(empty($this->uid)){
            $this->tuMsg('请先登陆', U('passport/login'));
        }
		
		
		if($lists = cookie('ele')){
            $lists = (array) json_decode($lists,true);
            $list = array();
			foreach($lists as $key=>$val){
				foreach($val as $k=>$v){
				  $list[] = $v;
				}
			}
        }
        if(empty($list)){
            $this->tuMsg('您还没有订餐呢');
        }
        $shop_id = 0;
        $shops = array();
        $products = array();
        $total = array('money' => 0, 'num' => 0);
        $product_name = array();
		
		foreach($list as $key => $val) {
            if($val['num'] < 1  || $val['num'] > 99){
				unset($list[$key]);
            }
           
        }
		if(!$list){
			$this->tuMsg('请选择正确的购买数量');
		}
		
        foreach ($list as $key => $val) {
            $product = D('Eleproduct')->find($val['product_id']);
            $product_name[] = $product['product_name'];
            if (empty($product)) {
				$this->tuMsg('产品不正确');
            }
            $shop_id = $product['shop_id'];
            $product['buy_num'] = $val['num'];
            $products[$key] = $product;
            $shops[$shop_id] = $shop_id;
            $total['num'] += $val['num'];
			$total['tableware_price'] += $product['tableware_price'] * $val['num'];//结算费总计
			$total['money'] += ($product['price'] * $val['num']) +($product['tableware_price'] * $val['num']);//用户值及支付 = 菜品价格+餐盒费
			$settlement_price  += ($product['settlement_price'] * $val['num'])+($product['tableware_price'] * $val['num']);//结算价格 = 菜品结算价+餐盒费
        }
        if (count($shops) > 1) {
            $this->tuMsg('您购买的商品是2个商户的');
        }
        if (empty($shop_id)) {
            $this->tuMsg('商家不存在');
        }
        $shop = D('Ele')->find($shop_id);
        if (empty($shop)) {
            $this->tuMsg('该商家不存在');
        }
		if (false == D('Shop')->check_shop_user_id($shop_id,$this->uid)) {//不能购买自己家的产品
			 $this->tuMsg('您不能购买自己的外卖');
		}
		
        if (!$shop['is_open']) {
            $this->tuMsg('商家已经打烊，实在对不住客官');
        }
		$busihour = $this->closeshopele($shop['busihour']);
		 if ($busihour == 1) {
            $this->tuMsg('商家休息中，请稍后再试');
        }
		
		$total['logistics_full_money'] = D('Eleorder')->get_logistics($total['money'],$shop_id);//获取用户实际支付配送费用
		
        $total['money'] += $shop['logistics'];//加上配送费
		//$total['money'] += $total['tableware_price'];//加上餐具费
		
        $total['need_pay'] = $total['money'];
		
		$total['full_reduce_price'] = D('Eleorder')->get_full_reduce_price($total['money'],$shop_id);//获取满减费用
		
        if ($shop['since_money'] > $total['money']){
            $this->tuMsg('客官，您再订点吧');
        }
		//新客户满多少减去多少
        if ($shop['is_new'] && !D('Eleorder')->checkIsNew($this->uid, $shop_id)) {
            if ($total['money'] >= $shop['full_money']) {
                $total['new_money'] = $shop['new_money'];
            }
        };
		
		
		//结算金额逻辑后期封装，如果是第三方配送，如果开通新单立减后，配送费用商家出，如果商家开启满减优惠，满减优惠商家出
		
		$shop_detail = D('Shop')->where(array('shop_id'=>$shop_id))->find();
		if($shop_detail){
			$logistics = 0;
			if($shop_detail['is_ele_pei'] == 1){
				//第三方平台配送，结算价-新单立减-配送费-满减费用
				$last_settlement_price = $settlement_price - $total['new_money'] - $total['logistics_full_money'] - $total['full_reduce_price'];
				
				$settlementIntro.='实际结算价格【'.round($last_settlement_price/100,2).'元】=';
				$settlementIntro.='结价格【'.round($settlement_price/100,2).'元】';
				if($total['new_money']){
					$settlementIntro.='-新单立减：【'.round($total['new_money']/100,2).'元】';
				}
				if($total['logistics_full_money']){
					$settlementIntro.='- 满减配送费：【'.round($total['logistics_full_money']/100,2).'元】';
				}
				if($total['logistics_full_money']){
					$settlementIntro.=' - 满减活动扣费：【'.round($total['full_reduce_price']/100,2).'元】';
				}
				//p($settlementIntro);die;
			}
		}else{
			//商家自己配送，结算价-新单立减+ 配送费-满减费用
			
			$logistics = ($shop['logistics'] >= 50) ? $shop['logistics'] : '50';
			
			$last_settlement_price = $settlement_price - $total['new_money'] - $total['full_reduce_price'] - $total['logistics_full_money'] + $logistics;
			
			$settlementIntro.='实际结算价格【'.round($last_settlement_price/100,2).'元】=';
			$settlementIntro.='结价格【'.round($settlement_price/100,2).'元】';
			if($total['new_money']){
				$settlementIntro.='-新单立减：【'.round($total['new_money']/100,2).'元】';
			}
			if($total['full_reduce_price']){
				$settlementIntro.=' - 满减活动扣费：【'.round($total['full_reduce_price']/100,2).'元】';
			}
			if($total['logistics_full_money']){
				$settlementIntro.=' - 满减配送费：【'.round($total['logistics_full_money']/100,2).'元】';
			}
			
			if($logistics){
				$settlementIntro.=' + 配送费：【'.round($logistics/100,2).'元】';
			}
			
			
		}
	    
		//p($total['full_reduce_price']);
		//p($settlementIntro);die;
		
		
		$total['need_pay'] = $total['need_pay'] - $total['new_money'] - $total['logistics_full_money'] - $total['reduce_coupun_money']- $total['full_reduce_price'];
		
		
        $month = date('Ym', NOW_TIME);
        if ($order_id = D('Eleorder')->add(array(
			'user_id' => $this->uid, 
			'shop_id' => $shop_id, 
			'total_price' => $total['money'], 
			'need_pay' => $total['need_pay'], 
			'num' => $total['num'], 
			'new_money' => (int) $total['new_money'], 
			'logistics_full_money' => (int) $total['logistics_full_money'], 
			'full_reduce_price' => (int) $total['full_reduce_price'], 
			'logistics' => $logistics, 
			'tableware_price' => (int) $total['tableware_price'], 
			'settlement_price' => $last_settlement_price, 
			'settlementIntro' => $settlementIntro, 
			'status' => 0, 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip(), 
			'is_pay' => 0, 
			'month' => $month
		))) {
            foreach ($products as $val) {
                D('Eleorderproduct')->add(array(
					'order_id' => $order_id, 
					'product_id' => $val['product_id'], 
					'num' => $val['buy_num'], 
					'total_price' => $val['price'] * $val['buy_num'], 
					'tableware_price' => $val['tableware_price'] * $val['buy_num'], 
					'month' => $month
				));
            }
            setcookie("ele", "", time() - 3600, "/");
            $this->tuMsg('下单成功，您可以选择配送地址', U('ele/pay', array('order_id' => $order_id)));
        }
        $this->tuMsg('创建订单失败');
    }
	
	
	
    public function message(){
        $order_id = (int) $this->_get('order_id');
        if (!($detail = D('Eleorder')->find($order_id))) {
            $this->tuMsg('没有该订单');
            die;
        }
        if ($detail['status'] != 0) {
            $this->tuMsg('参数错误');
            die;
        }
        $ele_shop = D('Ele')->find($detail['shop_id']);
        $tags = $ele_shop['tags'];
        $tagsarray = array();
        if (!empty($tags)) {
            $tagsarray = explode(',', $tags);
        }
        if ($this->isPost()) {
            if ($message = $this->_param('message', 'htmlspecialchars')) {
                $data = array('order_id' => $order_id, 'message' => $message);
                if (D('Eleorder')->save($data)) {
                    $this->tuMsg('添加留言成功', U('Wap/ele/pay', array('order_id' => $detail['order_id'])));
                }
            }
            $this->tuMsg('请填写留言');
        } else {
            $this->assign('detail', $detail);
            $this->assign('tagsarray', $tagsarray);
            $this->display();
        }
    }
    public function pay(){
        if(empty($this->uid)){
            header('Location:' . U('passport/login'));
            die;
        }
        $this->check_mobile();
        $order_id = (int) $this->_get('order_id');
        $order = D('Eleorder')->find($order_id);
        if(empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid){
            $this->error('该订单不存在');
            die;
        }
        $this->assign('shop', D('Ele')->find($order['shop_id']));
        $ordergoods = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        $goods = array();
        foreach($ordergoods as $key => $val){
            $goods[$val['product_id']] = $val['product_id'];
        }
        $products = D('Eleproduct')->itemsByIds($goods);
        $this->assign('products', $products);
        $this->assign('ordergoods', $ordergoods);
        $useraddr_is_default = D('Useraddr')->where(array('user_id' => $this->uid, 'is_default' => 1))->limit(0, 1)->select();
        $useraddrs = D('Useraddr')->where(array('user_id' => $this->uid))->limit(0, 1)->select();
		
        if(!empty($useraddr_is_default)){
            $this->assign('useraddr', $useraddr_is_default);
        }else{
            $this->assign('useraddr', $useraddrs);
        }
		
        $this->assign('order', $order);
        $eles = D('Ele')->find($order['shop_id']);
        if($eles['is_pay'] == 1) {
            $payment = D('Payment')->getPayments(true);
        }else{
            $payment = D('Payment')->getPayments_delivery(true);
        }
        $this->assign('payment', $payment);
        $this->display();
    }
	
	
    public function pay2() {
        if(empty($this->uid)){
            $this->ajaxLogin();
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Eleorder')->find($order_id);
        if(empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid){
            $this->tuMsg('该订单不存在');
            die;
        }
		
        $addr_id = (int) $this->_post('addr_id');
        $uaddr = D('Useraddr')->where('addr_id =' . $addr_id)->find();
        if(empty($addr_id)){
            $this->tuMsg('请选择一个要配送的地址');
        }
		
		
        D('Eleorder')->save(array('addr_id' => $addr_id, 'order_id' => $order_id));
        if (!($code = $this->_post('code'))){
            $this->tuMsg('请选择支付方式');
        }
		
		/*
		if(false == D('Eleorder')->getAddrDistance($addr_id,$order['shop_id'])){
			$this->tuMsg('您选择地址不在配送范围内');
		}
		*/
		
        if($code == 'wait'){
            D('Eleorder')->ele_delivery_order($order_id);//外卖配送接口
            D('Eleorder')->save(array('order_id' => $order_id, 'status' => 1));
            setcookie("ele", "", time() - 3600, "/");
            D('Eleorder')->save(array('order_id' => $order_id, 'is_daofu' => 1, 'status' => 1));
			D('Eleorder')->combination_ele_print($order_id, $addr_id);//外卖打印万能接口
            D('Sms')->eleTZshop($order_id);
			D('Eleorder')->ele_month_num($order_id);//更新外卖销量
			D('Weixintmpl')->weixin_notice_ele_user($order_id,$this->uid,0);//外卖微信通知货到付款
            $this->tuMsg('货到付款您下单成功', U('user/eleorder/index'));
        }else{
            $payment = D('Payment')->checkPayment($code);
            if(empty($payment)){
                $this->error('该支付方式不存在');
            }
            $logs = D('Paymentlogs')->getLogsByOrderId('ele', $order_id);
            if(empty($logs)){
                $logs = array('type' => 'ele', 'user_id' => $this->uid, 'order_id' => $order_id, 'code' => $code, 'need_pay' => $order['need_pay'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'is_paid' => 0);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            }else{
                $logs['need_pay'] = $order['need_pay'];
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }
            $this->tuMsg('选择支付方式成功，正在跳转到支付页面', U('payment/payment', array('log_id' => $logs['log_id'])));
        }
    }
   
   
    public function favorites(){
        if(empty($this->uid)){
            $this->ajaxLogin();
        }
        $shop_id = (int) $this->_get('shop_id');
        if(!($detail = D('Shop')->find($shop_id))){
            $this->error('没有该商家');
        }
        if($detail['closed']){
            $this->error('该商家已经被删除');
        }
        if(D('Shopfavorites')->check($shop_id, $this->uid)){
            $this->error('您已经收藏过了');
        }
        $data = array('shop_id' => $shop_id, 'user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        if(D('Shopfavorites')->add($data)){
            $this->success('恭喜您收藏成功', U('ele/detail', array('shop_id' => $shop_id)));
        }
        $this->error('收藏失败');
    }
	
	
    public function detail(){
        $shop_id = (int) $this->_param('shop_id');
        if(!($detail = D('Ele')->find($shop_id))){
            $this->error('没有该商家');
            die;
        }
        if($detail['closed'] != 0 || $detail['audit'] != 1){
            $this->error('该商家不存在');
            die;
        }
        $this->assign('detail', $detail);
        $this->assign('shop', D('Shop')->find($shop_id));
        $this->assign('ex', D('Shopdetails')->find($shop_id));
        $this->display();
    }
	
	
    public function dianping(){
        $shop_id = (int) $this->_get('shop_id');
        if(!($detail = D('Ele')->find($shop_id))){
            $this->error('没有该商家');
            die;
        }
        if($detail['closed']){
            $this->error('该商家已经被删除');
            die;
        }
        $this->assign('detail', $detail);
        $this->display();
    }
    public function dianpingloading(){
        $shop_id = (int) $this->_get('shop_id');
        if(!($detail = D('Ele')->find($shop_id))){
            die('0');
        }
        if($detail['closed'] != 0 || $detail['audit'] != 1){
            die('0');
        }
        $Eledianping = D('Eledianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'shop_id' => $shop_id, 'show_date' => array('ELT', TODAY));
        $count = $Eledianping->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $show = $Page->show();
        $list = $Eledianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $order_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $order_ids[$val['order_id']] = $val['order_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($order_ids)) {
            $this->assign('pics', D('Eledianpingpics')->where(array('order_id' => array('IN', $order_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	//点评详情
    public function img(){
        $dianping_id = (int) $this->_get('dianping_id');
        if (!($detail = D('Eledianping')->where(array('dianping_id'=>$dianping_id))->find())){
            $this->error('没有该点评');
            die;
        }
        if ($detail['closed']) {
            $this->error('该点评已经被删除');
            die;
        }
        $list =  D('Eledianpingpics')->where(array('order_id' =>$detail['order_id']))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	
}