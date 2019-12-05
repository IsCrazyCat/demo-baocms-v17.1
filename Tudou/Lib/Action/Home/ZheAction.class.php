<?php
class ZheAction extends CommonAction {
	protected $Activitycates = array();
    public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['zhe'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->shopcates = D('Shopcate')->fetchAll();
        $this->assign('shopcates', $this->shopcates);
		$this->getZheWeek = D('Zhe')->getZheWeek();
        $this->assign('weeks',  $this->getZheWeek);
        $this->getZheDate = D('Zhe')->getZheDate();
        $this->assign('dates',  $this->getZheDate);
		$this->assign('host',__HOST__);
    }

    public function index() {
        $Zhe = D('Zhe');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'audit' => 1,'city_id'=>$this->city_id, 'end_date' => array('EGT', TODAY));
		
		$linkArr = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        //搜索二开结束
		$cates = D('Shopcate')->fetchAll();
        $cat = (int) $this->_param('cat');
        $cate_id = (int) $this->_param('cate_id');
        if ($cat) {
            if (!empty($cate_id)) {
                $map['cate_id'] = $cate_id;
                $this->seodatas['cate_name'] = $cates[$cate_id]['cate_name'];
                $linkArr['cat'] = $cat;
                $linkArr['cate_id'] = $cate_id;
            } else {
                $catids = D('Appointcate')->getChildren($cat);
                if (!empty($catids)) {
                    $map['cate_id'] = array('IN', $catids);
                }
                $this->seodatas['cate_name'] = $cates[$cat]['cate_name'];
                $linkArr['cat'] = $cat;
            }
        }
        $this->assign('cat', $cat);
        $this->assign('cate_id', $cate_id);
        $area = (int) $this->_param('area');
        if ($area) {
            $map['area_id'] = $area;
            $this->seodatas['area_name'] = $this->areas[$area]['area_name'];
            $linkArr['area'] = $area;
        }
        $this->assign('area_id', $area);
        $business = (int) $this->_param('business');
        if ($business) {
            $map['business_id'] = $business;
            $this->seodatas['business_name'] = $this->bizs[$business]['business_name'];
            $linkArr['business'] = $business;
        }
        $this->assign('business_id', $business);
		
