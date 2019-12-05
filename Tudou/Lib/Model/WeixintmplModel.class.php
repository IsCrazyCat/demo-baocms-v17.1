<?php
class WeixintmplModel extends CommonModel{
	protected $pk   = 'tmpl_id';
    protected $tableName =  'weixin_tmpl';
	protected $_validate = array(
		array('title','2,20','模板标题2至10个字符！',Model::MUST_VALIDATE, 'length', Model::MODEL_BOTH),
		array('serial','/^\w{3,}$/','请输入正确的模板库编号！',Model::MUST_VALIDATE, 'regex', Model::MODEL_BOTH),
		array('status','0,1','状态值不合法,必须0或1！',Model::MUST_VALIDATE, 'in', Model::MODEL_BOTH),
		array('sort','/^\d{1,4}$/','排序值不合法！',Model::MUST_VALIDATE, 'regex', Model::MODEL_BOTH),
	);
	
	
	
	
	//抢单配送批量发送微信模板消息
	public function delivery_tz_user($order_id,$type){
		
		$config = D('Setting')->fetchAll();
		
		$order_id = $order_id;
		$type = (int) $type;//0是商城，1是外卖，2是快递
		if($type == 0){
			$obj = D('Order');
			$info = '商城';
		}elseif($type == 1){
			$obj = D('Eleorder');
			$info = '外卖';
		}elseif($type == 3){
			$obj = D('Marketorder');
			$info = '菜市场';
		}elseif($type == 4){
			$obj = D('Storeorder');
			$info = '便利店';
		}else{
			$obj = D('Express');
			$info = '快递';
		}
		
		$detail = $obj->find($order_id);
		
		$time = date("Y-m-d H:i:s",time()); //订单时间
		$delivery  = D('Delivery')->select();
		
		

		
        foreach ($delivery as $v=>$val)  { 
            include_once "Tudou/Lib/Net/Wxmesg.class.php";
            $_delivery_tz_user = array(//整体变更
                'url'       =>  $config['site']['host']."/delivery/lists/scraped.html",
                'topcolor'  =>  '#F55555',
                'first' => '您好：【'.$val['name'].'】，抢单中心有新的'.$typed.'订单',
                'remark' => '请尽快前去抢单，不然就被别人捷足先登了哦！',
                'nickname'  =>  $detail['order_id'],
                'title'     =>  $time,

         );
		 
         $delivery_tz_user_data = Wxmesg::delivery_tz_user($_delivery_tz_user);
         $return = Wxmesg::net($val['user_id'], 'OPENTM400045127', $delivery_tz_user_data);//结束
       } 
        return true;
    }
	
	
	
	//配送员抢单通知用户
	public function delivery_qiang_tz_user($order_id,$delivery_id,$type,$status){
		$config = D('Setting')->fetchAll();
		$order_id = (int) ($order_id);
		$type = (int) $type;//0是商城，1是外卖，2是快递
		$status = (int) $status;//0是商城，1是外卖，2是快递
		if($status == 0){
			$status = '您的订单已被抢单了' ;
		}else{
			$status = '您的订单已被配送员设置为已完成' ;
		}
		$config_site_url = $config['site']['host'] . '/user/';
		$detail = D('DeliveryOrder')->where(array('order_id'=>$order_id))->find();//配送订单信息
		$delivery = D('Delivery')->where(array('user_id'=>$delivery_id))->find();//筛选配送员信息
		if($type == 0){
			$url = $config_site_url.'goods/detail/order_id/'.$detail['type_order_id'].'/';
			$order_name = D('Ordergoods')->get_mall_order_goods_name($detail['type_order_id']);
			$order_id = $detail['type_order_id'];
		}elseif($type == 1){
			$url = $config_site_url.'eleorder/detail/order_id/'.$detail['type_order_id'].'/';
			$order_name = D('Eleorder')->get_ele_order_product_name($detail['type_order_id']);
			$order_id = $detail['type_order_id'];
		}elseif($type == 3){
			$url = $config_site_url.'market/detail/order_id/'.$detail['type_order_id'].'/';
			$order_name = D('Marketorder')->get_market_order_product_name($detail['type_order_id']);
			$order_id = $detail['type_order_id'];
		}elseif($type == 4){
			$url = $config_site_url.'store/detail/order_id/'.$detail['type_order_id'].'/';
			$order_name = D('Storeorder')->get_store_order_product_name($detail['type_order_id']);
			$order_id = $detail['type_order_id'];
		}
		
		$config = D('Setting')->fetchAll();
        include_once "Tudou/Lib/Net/Wxmesg.class.php";
        $_data = array(//整体变更
                'url'       => $url,
                'topcolor'  => '#F55555',
                'first'     => $status,
                'remark'    => '更多信息,请登录'.$config['site']['sitename'].',将为您提供更多信息服务！',
                'order_name'=> $order_name, //商品名称
				'order_id' => $order_id, //订单ID
				'delivery_user_name' => $delivery['name'],//配送员姓名
				'delivery_user_mobile' => $delivery['mobile'], //配送员电话
         );
         $data = Wxmesg::delivery_qiang_tz_user($_data);
         $return = Wxmesg::net($detail['user_id'], 'OPENTM406590003',$data);//结束
        return true;
    }
	
	
	
