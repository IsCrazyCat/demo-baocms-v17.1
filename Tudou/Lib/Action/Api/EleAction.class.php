<?php



class EleAction extends CommonAction{
	
	//获取海报
	public function banners(){
		$list = D('Ad')->where(array('site_id'=>'73','closed'=>'0'))->select();
		foreach ($list as $k => $val){
			$list[$k]['id'] = $val['ad_id'];
			$list[$k]['img'] = strpos($val['photo'],"http")===false ?  __HOST__.$val['photo'] : $val['photo'];
		}
		$json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$list);
        $json_str = json_encode($json_arr);
        exit($json_str); 
		
	}
	
	
	//获取分类
	public function cate(){
	    $cate = D('Ele')->getEleCate();
	    $arr = array();
	    foreach($cate as $k => $v){
		   $arr[$k]['id'] = $k;
		   $arr[$k]['name'] = $v;
		   $kk = $k + 1 ;
		   $arr[$k]['img'] = __HOST__.'/static/default/wap/image/ele/ele_cate_'.$kk.'.png';	
	    } 
		$datas = array();
		$data = array('0'=>'');
		foreach($data as $k2 => $vv){
			$datas[$k2] =  $arr;
	    }
		$json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$datas);
        $json_str = json_encode($json_arr);
        exit($json_str); 
	}
	
	//首页商家列表  商家搜索
	public function index(){
		$ele = D('Ele');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'is_open'=>1);
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
        $order = $this->_param('order', 'htmlspecialchars');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        switch ($order) {
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
		 
        foreach ($lists as $k => $val) {
				$lists[$k]['radius'] = $val['is_radius'];
				$lists[$k]['is_radius'] = getDistanceNone($lat, $lng, $val['lat'], $val['lng']);
				if (!empty($val['is_radius'])) { 
				   if ($lists[$k]['is_radius'] > $val['is_radius']*10000) { 
					   unset($lists[$k]);
					}
				}
            if (!empty($cate)) {
                if (strpos($val['cate'], $cate) === false) {
                    unset($lists[$k]);
                }
            }
			
        }
        $count = count($lists);
        $Page = new Page($count, 5);
        $show = $Page->show();
        $var = 'p';
        $p = intval($_GET[$var]);
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        $shop_ids = array();


        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        //所有商家信息
        $shops = D('Shop')->itemsByIds($shop_ids);
        $count = 0;
        foreach ($list as $k => $val) {
        	$photo = $shops[$val['shop_id']]['photo'];
            $list[$k]['photo'] = strpos($photo,"http")===false ?  __HOST__.$shops[$val['shop_id']]['photo'] : $photo ;
 

            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
			$list[$k]['score'] = D('Eledianping')->getShopScore($val['shop_id']);

			$list[$k]['since_money']=round($val['since_money']/100,2);
			$list[$k]['logistics']=round($val['logistics']/100,2);

            if ($this->closeshopele($val['busihour'])) {
                $list[$k]['bsti'] = 1;
            } else {
                $list[$k]['bsti'] = 0;
            }
            $count++;
        }

        
        $json_arr = array('status'=>1,'msg'=>'获取成功','p'=>$p,'count'=>$count,'data'=>$list);
        
        $json_str = json_encode($json_arr);
        exit($json_str); 
       
	}

	//辅助函数
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

    //商家菜品分类
    public function shopCates($shop_id){

		$cates = D('Elecate')->where(array('shop_id' => $shop_id, 'closed' => 0))->select();
		
		$Eleproduct = D('Eleproduct');
		foreach ($cates as $k => $v) {
        	$map = array('closed' => 0, 'audit' => 1, 'shop_id' => $shop_id,'cate_id'=>$v['cate_id']);
        	$product_ids = $Eleproduct->Field('product_id')->where($map)->select();

        	$len = 0;
        	foreach ($product_ids as $key => $value) {
        		$cates[$k]['goods'][] = (int)$value['product_id'];
        		$len++;
        	}
        	$cates[$k]['id'] = "cat_".$v['cate_id'];
        	$cates[$k]['len'] = $len;
            if(empty($product_ids)) unset($cates[$k]);
		}
		$json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$cates);
        $json_str = json_encode($json_arr);
        exit($json_str); 
    }
	
	
    //详情页
    public function shop(){
        $shop_id = (int) $this->_param('shop_id');
        if (!($detail = D('Ele')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
        if (!($shop = D('Shop')->find($shop_id))) {
            $this->error('该餐厅不存在');
        }
		if ($this->closeshopele($detail['busihour'])) {
           $detail['bsti'] = 1;
        } else {
           $detail['bsti'] = 0;
        }
        $Eleproduct = D('Eleproduct');
        $map = array('closed' => 0, 'audit' => 1, 'shop_id' => $shop_id);
        $list = $Eleproduct->where($map)->order(array('sold_num' => 'desc', 'price' => 'asc'))->select();
        foreach ($list as $k => $val) {
            $list[$k]['cart_num'] = $this->cart[$val['product_id']]['cart_num'];

            $photo = $val['photo'];
            $list[$k]['photo'] = strpos($photo,"http")===false ?  __HOST__.$val['photo'] : $photo ;
            $list[$k]['price'] = round($val['price']/100,2);
            $goods[$val['product_id']] = $list[$k];
 
        }

        $photo = $shop['photo'];
        $detail['photo'] = strpos($photo,"http")===false ?  __HOST__.$photo : $photo;

        $data['goods'] = $goods;
        $data['shop'] = $detail;

        $json_arr = array('status'=>1,'msg'=>'获取成功','data'=>$data);
        $json_str = json_encode($json_arr);
        exit($json_str);   
    }
	
	
    //下单
    public function order(){
        $data = I('post.');
        $data['data'] = str_replace("&quot;","\"",$data['data']);

        $data['data'] = json_decode($data['data'],true);

        
        foreach ($data['data']['list'] as $key => $value) {
            $list[$key]['product_id'] = $key;
            $list[$key]['num'] = $value;
        }

        if(empty($list)){
            exit(json_encode(array('status'=>-1,'msg'=>'你还没有订餐呢','data'=>'')));
        }

        $shop_id = 0;
        $shops = array();
        $products = array();
        $total = array('money' => 0, 'num' => 0);
        $product_name = array();
        $user = $this->checkLogin($data['rd_session']);
        $this->uid = $user['uid'];

        foreach($list as $key => $val) {
            if($val['num'] < 1  || $val['num'] > 99){
                unset($list[$key]);
            } 
        }
        if(!$list){
           exit(json_encode(array('status'=>-1,'msg'=>'请选择正确的数量','data'=>'')));
        }

       
        foreach ($list as $key => $val) {
            $product = D('Eleproduct')->find($val['product_id']);
            $product_name[] = $product['product_name'];
            if (empty($product)) {
                 exit(json_encode(array('status'=>-1,'msg'=>'产品不正确','data'=>'')));
            }
            $shop_id = $product['shop_id'];
            $product['buy_num'] = $val['num'];
            $products[$key] = $product;
            $shops[$shop_id] = $shop_id;
            $total['money'] += $product['price'] * $val['num'];
            $total['num'] += $val['num'];
            $total['tableware_price'] += $product['tableware_price'] * $val['num'];
            $settlement_price  += $product['settlement_price'] * $val['num'];
        } 

        if (empty($shop_id)) {
             exit(json_encode(array('status'=>-1,'msg'=>'商家不存在','data'=>'')));
        }
        $shop = D('Ele')->find($shop_id);
        if (empty($shop)) {
             exit(json_encode(array('status'=>-1,'msg'=>'该商家不存在','data'=>'')));
        }
        if (false == D('Shop')->check_shop_user_id($shop_id,$this->uid)) {//不能购买自己家的产品
             exit(json_encode(array('status'=>-1,'msg'=>'您不能购买自己家的外卖，请换个账号登录','data'=>'')));
        }
        
        if (!$shop['is_open']) {
             exit(json_encode(array('status'=>-1,'msg'=>'商家已经打烊了','data'=>'')));
        }
        $busihour = $this->closeshopele($shop['busihour']);
         if ($busihour == 1) {
             exit(json_encode(array('status'=>-1,'msg'=>'商家休息中，无法接受订餐','data'=>'')));
        }
        
        $total['logistics_full_money'] = D('Eleorder')->get_logistics($total['money'],$shop_id);//获取配送费用
        
        $total['money'] += $shop['logistics'];//加上配送费
        $total['money'] += $total['tableware_price'];//加上餐具费
        
        $total['need_pay'] = $total['money'];
        
        $total['full_reduce_price'] = D('Eleorder')->get_full_reduce_price($total['money'],$shop_id);//获取满减费用
        
        if ($shop['since_money'] > $total['money']) {
            exit(json_encode(array('status'=>-1,'msg'=>'没有达到配送金额','data'=>'')));
        }
        //新客户满多少减去多少
        if ($shop['is_new'] && !D('Eleorder')->checkIsNew($this->uid, $shop_id)) {
            if ($total['money'] >= $shop['full_money']) {
                $total['new_money'] = $shop['new_money'];
            }
        };
        
        
        //结算金额逻辑后期封装，如果是第三方配送，如果开通新单立减后，配送费用商家出，如果商家开启满减优惠，满减优惠商家出
        if($total['logistics_full_money']){
            $logistics = 0;
            $shop_detail = D('Shop')->find($shop_id);
            if($shop_detail['is_pei'] == 0){
                $last_settlement_price = $settlement_price -$total['logistics_full_money']- $total['full_reduce_price'];
            }
        }else{
            $logistics = $shop['logistics'];
            $last_settlement_price = $settlement_price - $total['full_reduce_price'];
        }
        
        
        $total['need_pay'] = $total['need_pay'] - $total['new_money'] - $total['logistics_full_money'] - $total['reduce_coupun_money']- $total['full_reduce_price']+$total['tableware_price'];
        
        
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

            exit(json_encode(array('status'=>1,'msg'=>'下单成功','order_id'=>$order_id)));
        }
        exit(json_encode(array('status'=>-1,'msg'=>'订单创建失败','data'=>'')));

    }

    //订单详情页，支付页面
    public function pay(){

        $rd_session=$this->_get('rd_session');
        $addr_id=$this->_get('addr_id');
        $user = $this->checkLogin($rd_session);
        $this->uid = $user['uid'];
        $order_id = (int) $this->_get('order_id');
        $order = D('Eleorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            exit(json_encode(array('status'=>-1,'msg'=>'订单不存在','data'=>'')));
        }

        $ordergoods = D('Eleorderproduct')->where(array('order_id' => $order_id))->select();
        $goods = array();
        foreach ($ordergoods as $key => $val) {
            $goods[$val['product_id']] = $val['product_id'];
        }
        $products = D('Eleproduct')->itemsByIds($goods);

        foreach ($ordergoods as $key => $value) {
            $ordergoods[$key]['product_name'] = $products[$value['product_id']]['product_name'];
            $ordergoods[$key]['total_price'] = round($value['total_price']/100,2);
        }

               
        $order['tableware_price']=round($order['tableware_price']/100,2);
        $order['full_reduce_price']=round($order['full_reduce_price']/100,2);
        $order['logistics']=round($order['logistics']/100,2);
        $order['total_price']=round($order['total_price']/100,2);
        $order['need_pay']=round($order['need_pay']/100,2);

        $order['cut_money_total'] = $order['total_price'] - $order['need_pay'];
        $eles = D('Ele')->find($order['shop_id']);

        $info['shop_name'] = $eles['shop_name'];
        $info['order_info'] = $order;
        $info['goods'] = $ordergoods;
        if(!$addr_id){
            //收货地址
            $useraddr_is_default = D('Useraddr')->where(array('user_id' => $this->uid, 'is_default' => 1))->limit(0, 1)->select();
            $useraddrs = D('Useraddr')->where(array('user_id' => $this->uid))->limit(0, 1)->select();

            if (!empty($useraddr_is_default)) {
                $info['addr_id'] = $useraddr_is_default[0]['addr_id'];
                $info['name'] = $useraddr_is_default[0]['name'];
                $info['mobile'] = $useraddr_is_default[0]['mobile'];
                $info['addr'] = $useraddr_is_default[0]['addr'];
            } else {
                $info['addr_id'] = $useraddrs[0]['addr_id'];
                $info['name'] = $useraddrs[0]['name'];
                $info['mobile'] = $useraddrs[0]['mobile'];
                $info['addr'] = $useraddrs[0]['addr'];
            }
        }else{
                $addr = D('UserAddr') -> where(array('user_id'=>$this->uid,'closed'=>0,'addr_id'=>$addr_id)) -> select();
                $info['addr_id'] = $addr[0]['addr_id'];
                $info['name'] = $addr[0]['name'];
                $info['mobile'] = $addr[0]['mobile'];
                $info['addr'] = $addr[0]['addr'];
        }
        exit(json_encode(array('status'=>1,'msg'=>'订单获取成功','info'=>$info)));
        if ($eles['is_pay'] == 1) {
            $payment = D('Payment')->getPayments(true);
        } else {
            $payment = D('Payment')->getPayments_delivery(true);
        }
        $this->assign('payment', $payment);
    }

    

}