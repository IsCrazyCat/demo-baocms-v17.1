<?php
class WeixinmsgModel extends CommonModel{
    protected $pk = 'msg_id';
    protected $tableName = 'weixin_msg';
	protected $_type = array(
		'1' => '外卖', 
		'2' => '商城', 
		'3' => '家政', 
		'4' => '抢购', 
		'5' => '农家乐',
		'6' => '酒店',
		'7' => '订座',
		'8' => 'KTV',
		'9' => '菜市场',
		'10' => '便利店',
	);
	protected $_status = array(
		'0' => '下单成功', 
		'1' => '订单已支付', 
		'2' => '订单已发货', 
		'3' => '订单申请退款', 
		'4' => '订单已退款', 
		'8' => '订单已完成',
		'11' => '订单已取消',
	);
	
	protected $_Capital_Type = array(
		'1' => '余额可提现收入', 
		'2' => '积分', 
		'3' => '商户资金可提现收入', 
		'4' => '威望', 
	);
	
	//订单消息提醒OPENTM205109409
	//$order_id订单ID
	//$cate  1通知会员，2通知商家
	//$type  1外卖，2商城，3家政，4团购，5农家乐，6酒店，7订座，8KTV
	//$status 0取消订单，2订单已发货，3申请退款，4已同意退款，8订单已完成
    public function weixinTmplOrderMessage($order_id,$cate,$type,$status){
		$config = D('Setting')->fetchAll();
		    $remark = $this->_type[$type].''.$this->_status[$status].'详情请登录：' . $config['site']['host'];
			if($type == 1){
				$Eleorder = D('Eleorder')->where(array('order_id'=>$order_id))->find();
				$user_id = $this->getUserId($cate,$Eleorder['user_id'],$Eleorder['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = D('Eleorder')->get_ele_order_product_name($order_id);
				$price = round($Eleorder['need_pay']/100,2).'元';
				$status =  $this->_status[$status];
			}elseif($type == 2){
				$Order = D('Order')->find($order_id);
				$user_id = $this->getUserId($cate,$Order['user_id'],$Order['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = D('Ordergoods')->get_mall_order_goods_name($order_id);
				$price = round($Order['need_pay']/100,2).'元';
				$status =  $this->_status[$status];
			}elseif($type == 3){
				$Appointorder = D('Appointorder')->find($order_id);
				$Appoint= D('Appoint')->find($Appointorder['appoint_id']);
				$user_id = $this->getUserId($cate,$Appointorder['user_id'],$Appointorder['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = $Appoint['title'];
				$price = round($Appointorder['need_pay']/100,2).'元';
				$status =  $this->_status[$status];
			}elseif($type == 4){
				$Tuanorder = D('Tuanorder')->find($order_id);
			    $Tuan = D('Tuan')->find($Tuanorder['tuan_id']);
				$user_id = $this->getUserId($cate,$Tuanorder['user_id'],$Tuanorder['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = $Tuan['title'];
				$price = round($Tuanorder['need_pay']/100,2).'元';
				$status =  $this->_status[$status];
			}elseif($type == 5){
				$Farmgorder = D('Farmorder')->find($order_id);
			    $Farm = D('Farm')->find($Farmorder['farm_id']);
				$user_id = $this->getUserId($cate,$Farmgorder['user_id'],$Farm['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = $Farm['farm_name'];
				$price = $Farmgorder['amount'].'元';
				$status =  $this->_status[$status];
			}elseif($type == 6){
				$Hotelorder = D('Hotelorder')->where(array('order_id'=>$order_id))->find();
			    $Hotel = D('Hotel')->find($Hotelorder['hotel_id']);
				$Hotelroom = D('Hotelroom')->find($Hotelorder['room_id']);
				$user_id = $this->getUserId($cate,$Hotelorder['user_id'],$Hotel['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = $Hotelroom['title'];
				$price = $Hotelorder['amount'].'元';
				$status =  $this->_status[$status];
			}elseif($type == 7){
				$Bookingorder = D('Bookingorder')->find($order_id);
			    $Booking = D('Booking')->find($Bookingorder['shop_id']); 
			    $Bookingroom = D('Bookingroom')->find($Bookingorder['room_id']); 
				$user_id = $this->getUserId($cate,$Bookingorder['user_id'],$Bookingorder['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = $Bookingroom['name'];
				$price = round($Bookingorder['amount']/100,2).'元';
				$status =  $this->_status[$status];
			}elseif($type == 8){
				$KtvOrder = D('KtvOrder')->find($order_id);
			    $Ktv = D('Ktv')->find($KtvOrder['shop_id']); 
			    $KtvRoom = D('KtvRoom')->find($KtvOrder['room_id']); 
				$user_id = $this->getUserId($cate,$KtvOrder['user_id'],$KtvOrder['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = $KtvRoom['title'];
				$price = round($KtvOrder['price']/100,2).'元';
				$status =  $this->_status[$status];
			}elseif($type == 9){
				$Marketorder = D('Marketorder')->where(array('order_id'=>$order_id))->find();
				$user_id = $this->getUserId($cate,$Marketorder['user_id'],$Marketorder['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = D('Marketorder')->get_market_order_product_name($order_id);
				$price = round($Marketorder['need_pay']/100,2).'元';
				$status =  $this->_status[$status];
			}elseif($type == 10){
				$Storeorder = D('Storeorder')->where(array('order_id'=>$order_id))->find();
				$user_id = $this->getUserId($cate,$Storeorder['user_id'],$Storeorder['shop_id']);
				$first = $this->_type[$type].''.$this->_status[$status];
				$url = $this->getUrl($order_id,$cate,$type);
				$title = D('Storeorder')->get_store_order_product_name($order_id);
				$price = round($Storeorder['need_pay']/100,2).'元';
				$status =  $this->_status[$status];
			}
            include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $data = array(
				'url' => $url, 
				'first' => $first, 
				'remark' => $remark, 
				'title' => $title, //商品名称
				'price' => $price , //价格
				'status' => $status,//状态
			);
            $_data = Wxmesg::order_message($data);
            Wxmesg::net($user_id,'OPENTM205109409', $_data);
			return true;
    }
	//获取发送微信模板消息的主体
	public function getUserId($cate,$user_id,$shop_id){
		if($cate == 1){
			return $user_id;
		}elseif($cate == 2){
			$detail = D('Shop')->where(array('shop_id'=>$shop_id))->find();
			return $detail['user_id'];
		}else{
			return 1;
		}
	}
	//获取订单支付信息的URL
	public function getUrl($order_id,$cate,$type){
		$config = D('Setting')->fetchAll();
		if($type == 1){
			if($cate == 1){
				return $config['site']['host'] . '/user/eleorder/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/ele/eleorder';
			}
		}elseif($type == 2){
			if($cate == 1){
				return $config['site']['host'] . '/user/goods/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/mart/order.html';
			}
		}elseif($type == 3){
			if($cate == 1){
				return $config['site']['host'] . '/user/appoint/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/';
			}
		}elseif($type == 4){
			if($cate == 1){
				return $config['site']['host'] . '/user/tuan/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/tuan/order';
			}
		}elseif($type == 5){//农家乐
			if($cate == 1){
				return $config['site']['host'] . '/user/farm/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/';
			}
		}elseif($type == 6){
			if($cate == 1){
				return $config['site']['host'] . '/user/hotels/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/';
			}
		}elseif($type == 7){
			if($cate == 1){
				return $config['site']['host'] . '/user/booking/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/';
			}
		}elseif($type == 8){
			if($cate == 1){
				return $config['site']['host'] . '/user/ktv/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/ktv/index';
			}
		}elseif($type == 9){
			if($cate == 1){
				return $config['site']['host'] . '/user/market/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/market/index';
			}
		}elseif($type == 10){
			if($cate == 1){
				return $config['site']['host'] . '/user/store/detail/order_id/' . $order_id . '.html';
			}else{
				return $config['site']['host'] . '/seller/store/index';
			}
		}
		
	}

	
		
	//分类信息推送
    public function weixin_tmpl_life_subscribe($detail,$user_id){
		$config = D('Setting')->fetchAll();
			$Users = D('Users')->find($user_id);
			$cates = D('Lifecate')->fetchAll();
			include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $data = array(
				'url' => $config['site']['host'] . '/wap/life/detail/'.$detail['life_id'].'/', 
				'first' => '分类信息推送', 
				'user_demand' => $cates[$detail['cate_id']]['cate_name'], //客户要求
				'user_name' => $Users['nickname'] , //客户名称
				'time' => date('Y-m-d H:i:s ', $detail['create_time']),//提出时间
				'remark' => '根据您的需求匹配：信息标题'.$detail['title'].'请点击下面的连接查看', 
			);
            $_data = Wxmesg::subscribe($data);
            Wxmesg::net($user_id,'OPENTM207467627', $_data);
			return true;
    }
	
	//商家新闻推送
    public function weixin_shop_news_push($detail,$user_id){
		$config = D('Setting')->fetchAll();
			$Users = D('Users')->find($user_id);
			include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $data = array(
				'url' => $config['site']['host'] . '/wap/shop/news_detail/'.$detail['news_id'].'/', 
				'first' => '商家新闻推送', 
				'user_demand' => $detail['title'], //客户要求
				'user_name' => $Users['nickname'] , //客户名称
				'time' => date('Y-m-d H:i:s ', $detail['create_time']),//提出时间
				'remark' => '作者'.$detail['source'].'给你推荐的新文章，标题'.$detail['title'].'请点击下面的连接查看', 
			);
            $_data = Wxmesg::subscribe($data);
            Wxmesg::net($user_id,'OPENTM207467627', $_data);
			return true;
    }
	
	
	//后台微信模板消息推送
    public function weixin_admin_push($detail,$user_id){
		$config = D('Setting')->fetchAll();
			$Users = D('Users')->find($user_id);
			include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $data = array(
				'url' => $detail['url'], 
				'first' => '推送消息', 
				'user_demand' => $detail['title'], //推送标题
				'user_name' => $Users['nickname'] , //推送昵称
				'time' => date('Y-m-d H:i:s ', $detail['create_time']),//推送时间
				'remark' => niuMsubstr($detail['intro'],0,38,false).'请点击下面的连接查看', 
			);
            $_data = Wxmesg::subscribe($data);
            Wxmesg::net($user_id,'OPENTM207467627', $_data);
			return true;
    }
	
	
	
	//会员Capital账户变动通知
	//$type  1余额，2积分，3商户资金，4威望
    public function weixinTmplCapital($type,$user_id,$capital,$intro){
		    $config = D('Setting')->fetchAll();
			$first = $this->_Capital_Type[$type].'资金变动';
			$remark = $intro.'详情请登录：' . $config['site']['host'].'/user';
			$types = $this->_Capital_Type[$type];
			$time = date('Y-m-d H:i:s ', time());
			$url = $config['site']['host'] . '/user';
			if($type == 1){
				$capital = round($capital/100,2).'元';
			}elseif($type == 2){
				$capital = $capital.'分';
			}elseif($type == 3){
				$capital = round($capital/100,2).'元';
			}else{
				$capital = $capital.'威望';
			}
			include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $data = array(
				'url' => $url, 
				'first' => $first, 
				'remark' => $remark, 
				'types' =>  $types, //资金类型
				'capital' => $capital , //金额
				'time' => $time,//状态
			);
            $_data = $this->capital($data);
            $this->net($user_id,'OPENTM200465417', $_data);
			return true;
    }
	
	//为了微信关注注册，自动写这里的代码
	public function net($uid,$serial=null,$data=null){
		$uid = (int)$uid;
		$openid = D('Connect')->where("type='weixin'")->getFieldByUid($uid,'open_id'); 
		if($openid){
            $data['template_id'] = D('Weixintmpl')->getFieldBySerial($serial,'template_id');//支付成功模板
            $data['touser']  = $openid;
			$msg = array();
			$msg['user_id'] = $uid;
			$msg['open_id'] = $openid;
			$msg['serial'] = $serial;
			$msg['template_id'] = $data['template_id'];
			$html = '';
			foreach ($data['data'] as $v) {
				$html .= $v['value'].'<br>';
			}
			$msg['comment'] = $html;
			$msg['create_time'] = time();
			$msg['create_ip'] = get_client_ip();
			if($msg_id = D('Weixinmsg')->add($msg)){
				return D('Weixin')->tmplmesg($data,$msg_id);
			};
		}
		return true;
	}
	
	
	
	//资金变动组合数组
	public function capital($data=null){
		return array(
			'touser'  => '',
			'url'=> $data['url'],
			'template_id'  => '',
			'topcolor'=> $data['topcolor'],
			'data'=> array(
				'first'   =>array('value'=>$data['first'],   'color'=>'#000000'),
				'keyword1'=>array('value'=>$data['types'],   'color'=>'#000000'), 
				'keyword2'=>array('value'=>$data['capital'],'color'=>'#000000'), 
				'keyword3'=>array('value'=>$data['time'],    'color'=>'#000000'),
				'remark'  =>array('value'=>$data['remark'],  'color'=>'#000000')
			)
		);
	}	

	
	//循环获取三级推荐人ID
	public function array_list($user_id){
		$lists = D('Users')->find($user_id);
		$list = array();
		$list['fuid1'] = $lists['fuid1'];
		$list['fuid2'] = $lists['fuid2'];
		$list['fuid3'] = $lists['fuid3'];
		$list = array_filter($list);
		return $list;
		
	}
	
	//微信扫码注册通知推荐人
    public function profit_register_weixin_tpl($user_id,$nickname){
		$config = D('Setting')->fetchAll();
			$list = $this->array_list($user_id);//获取会员上级数组
			$i = 0;
			foreach ($list as $k => $v){ 
				$i++;
				$detail = D('Users')->find($v);
				if($detail['user_id']){
					include_once 'Tudou/Lib/Net/Wxmesg.class.php';
					$data = array(
						'url' => $config['site']['host'] . '/user/distribution/subordinate.html', 
						'first' => '尊敬的【'.$detail['nickname'].'】您有【'.$i.'】代下级成功注册', 
						'keyword1' => '下级昵称：'.$nickname, //下级昵称
						'keyword2' => '下级ID：'.$user_id , //下级ID
						'keyword3' => '时间:'.date('Y-m-d H:i:s',time()),//时间
						'remark' => ' 访问【http://' . $_SERVER['HTTP_HOST'] . '/user】', 
					);
					$_data = $this->profit_register($data);
					$this->net($detail['user_id'],'OPENTM401095581', $_data);
				}
			}
			return true;
    }
	
	//分销邀请注册批量推送组合数组
	public function profit_register($data=null){
		return array(
			'touser'  => '',
			'url'=> $data['url'],
			'template_id'  => '',
			'topcolor'=> $data['topcolor'],
			'data'=> array(
				'first'   =>array('value'=>$data['first'],   'color'=>'#000000'),
				'keyword1'=>array('value'=>$data['keyword1'],   'color'=>'#000000'), 
				'keyword2'=>array('value'=>$data['keyword2'],'color'=>'#000000'), 
				'keyword3'=>array('value'=>$data['keyword3'],    'color'=>'#000000'),
				'remark'  =>array('value'=>$data['remark'],  'color'=>'#000000')
			)
		);
	}			
	
}