	//抢购下单微信通知
    public function weixin_notice_tuan_user($order_id,$user_id,$type){
		
		$config = D('Setting')->fetchAll();
		
		
            $Tuanorder = D('Tuanorder')->where(array('order_id'=>$order_id))->find();
		    $Tuan = D('Tuan')->find($Tuanorder['tuan_id']);
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
            include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $notice_data = array(
				'url' => $config['site']['host'] . '/user/tuan/detail/order_id/' . $order_id . '.html', 
				'first' => '亲,您的抢购订单创建成功!', 
				'remark' => '详情请登录-' . $config['site']['host'], 
				'order_id' => $order_id, 
				'title' => $Tuan['title'], 
				'num' => $Tuanorder['num'],
				'price' => round($Tuanorder['need_pay'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
			);
			
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id, 'OPENTM202297555', $notice_data);
			return true;
    }
	
	//商城下单微信通知
    public function weixin_notice_goods_user($order_id,$user_id,$type){
		
		$config = D('Setting')->fetchAll();
		
		
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$Order = D('Order')->find($order_id);
			$num = D('Ordergoods')->where(array('order_id'=>$order_id))->sum('num');
			$goods_name = D('Ordergoods')->get_mall_order_goods_name($order_id);//获取商城订单名称
            include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $notice_data = array(
				'url' => $config['site']['host'] . '/user/goods/index/aready/' . $order_id . '.html', 
				'first' => '亲,您的商城订单创建成功!', 
				'remark' => '详情请登录-' . $config['site']['host'], 
				'order_id' => $order_id, 
				'title' => $goods_name, 
				'num' => $num,
				'price' => round($Order['need_pay'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
			);
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id,'OPENTM202297555', $notice_data);
			return true;
    }
	
	//订座下单微信通知
    public function weixin_notice_booking_user($order_id,$user_id,$type){
		
		$config = D('Setting')->fetchAll();
		
		
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$Bookingorder = D('Bookingorder')->find($order_id);
			$Booking = D('Booking')->find($Bookingorder['shop_id']);
			
            include_once "Tudou/Lib/Net/Wxmesg.class.php";
            $notice_data = array(
                'url'       =>  $config['site']['host']."/user/booking/detail/order_id/".$order_id.".html",
                'first'   => '亲,您的订座订单创建成功!',
                'remark' => '详情请登录-' . $config['site']['host'], 
				'order_id' => $order_id, 
				'title' => $Booking['shop_name'], 
				'num' => '1',
				'price' => round($Bookingorder['amount'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
            );
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id, 'OPENTM202297555', $notice_data);
			return true;
    }
	
	//酒店下单微信通知
    public function weixin_notice_hotel_user($order_id,$user_id,$type){
		
		$config = D('Setting')->fetchAll();
		
		
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$Hotelorder = D('Hotelorder')->find($order_id);
			$Hotel = D('Hotel')->find($Hotelorder['hotel_id']);
            include_once "Tudou/Lib/Net/Wxmesg.class.php";
            $notice_data = array(
                'url'       =>  $config['site']['host']."/user/hotels/detail/order_id/".$order_id.".html",
                'first'   => '亲,您的酒店订单创建成功!',
                'remark' => '详情请登录-' . $config['site']['host'], 
				'order_id' => $order_id, 
				'title' => $Hotel['hotel_name'], 
				'num' => '1',
				'price' => $Hotelorder['amount']. '元', 
				'pay_type' => $pay_type 
            );
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id, 'OPENTM202297555', $notice_data);
			return true;
    }
	
	//农家乐下单微信通知
    public function weixin_notice_farm_user($order_id,$user_id,$type){
		$config = D('Setting')->fetchAll();
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$Farmgorder = D('Farmorder')->find($order_id);
			$Farm = D('Farm')->find($Farmorder['farm_id']);
            include_once "Tudou/Lib/Net/Wxmesg.class.php";
            $notice_data = array(
                'url'       =>  $config['site']['host']."/user/farm/detail/order_id/".$order_id.".html",
                'first'   => '亲,您的农家乐订单创建成功!',
                'remark' => '详情请登录-' . $config['site']['host'], 
				'order_id' => $order_id, 
				'title' => $Farm['farm_name'], 
				'num' => '1',
				'price' => $Farmorder['amount'] . '元', 
				'pay_type' => $pay_type 
            );
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id, 'OPENTM202297555', $notice_data);
			return true;
    }
	
	//外卖下单微信通知
    public function weixin_notice_ele_user($order_id,$user_id,$type){
		
		$config = D('Setting')->fetchAll();
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$order = D('Eleorder')->find($order_id);
            $product_name = D('Eleorder')->get_ele_order_product_name($order_id);
            include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $notice_data = array(
				'url' => $config['site']['host'] . '/user/eleorder/detail/order_id/' . $order_id . '.html', 
				'first' => '亲,您的外卖订单创建成功!', 
				'remark' => '详情请登录-' . $config['site']['host'] ,
				'order_id' => $order_id, 
				'title' => $product_name, 
				'num' => $order['num'],
				'price' => round($order['need_pay'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
			);
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id,'OPENTM202297555', $notice_data);
			return true;
    }
	
	//菜市场下单微信通知
    public function weixin_notice_market_user($order_id,$user_id,$type){
		
		$config = D('Setting')->fetchAll();
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$order = D('Marketorder')->find($order_id);
            $product_name = D('Marketorder')->get_market_order_product_name($order_id);
            include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $notice_data = array(
				'url' => $config['site']['host'] . '/user/market/detail/order_id/' . $order_id . '.html', 
				'first' => '亲,您的菜市场订单创建成功!', 
				'remark' => '详情请登录-' . $config['site']['host'], 
				'order_id' => $order_id, 
				'title' => $product_name, 
				'num' => $order['num'],
				'price' => round($order['need_pay'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
			);
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id,'OPENTM202297555', $notice_data);
			return true;
    }
	
	//便利店下单微信通知
    public function weixin_notice_store_user($order_id,$user_id,$type){
		
		$config = D('Setting')->fetchAll();
		
			if($type == 0){
				$pay_type = '货到付款' ;
			}else{
				$pay_type = '在线支付' ;
			}
			$order = D('Storeorder')->find($order_id);
            $product_name = D('Storeorder')->get_store_order_product_name($order_id);
            include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $notice_data = array(
				'url' => $config['site']['host'] . '/user/store/detail/order_id/' . $order_id . '.html', 
				'first' => '亲,您的菜市场订单创建成功!', 
				'remark' => '详情请登录-' . $config['site']['host'], 
				'order_id' => $order_id, 
				'title' => $product_name, 
				'num' => $order['num'],
				'price' => round($order['need_pay'] / 100, 2) . '元', 
				'pay_type' => $pay_type 
			);
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id,'OPENTM202297555', $notice_data);
			return true;
    }
	
	
	
	//会员提现，审核，拒绝，通知会员自己
 	 public function weixin_cash_user($user_id,$tpye){
		$config = D('Setting')->fetchAll();
		if($tpye ==1){
			$tpye_name = '您已经成功申请提现'; 
		}elseif($tpye ==2){
			$tpye_name = '您的提现已通过审核'; 
		}elseif($tpye ==3){
			$tpye_name = '您的提现被拒绝，请关注您的账户'; 
		}
		$Users = D('Users')->find($user_id);
		$t = time(); 
        include_once "Tudou/Lib/Net/Wxmesg.class.php";
        $_cash_data = array(
             'url'       =>  $config['site']['host']."/user/",
             'first'   => $tpye_name,
             'remark'  => '详情请登录-'.$config['site']['host'],
             'balance'  => '您的余额：'.round($Users['money']/100,2).'元',
             'time'   => '操作时间：'.$t,
          );
         $cash_data = Wxmesg::cash($_cash_data);
	      Wxmesg::net($user_id, 'OPENTM206909003', $cash_data);
		return true;
	}
	
	//积分兑换通知买家
    public function weixin_notice_jifen_user($exchange_id,$user_id){
		
		$config = D('Setting')->fetchAll();
		
            $detail = D('Integralexchange')->find($exchange_id);
			$goods = D('Integralgoods')->find($detail['goods_id']);
            include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $notice_data = array(
				'url' => $config['site']['host'] . '/user/exchange/index.html', 
				'first' => '亲,您成功兑换积分商品!', 
				'remark' => '详情请登录-' . $config['site']['host'], 'order_id' => $exchange_id, 
				'title' => $goods['title'], 
				'num' => '1',
				'price' => round($goods['price'] / 100, 2) . '分', 
				'pay_type' => '积分兑换' 
			);
            $notice_data = Wxmesg::place_an_order($notice_data);
            Wxmesg::net($user_id, 'OPENTM202297555', $notice_data);
			return true;
    }
	
	//积分兑换通知商家
    public function weixin_notice_jifen_shop($exchange_id,$user_id){
		$config = D('Setting')->fetchAll();
           $detail = D('Integralexchange')->find($exchange_id);
		   $Shop = D('Shop')->find($detail['shop_id']);
		   $goods = D('Integralgoods')->find($detail['goods_id']);
           include_once "Tudou/Lib/Net/Wxmesg.class.php";
           $_data_order_notice = array(
				'url' => $config['site']['host'] . '/seller/', 
				'topcolor' => '#F55555', 
				'first' => '积分兑换商品通知', 
				'remark' => '尊敬的【'.$Shop['shop_name'].'】，您有一笔新兑换订单！', 
				'order_id' => $exchange_id, 
				'order_goods' => $goods['title'], 
				'order_price' => round($goods['price'] / 100, 2) . '分', 
				'order_ways' => '积分兑换', 
				'order_user_information' => '兑换人信息登录管理中心查看'
			);
            $order_notice = Wxmesg::order_notice_shop($_data_order_notice);
            $return = Wxmesg::net($Shop['user_id'], 'OPENTM401973756', $order_notice);
			return true;
    }
	
	//客户预约商家微信通知OPENTM206305152，1通知客户，2通知商家
    public function weixin_yuyue_notice($yuyue_id,$type){
		$config = D('Setting')->fetchAll();
            $detail = D('Shopyuyue')->find($yuyue_id);
			$Shop = D('Shop')->find($detail['shop_id']);
			if($type == 1){
				$user_id = $detail['user_id'];
				$first = '恭喜您成功预约';
				$url = $config['site']['host'] . '/user/yuyue/index.html';
			}else{
				$user_id = $Shop['user_id'];
				$first = '您有新的预约订单';
				$url = $config['site']['host'] . '/seller/yuyue/index.html';
			}
			
            include_once 'Tudou/Lib/Net/Wxmesg.class.php';
            $yuyue_notice_data = array(
				'url' => $url, 
				'first' => $first, 
				'remark' => '详情请登录-' . $config['site']['host'], 
				'name' => $detail['name'], 
				'mobile' => $detail['mobile'], 
				'date' => $detail['yuyue_date'].'---'.$detail['yuyue_time'],
				'content' => $detail['content']
			);
            $_yuyue_notice_data = Wxmesg::yuyue($yuyue_notice_data);
            Wxmesg::net($user_id, 'OPENTM206305152', $_yuyue_notice_data);
			return true;
    }
	
	
	
	
	
	//推送微信模板消息红包
    public function pushRedPacketWeixinTmpl($life_id){
		    $config = D('Setting')->fetchAll();
			$detail = D('Life')->where(array('life_id'=>$life_id))->find();
			$LifePacket = D('LifePacket')->where(array('life_id'=>$detail['life_id'],'closed'=>0,'status'=>1))->find();
			$Users = D('Users')->find($user_id);
			include_once 'Tudou/Lib/Net/Wxmesg.class.php';
			$arr = D('Connect')->where(array('type'=>'weixin'))->select();//这里性能很差的
			foreach ($arr as $k => $v){ 
				$data = array(
					'url' => $config['site']['host'] . '/wap/life/index', 
					'first' =>  $Users['nickname'].'发布了福利前去看看吧', 
					'remark' => '点击进去咨询页面',  
					'keyword1' =>  $config['site']['sitename'], //咨询名称
					'keyword2' => date('Y-m-d H:i:s ', time()) ,//消息回复
				);
				$_data = $this->pushRedPacket($data);
				$this->net($v['uid'],'OPENTM202109783', $_data);
			}
			return true;
    }
	
	
	
	//发送微信模板消息
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
	
	
	
	//推送消息组合数组
	public function pushRedPacket($data=null){
		return array(
			'touser'  => '',
			'url'=> $data['url'],
			'template_id'  => '',
			'topcolor'=> $data['topcolor'],
			'data'=> array(
				'first'   =>array('value'=>$data['first'],   'color'=>'#000000'),
				'keyword1'=>array('value'=>$data['keyword1'],   'color'=>'#000000'), 
				'keyword2'=>array('value'=>$data['keyword2'],'color'=>'#000000'), 
				'remark'  =>array('value'=>$data['remark'],  'color'=>'#000000')
			)
		);
	}	
	

}