		//增加星期几选择
		$week_id = (int) $this->_param('week_id');
        if ($week_id) {
            $this->seodatas['week_name'] = $this->getZheWeek[$week_id];
            $linkArr['week_id'] = $week_id;
        }
        $this->assign('week_id', $week_id);
		$order = $this->_param('order', 'htmlspecialchars');
        $orderby = '';
        switch ($order) {
            case 's':
                $orderby = array('yuyue_num' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 'p':
                $orderby = array('price' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 't':
                $orderby = array('create_time' => 'asc');
                $linkArr['order'] = $order;
                break;
            case 'v':
                $orderby = array('views' => 'asc');
                $linkArr['order'] = $order;
                break;
            default:
                $orderby = array('yuyue_num' => 'desc', 'zhe_id' => 'desc');
                break;
        }
		$this->assign('order', $order);
		//搜索二开结束
        $count = $Zhe->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $Zhe->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $cate_ids = array();
        foreach ($list as $k => $val) {
			$list[$k]['get_day_week'] = $Zhe->get_day_week($val['zhe_id']); 
			$list[$k]['get_day_date'] = $Zhe->get_day_date($val['zhe_id']);
			if (!empty($week_id)) {
			    $a = $Zhe->get_weed_id_unset($val['week_id'],$week_id);
				if(empty($a)){
				  	unset($list[$k]);
				 }
            }
			 
			if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
				$cate_ids[$val['cate_id']] = $val['cate_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
		if ($cate_ids) {
            $this->assign('shop_cates', D('Shopcate')->itemsByIds($cate_ids));
        }
		$selArr = $linkArr;
        foreach ($selArr as $k => $val) {
            if ($k == 'order' || $k == 'new' || $k == 'freebook' || $k == 'hot' || $k == 'tui' || $k == 'area'|| $k == 'week_id') {
                unset($selArr[$k]);
            }
        }		
        $this->assign('list', $list); 
		$this->assign('selArr', $selArr);
		$this->assign('cates', $cates);
        $this->assign('page', $show);
		$this->assign('linkArr', $linkArr);
        $this->display(); 
    }



    public function detail($zhe_id) {
        $zhe_id = (int) $zhe_id;
		$Zhe = D('Zhe');
		//检测域名前缀封装函数
		$Zhe_city_id = $Zhe->where(array('zhe_id' => $zhe_id))->Field('city_id')->select();
		$url = D('city')->check_city_domain($Zhe_city_id['0']['city_id'],$this->_NOWHOST,$this->_TU_DOMAIN);
		if(!empty($url)){
			header("Location:".$url);
		}
		if (!$detail = $Zhe->find($zhe_id)) {
            $this->error('该五折卡项目不存在');
            die;
        }
		
		$this->seodatas['cate_name'] = $this->shopcates[$detail['cate_id']]['cate_name'];
        $this->seodatas['cate_area'] = $this->areas[$detail['area_id']]['area_name'];
        $this->seodatas['cate_business'] = $this->bizs[$detail['business_id']]['business_name'];
        $this->seodatas['description'] = $detail['description'];
        $this->seodatas['title'] = $detail['title'];
		
        if ($this->shopcates[$detail['cate_id']]['parent_id'] == 0) {
            $this->assign('catstr', $this->shopcates[$detail['cate_id']]['cate_name']);
        } else {
            $this->assign('catstr', $this->shopcates[$this->shopcates[$detail['cate_id']]['parent_id']]['cate_name']);
            $this->assign('cat', $this->shopcates[$detail['cate_id']]['parent_id']);
            $this->assign('catestr', $this->shopcates[$detail['cate_id']]['cate_name']);
        }
		
		$Zhe->updateCount($zhe_id, 'views');//更新浏览量
		$this->assign('week_ids', $week_ids = explode(',', $detail['week_id']));
		$this->assign('date_ids', $date_ids = explode(',', $detail['date_id']));
		$this->assign('shops', D('Shop')->find($detail['shop_id']));
		$this->assign('check_user_zhe',$check_user_zhe =  D('Zhe')->check_user_zhe($this->uid));
        $this->assign('totalnum', $count);
        $this->assign('list', $list); 
        $this->assign('page', $show);
		$this->assign('detail', $detail);
		$this->assign('height_num', 760);//下拉横条导航
        $this->display();

    }

    //五折卡购买直接支付页面
	 public function buy(){
        if (empty($this->uid)) {
            $this->tuSuccess("请登录后购买" , U('passport/login'));
            die;
        }
		$obj = D('Zheorder');
		if (!$obj->Check_Zhe_Order_User_Buy($this->uid)) {
			$this->tuError($obj->getError());	  
		}
        $zhe_id = (int) $this->_get('zhe_id');
		$type = (int) $this->_param('type');
		$need_pay = $obj->get_zhe_need_pay($type);
		$number = $obj->get_zhe_number($this->uid);
		$data = array();
			$data['city_id'] = $this->city_id;
			$data['zhe_id'] = $zhe_id;
			$data['type'] = $type;
			$data['user_id'] = $this->uid;
			$data['status'] = 0;
			$data['need_pay'] = $need_pay;
			$data['number'] = $number;
			$data['create_time'] =	time();
			$data['create_ip'] = get_client_ip();
		if($order_id = $obj->add($data)){
			$this->tuMsg('恭喜您下单成功，正在为您跳转付款页面',U('zhe/pay',array('order_id'=>$order_id)));
		}else {
			 $this->tuError('创建订单失败，请稍后再试试');
		}
        $this->display();
    }
	
	//五折卡购买直接支付页面
	 public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Zheorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
		$this->assign('order', $order);
		$this->assign('type', crowd);
        $this->assign('payment', D('Payment')->getPayments());
        $this->display();
    }
	//去付款
	 public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Zheorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->tuError('该订单不存在');
            die;
        }
        if (!($code = $this->_post('code'))) {
            $this->tuError('请选择支付方式');
        }
        if ($code == 'wait') {
             $this->tuError('暂不支持货到付款，请重新选择支付方式');
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->tuError('该支付方式不存在，请稍后再试试');
            }
            $logs = D('Paymentlogs')->getLogsByOrderId('appoint', $order_id);//查找日志
			$need_pay = $order['need_pay'];//再更新防止篡改支付日志
            if (empty($logs)) {//独家再更新
                $logs = array(
					'type' => 'zhe', 
					'user_id' => $this->uid, 
					'order_id' => $order_id, 
					'code' => $code, 
					'need_pay' => $need_pay, 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
            } else {
                $logs['need_pay'] = $need_pay;
                $logs['code'] = $code;
                D('Paymentlogs')->save($logs);
            }
					
			if (false == D('Zheorder')->updateCount_buy_num($order_id)) {//更新什么什么的
				$this->tuError('更新购买信息出错');
			}else{
				$this->tuJump(U('payment/payment', array('log_id' => $logs['log_id'])));
			}
            
        }
    }
}

