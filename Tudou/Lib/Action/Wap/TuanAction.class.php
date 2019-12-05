<?php
class TuanAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        $this->assign('tuancates', $tuancates = D('Tuancate')->fetchAll());
    }
    
    public function index(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);
		$area = (int) $this->_param('area');
        $this->assign('area', $area);
		
		$shop_id = (int) $this->_param('shop_id');
        $this->assign('shop_id',$shop_id);
		
		
        $order = $this->_param('order', 'htmlspecialchars');
        $this->assign('order', $order);
        $this->assign('nextpage', LinkTo('tuan/loaddata', array('cat' => $cat, 'area' => $area, 'order' => $order, 'shop_id' => $shop_id,'t' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }
	
	
    public function loaddata(){
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        $map = array('audit' => 1, 'closed' => 0, 'city_id' => $this->city_id, 'end_date' => array('EGT', TODAY));
        if($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $cat = (int) $this->_param('cat');
        if($cat){
            $catids = D('Tuancate')->getChildren($cat);
            if(!empty($catids)){
                $map['cate_id'] = array('IN', $catids);
            }else{
                $map['cate_id'] = $cat;
            }
        }
        $area = (int) $this->_param('area');
        if($area){
            $map['area_id'] = $area;
        }
		
		$shop_id = (int) $this->_param('shop_id');
        if($shop_id){
            $map['shop_id'] = $shop_id;
        }
        $order = $this->_param('order', 'htmlspecialchars');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $orderby = '';
        switch ($order){
            case 3:
                $orderby = array('create_time' => 'desc');
                break;
            case 2:
                $orderby = array('orderby' => 'asc', 'tuan_id' => 'desc');
                break;
            default:
                $orderby = array('orderby' => 'asc','sold_num' => 'desc');
                break;
        }
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if($Page->totalPages < $p){
            die('0');
        }
        $list = $Tuan->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
            if($val['shop_id']){
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['end_time'] = strtotime($val['end_date']) - NOW_TIME + 86400;
            $list[$k] = $val;
        }
        if($shop_ids){
            $shops = D('Shop')->itemsByIds($shop_ids);
            $ids = array();
            foreach ($shops as $k => $val){
                $shops[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
                $d = getDistanceNone($lat, $lng, $val['lat'], $val['lng']);
                $ids[$d][] = $k;
            }
            ksort($ids);
            $showshops = array();
            foreach($ids as $arr1){
                foreach ($arr1 as $val){
                    $showshops[$val] = $shops[$val];
                }
            }
            $this->assign('shops', $showshops);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
    public function detail(){
        $tuan_id = (int) $this->_get('tuan_id');
        $tao_arr = D('Tuanmeal')->order(array('id' => 'asc'))->where(array('tuan_id' => $tuan_id))->select();
        $this->assign('tuan_id', $tuan_id);
        $this->assign('tao_arr', $tao_arr);
        if(empty($tuan_id)){
            $this->error('该抢购信息不存在');
            die;
        }
        if(!($detail = D('Tuan')->find($tuan_id))){
            $this->error('该抢购信息不存在');
            die;
        }
        if($detail['audit'] != 1){
            $this->error('该抢购信息还在审核中哦');
            die;
        }
        if($detail['closed']){
            $this->error('该抢购信息不存在');
            die;
        }
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if(empty($lat) || empty($lng)){
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        $detail = D('Tuan')->_format($detail);
        $detail['d'] = getDistance($lat, $lng, $detail['lat'], $detail['lng']);
        $detail['end_time'] = strtotime($detail['end_date']) - NOW_TIME + 86400;
        $this->assign('detail', $detail);
        $shop_id = $detail['shop_id'];
        $shop = D('Shop')->find($shop_id);
        $this->assign('tuans', D('Tuan')->where(array('audit' => 1, 'closed' => 0, 'shop_id' => $shop_id, 'bg_date' => array('ELT', TODAY), 'end_date' => array('EGT', TODAY), 'tuan_id' => array('NEQ', $tuan_id)))->limit(0, 5)->select());
        $pingnum = D('Tuandianping')->where(array('tuan_id' => $tuan_id))->count();
        $this->assign('pingnum', $pingnum);
        $score = (int) D('Tuandianping')->where(array('tuan_id' => $tuan_id))->avg('score');
        if($score == 0){
            $score = 5;
        }
        $this->assign('score', $score);
        $this->assign('tuandetails', $tuandetails = D('Tuandetails')->find($tuan_id));
        $this->assign('shop', $shop);
        $this->assign('tuansids', $tuansids = $detail['cate_id']);
        $this->assign('thumb', $thumb = unserialize($detail['thumb']));
		$this->assign('tuan_favorites', $tuan_favorites = D('Tuanfavorites')->check($tuan_id, $this->uid));//检测自己是不是收
        $this->display();
    }
	
	
    //团购图片详情
    public function pic(){
        $tuan_id = (int) $this->_get('tuan_id');
        if(!($detail = D('Tuan')->find($tuan_id))){
            $this->error('没有该团购');
            die;
        }
        if($detail['closed']){
            $this->error('该团购已经被删除');
            die;
        }
        $thumb = unserialize($detail['thumb']);
        $this->assign('thumb', $thumb);
        $this->assign('detail', $detail);
        $this->display();
    }
   
   
    public function dianpingloading(){
        $tuan_id = (int) $this->_get('tuan_id');
        if(!($detail = D('Tuan')->find($tuan_id))){
            die('0');
        }
        if($detail['closed']){
            die('0');
        }
		
        $Tuandianping = D('Tuandianping');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'tuan_id' => $tuan_id, 'show_date' => array('ELT', TODAY));
        $count = $Tuandianping->where($map)->count();
        $Page = new Page($count, 5);
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if($Page->totalPages < $p){
            die('0');
        }
        $list = $Tuandianping->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $orders_ids = array();
        foreach($list as $k => $val){
            $user_ids[$val['user_id']] = $val['user_id'];
            $orders_ids[$val['order_id']] = $val['order_id'];
        }
        if(!empty($user_ids)){
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if(!empty($orders_ids)){
            $this->assign('pics', D('Tuandianpingpics')->where(array('order_id' => array('IN', $orders_ids)))->select());
        }
        $this->assign('totalnum', $count);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
	
	//点评详情
    public function img(){
        $dianping_id = (int) $this->_get('dianping_id');
        if(!($detail = D('Tuandianping')->where(array('dianping_id'=>$dianping_id))->find())){
            $this->error('没有该点评');
            die;
        }
        if($detail['closed']){
            $this->error('该点评已经被删除');
            die;
        }
        $list =  D('Tuandianpingpics')->where(array('order_id' =>$detail['order_id']))->select();
        $this->assign('list', $list);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	
    public function order(){
        if(!$this->uid){
            $this->ajaxReturn(array('status' =>'login'));
        }
		if(empty($this->member['mobile'])){
			$this->ajaxReturn(array('status' =>'mobile'));
		}
		$tuan_id = I('tuan_id', 0, 'trim,intval');
        if(!($detail = D('Tuan')->find($tuan_id))){
			$this->ajaxReturn(array('status' => 'error', 'msg' => '该商品不存在'));
        }
        if($detail['closed'] == 1 || $detail['end_date'] < TODAY){
			$this->ajaxReturn(array('status' => 'error', 'msg' => '该商品已经结束'));
        }
		$num = I('num2',0,'trim,intval');
		
        if($num <= 0 || $num > 99){
			$this->ajaxReturn(array('status' => 'error', 'msg' => '请输入正确的购买数量'));
        }
		if($num > $detail['num']){
			$this->ajaxReturn(array('status' => 'error', 'msg' => '亲，您最多购买' . $detail['num'] . '份哦'));
        }
		if(false == D('Shop')->check_shop_user_id($detail['shop_id'],$this->uid)){
			$this->ajaxReturn(array('status' => 'error', 'msg' => '您不能购买自己的产品'));
		}
        if($num > $detail['xiangou'] && $detail['xiangou'] > 0){
			$this->ajaxReturn(array('status' => 'error', 'msg' => '亲，每人只能购买' . $detail['xiangou'] . '份哦'));
        }
        if($detail['xiadan'] == 1){
            $where['user_id'] = $this->uid;
            $where['tuan_id'] = $tuan_id;
            $xdinfo = D('Tuanorder')->where($where)->order('order_id desc')->Field('order_id')->find();
            if($xdinfo){
				$this->ajaxReturn(array('status' => 'error', 'msg' => '该商品只允许购买一次'));
            }
        }
        if($detail['xiangou'] > 0){
            $y = date('Y');
            $m = date('m');
            $d = date('d');
            $day_start = mktime(0, 0, 0, $m, $d, $y);
            $day_end = mktime(23, 59, 59, $m, $d, $y);
            $where['user_id'] = $this->uid;
            $where['tuan_id'] = $tuan_id;
            $xdinfo = D('Tuanorder')->where($where)->order('order_id desc')->Field('create_time,num')->select();
            $order_num = 0;
            foreach($xdinfo as $k => $val){
                if($val['create_time'] >= $day_start && $val['create_time'] <= $day_end){
                    $order_num += $val['num'] + $num;
                    if($order_num > $detail['xiangou']){
						$this->ajaxReturn(array('status' => 'error', 'msg' => '该商品每天每人限购' . $detail['xiangou'] . '份'));
                    }
                }
            }
        }
		
        $data = array(
			'tuan_id' => $tuan_id, 
			'num' => $num, 
			'user_id' => $this->uid, 
			'shop_id' => $detail['shop_id'], 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip(), 
			'total_price' => $detail['tuan_price'] * $num, 
			'mobile_fan' => $detail['mobile_fan'] * $num, 
			'need_pay' => $detail['tuan_price'] * $num - $detail['mobile_fan'] * $num, 
			'status' => 0, 
			'is_mobile' => 1
		);
		
        if($order_id = D('Tuanorder')->add($data)){
			$this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜下单成功','order_id'=>$order_id));
        }else{
			$this->ajaxReturn(array('status' => 'error', 'msg' => '创建订单失败'));
		}
    }
 
 
    public function pay(){
        if(empty($this->uid)){
            header('Location:' . U('passport/login'));
            die;
        }
		$order_id = I('order_id', 0, 'trim,intval');
        $order = D('Tuanorder')->find($order_id);
        if(empty($order)){
            $this->error('该订单不存在');
            die;
        }
		if($order['status'] != 0){
            $this->error('订单状态不正确');
            die;
        }
		if($order['user_id'] != $this->uid){
            $this->error('非法操作');
            die;
        }
        $Tuan = D('Tuan')->find($order['tuan_id']);
        if(empty($Tuan)){
            $this->error('该抢购不存在');
            die;
        }
		if($Tuan['closed'] == 1){
            $this->error('该抢购已删除');
            die;
        }
		if($Tuan['end_date'] < TODAY){
            $this->error('抢购已过期');
            die;
        }
        $this->assign('use_integral', $Tuan['use_integral'] * $order['num']);
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->assign('tuan', $Tuan);
        $this->assign('order', $order);
        $this->display();
    }
	
	
	
    public function tuan_mobile(){
        $this->mobile();
    }
    public function tuan_mobile2(){
        $this->mobile2();
    }
    public function tuan_sendsms(){
        $this->sendsms();
    }
	
	
    public function pay2(){
        if(empty($this->uid)){
            $this->tuMsg('登录状态失效!', U('passport/login'));
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Tuanorder')->find($order_id);
        if(empty($order) || (int) $order['status'] != 0 || $order['user_id'] != $this->uid){
            $this->tuMsg('该订单不存在');
        }
        if(!($code = $this->_post('code'))){
            $this->tuMsg('请选择支付方式');
        }
        $mobile = D('Users')->where(array('user_id' => $this->uid))->getField('mobile');
        if(!$mobile){
            $this->tuMsg('请先绑定手机号码再提交');
        }
		
        $pay_mode = '在线支付';
		
        if($code == 'wait'){
            $pay_mode = '货到支付';
            $codes = array();
            $obj = D('Tuancode');
            if (D('Tuanorder')->save(array('order_id' => $order_id,'status' => '-1'))){
                //更新成到店付的状态
                $tuan = D('Tuan')->find($order['tuan_id']);
                for ($i = 0; $i < $order['num']; $i++) {
                    $local = $obj->getCode();
                    $insert = array(
						'user_id' => $this->uid, 
						'shop_id' => $tuan['shop_id'], 
						'order_id' => $order['order_id'], 
						'tuan_id' => $order['tuan_id'], 
						'code' => $local, 
						'price' => 0, 
						'real_money' => 0, 
						'real_integral' => 0, 
						'fail_date' => $tuan['fail_date'], 
						'settlement_price' => 0, 
						'create_time' => NOW_TIME, 
						'create_ip' => $ip
					);
                    $codes[] = $local;
                    $obj->add($insert);
                }
                D('Tuan')->updateCount($tuan['tuan_id'], 'sold_num');//更新卖出产品
				D('Sms')->sms_tuan_user($this->uid,$order['order_id']);//团购商品通知用户
                D('Users')->prestige($this->uid, 'tuan');
                D('Sms')->tuanTZshop($tuan['shop_id']);
       			D('Weixintmpl')->weixin_notice_tuan_user($order_id,$this->uid,0);
                $this->tuMsg('恭喜您下单成功', U('user/tuan/index'));
            }else{
                $this->tuMsg('您已经设置过该抢购为到店付了');
            }
        }else{
            $payment = D('Payment')->checkPayment($code);
            if(empty($payment)){
                $this->tuMsg('该支付方式不存在');
            }
			
			$order['need_pay'] = D('Tuanorder')->get_tuan_need_pay($order_id,$this->uid,2);//获取实际支付价格封装
            $logs = D('Paymentlogs')->getLogsByOrderId('tuan', $order_id);
            if(empty($logs)){
                $logs = array(
					'type' => 'tuan', 
					'user_id' => $this->uid, 
					'order_id' => $order_id, 
					'code' => $code, 
					'need_pay' => $order['need_pay'], 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            }else{
                $logs['need_pay'] = $order['need_pay'];
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }
            $codestr = join(',', $codes);
            D('Weixintmpl')->weixin_notice_tuan_user($order_id,$this->uid,1);
            $this->tuMsg('订单设置完毕，即将进入付款', U('payment/payment', array('log_id'=>$logs['log_id'])),200);
        }
    }


	
	//抢购收藏
	public function favorites(){
        if(empty($this->uid)){
            $this->tuMsg('登录状态失效!', U('passport/login'));
            die;
        }
        $tuan_id = (int) $this->_get('tuan_id');
        if(!($detail = D('Tuan')->find($tuan_id))){
            $this->tuMsg('没有该抢购');
        }
        if($detail['closed']){
            $this->tuMsg('该抢购已经被删除');
        }
        if(D('Tuanfavorites')->check($tuan_id, $this->uid)){
            $this->tuMsg('您已经收藏过了');
        }
        $data = array('tuan_id' => $tuan_id, 'user_id' => $this->uid, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        if(D('Tuanfavorites')->add($data)){
            $this->tuMsg('恭喜您收藏成功', U('tuan/detail', array('tuan_id' => $tuan_id)));
        }
        $this->tuMsg('收藏失败');
    }
}