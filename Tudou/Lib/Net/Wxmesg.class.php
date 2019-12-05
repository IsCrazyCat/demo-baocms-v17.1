<?php 
class Wxmesg{
	/**
	 * 网络发送数据
	 * @param string $uid,用户的openid
	 * @param string $serial,模板编号
	 * @param array  $data ,填充模板数据
	 */
	static public function net($uid,$serial=null,$data=null){
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
			return true;//忽略报错
		}
		return true;//忽略报错
	}
	//下单成功模板
	static public function order($data=null){
		return array(
			'touser' => '',
			'url'=> $data['url'],
			'template_id' => '',
			'topcolor' => $data['topcolor'],
			'data' => array(
				'first'   =>array('value'=>	$data['first'],    'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['orderNum'], 'color'=>'#000000'), //订单号
				'keyword2'=>array('value'=> $data['goodsName'],'color'=>'#000000'), //商品名称
				'keyword3'=>array('value'=> $data['buyNum'],   'color'=>'#000000'), //订购数量
				'keyword4'=>array('value'=> $data['money'],    'color'=>'#000000'), //订单金额
				'keyword5'=>array('value'=> $data['payType'],  'color'=>'#000000'), //付款方式
				'remark'  =>array('value'=> $data['remark'],   'color'=>'#000000')
			)
		);
	}
	//支付成功调用全局通用，小灰灰修改
	static public function pay($data=null){
		return array(
			'touser' => '',
			'url'  => $data['url'],
			'template_id'  => '',
			'topcolor' => $data['topcolor'],
			'data'	 => array(
				'first'   =>array('value'=>$data['first'],   'color'=>'#000000'),
				'keyword1'=>array('value'=>$data['accountType'],   'color'=>'#000000'), //会员账户
				'keyword2'=>array('value'=>$data['operateType'],'color'=>'#000000'), //费用类型
				'keyword3'=>array('value'=>$data['operateInfo'],    'color'=>'#000000'), //消费类型
				'keyword4'=>array('value'=>$data['price'],'color'=>'#000000'), //变动金额
				'remark'  =>array('value'=>$data['balance'],  'color'=>'#000000')//会员账户余额
			)
		);
	}
	
	//会员提现余额变动全部封装，哈土豆源码开发
	static public function cash($data=null){
		return array(
			'touser'  => '',
			'url' => $data['url'],
			'template_id'  => '',
			'topcolor' => $data['topcolor'],
			'data' => array(
				'first'   =>array('value'=>$data['first'],   'color'=>'#000000'),
				'keyword1'=>array('value'=>$data['balance'],   'color'=>'#000000'), //余额
				'keyword2'=>array('value'=>$data['time'],'color'=>'#000000'), //时间
				'remark'  =>array('value'=>$data['remark'],  'color'=>'#000000')
			)
		);
	}
	
	//余额变动
	static public function balance($data=null){
		return array(
			'touser' => '',
			'url'  => $data['url'],
			'template_id'  => '',
			'topcolor'  => $data['topcolor'],
			'data' => array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['accountType'], 'color'=>'#000000'), //账户类型
				'keyword2'=>array('value'=> $data['operateType'], 'color'=>'#000000'), //操作类型
				'keyword3'=>array('value'=> $data['operateInfo'], 'color'=>'#000000'), //操作内容
				'keyword4'=>array('value'=> $data['limit'],       'color'=>'#000000'), //变动额度
				'keyword5'=>array('value'=> $data['balance'],     'color'=>'#000000'), //账户余额
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}
	/*订单通知模板*/
	static public function notice($data=null){
		return array(
			'touser'=> '',
			'url'=> $data['url'],
			'template_id'=> '',
			'topcolor'=> '#000000',
			'data' => array(
				'first'   => array( 'value'=> $data['first'],  'color'=>'#000000' ),
				'keyword1'=> array( 'value'=> $data['order'],  'color'=>'#000000' ),//订单号
				'keyword2'=> array( 'value'=> $data['amount'], 'color'=>'#000000' ),//订单金额
				'keyword3'=> array( 'value'=> $data['info'],   'color'=>'#000000' ),//商品信息
				'remark'  => array( 'value'=> $data['remark'], 'color'=>'#000000' )
			)
		);
	}
	
	//下单成功模板重写，小灰灰重新编写，模板IDOPENTM202297555
	static public function place_an_order($data=null){
		return array(
			'touser' => '',
			'url' => $data['url'],
			'template_id' => '',
			'topcolor'=> '#000000',
			'data'=> array(
				'first'   => array( 'value'=> $data['first'],  'color'=>'#000000' ),
				'keyword1'=> array( 'value'=> $data['order_id'],  'color'=>'#000000' ),//订单号
				'keyword2'=> array( 'value'=> $data['title'], 'color'=>'#000000' ),//商品名称
				'keyword3'=> array( 'value'=> $data['num'],   'color'=>'#000000' ),//订购数量
				'keyword4'=> array( 'value'=> $data['price'],   'color'=>'#000000' ),//订单总额
				'keyword5'=> array( 'value'=> $data['pay_type'],   'color'=>'#000000' ),//付款方式
				'remark'  => array( 'value'=> $data['remark'], 'color'=>'#000000' )
			)
		);
	}
	
	//用户付款后订单通知商家
	static public function order_notice_shop($data=null){
		return array(
			'touser' => '',
			'url' => $data['url'],
			'template_id' => '',
			'topcolor'=> '#000000',
			'data'  => array(
				'first'   => array( 'value'=> $data['first'],  'color'=>'#000000' ),
				'keyword1'=> array( 'value'=> $data['order_id'],  'color'=>'#000000' ),
				'keyword2'=> array( 'value'=> $data['order_goods'], 'color'=>'#000000' ),
				'keyword3'=> array( 'value'=> $data['order_price'],   'color'=>'#000000' ),
				'keyword4'=> array( 'value'=> $data['order_ways'],   'color'=>'#000000' ),
				'keyword5'=> array( 'value'=> $data['order_user_information'],   'color'=>'#000000' ),
				'remark'  => array( 'value'=> $data['remark'], 'color'=>'#000000' )
			)
		);
	}
	
	//客户预约成功通知商家

	static public function yuyue($data=null){
		return array(
			'touser' => '',
			'url' => $data['url'],
			'template_id' => '',
			'topcolor'  => $data['topcolor'],
			'data'=> array(
				'first'   =>array('value'=>	$data['first'],    'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['remark'], 'color'=>'#000000'), //订单号
				'keyword2'=>array('value'=> $data['name'],'color'=>'#000000'), //预约人名字
				'keyword3'=>array('value'=> $data['date'],   'color'=>'#000000'), //时间
				'keyword4'=>array('value'=> $data['tel'],    'color'=>'#000000'), //电话
				'keyword5'=>array('value'=> $data['contents'],  'color'=>'#000000') //内容
			)
		);
	}
	
	
	//开团提醒二开到下面结束
	static public function kaituan($data=null){
		return array(
			'touser' => '',
			'url' => $data['url'],
			'template_id' => '',
			'topcolor' => $data['topcolor'],
			'data'	=> array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['goodsName'], 'color'=>'#000000'), //商品名称
				'keyword2'=>array('value'=> $data['orderno'], 'color'=>'#000000'), //订单编号
				'keyword3'=>array('value'=> $data['pintuannum'], 'color'=>'#000000'), //拼团人数
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}

	//参团成功通知
	static public function cantuan($data=null){
		return array(
			'touser'=> '',
			'url' => $data['url'],
			'template_id' => '',
			'topcolor'=> $data['topcolor'],
			'data'=> array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['payprice'], 'color'=>'#000000'), //订单金额
				'keyword2'=>array('value'=> $data['goodsName'], 'color'=>'#000000'), //商品名称
				'keyword3'=>array('value'=> $data['dizhi'], 'color'=>'#000000'), //收货地址
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}

	//用户拼团成功通知
	static public function ctsuccess($data=null){
		return array(
			'touser'=> '',
			'url' => $data['url'],
			'template_id' => '',
			'topcolor' => $data['topcolor'],
			'data' => array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['payprice'], 'color'=>'#000000'), //商品名称
				'keyword2'=>array('value'=> $data['orderno'], 'color'=>'#000000'), //订单编号
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}

	//拼团失败通知
	static public function ctover($data=null){
		return array(
			'touser'  => '',
			'url'  => $data['url'],
			'template_id'  => '',
			'topcolor'=> $data['topcolor'],
			'data'=> array(
				'first'  =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['payprice'], 'color'=>'#000000'), //订单金额
				'keyword2'=>array('value'=> $data['goodsName'], 'color'=>'#000000'), //商品名称
				'keyword3'=>array('value'=> $data['orderno'], 'color'=>'#000000'), //订单编号
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}
	//拼团退款通知
	static public function cttuikuan($data=null){
		return array(
			'touser'  => '',
			'url'  => $data['url'],
			'template_id'  => '',
			'topcolor'=> $data['topcolor'],
			'data' => array(
				'first' =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['yuanyin'], 'color'=>'#000000'), //退款原因
				'keyword2'=>array('value'=> $data['payprice'], 'color'=>'#000000'), //退款金额
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}
	//拼团发货通知
	static public function fahuo($data=null){
		return array(
			'touser' => '',
			'url'  => $data['url'],
			'template_id' => '',
			'topcolor' => $data['topcolor'],
			'data'	=> array(
				'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
				'keyword1'=>array('value'=> $data['goodsName'], 'color'=>'#000000'), //商品名称
				'keyword2'=>array('value'=> $data['kuaidi'], 'color'=>'#000000'), //快递名称
				'keyword3'=>array('value'=> $data['kuaididanhao'], 'color'=>'#000000'), //快递名称
				'keyword4'=>array('value'=> $data['dizhi'],       'color'=>'#000000'), //收货地址
				'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
			)
		);
	}	
	//物业
        static public function wuyetz($data=null){
                return array(
                        'touser' => '',
                        'url'  => $data['url'],
                        'template_id' => '',
                        'topcolor' => $data['topcolor'],
                        'data'=> array(
                                'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
                                'keyword1'=>array('value'=> $data['nickname'], 'color'=>'#000000'), //账户类型
                                'keyword2'=>array('value'=> $data['title'], 'color'=>'#000000'), //操作类型
                                'keyword3'=>array('value'=> $data['nowtime'], 'color'=>'#000000'), //操作类型
                                'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
                        )
                );
        }
        //物业通知
        static public function wuyexttz($data=null){
                return array(
                        'touser' => '',
                        'url' => $data['url'],
                        'template_id'  => '',
                        'topcolor' => $data['topcolor'],
                        'data' => array(
                                'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
                                'keyword1'=>array('value'=> $data['nickname'], 'color'=>'#000000'), //账户
                                'keyword2'=>array('value'=> $data['title'], 'color'=>'#000000'), //操作类型
                                'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
                        )
                );
        }	
		//全局推送到配送员
        static public function delivery_tz_user($data=null){
                return array(
                        'touser'  => '',
                        'url' => $data['url'],
                        'template_id' => '',
                        'topcolor' => $data['topcolor'],
                        'data'=> array(
                        'first'  =>array('value'=> $data['first'],       'color'=>'#000000'),
                        'keyword1'=>array('value'=> $data['nickname'], 'color'=>'#000000'), //账户
                        'keyword2'=>array('value'=> $data['title'], 'color'=>'#000000'), //操作类型
                        'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
                      )
                );
        }
		
		//配送员抢单通知用户
        static public function delivery_qiang_tz_user($data=null){
                return array(
                        'touser' => '',
                        'url' => $data['url'],
                        'template_id' => '',
                        'topcolor' => $data['topcolor'],
                        'data' => array(
                        'first'   =>array('value'=> $data['first'],       'color'=>'#000000'),
                        'keyword1'=>array('value'=> $data['order_name'], 'color'=>'#000000'), //账户
                        'keyword2'=>array('value'=> $data['order_id'], 'color'=>'#000000'), //操作类型
						'keyword3'=>array('value'=> $data['delivery_user_name'], 'color'=>'#000000'), //账户
                        'keyword4'=>array('value'=> $data['delivery_user_mobile'], 'color'=>'#000000'), //操作类型
                        'remark'  =>array('value'=> $data['remark'],      'color'=>'#000000')
                      )
                );
        }	
			
		//后台全局推送
        static public function tuisongweixin($data=null){
                return array(
                        'touser'  => '',
                        'url' => $data['url'],
                        'template_id'  => '',
                        'topcolor' => $data['topcolor'],
                        'data'  => array(
                                'first'   =>array('value'=> $data['first'],    'color'=>'#000000'),
                                'keyword1'=>array('value'=> $data['nickname'], 'color'=>'#000000'), //账户
                                'keyword2'=>array('value'=> $data['title'], 'color'=>'#000000'), //操作类型
                                'remark'  =>array('value'=> $data['remark'], 'color'=>'#000000')
                        )
                );
        }	
		
	//订单状态通知
	static public function order_message($data=null){
		return array(
			'touser' => '',
			'url'  => $data['url'],
			'template_id' => '',
			'topcolor' => $data['topcolor'],
			'data'=> array(
				'first'   =>array('value'=>$data['first'],   'color'=>'#000000'),
				'keyword1'=>array('value'=>$data['title'],   'color'=>'#000000'), 
				'keyword2'=>array('value'=>$data['price'],'color'=>'#000000'), 
				'keyword3'=>array('value'=>$data['status'],    'color'=>'#000000'),
				'remark'  =>array('value'=>$data['remark'],  'color'=>'#000000')
			)
		);
	}	
	
	//资金变动
	static public function capital($data=null){
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
	
	//分类信息推送
	static public function subscribe($data=null){
		return array(
			'touser'  => '',
			'url'=> $data['url'],
			'template_id'  => '',
			'topcolor'=> $data['topcolor'],
			'data'=> array(
				'first'   =>array('value'=>$data['first'],   'color'=>'#000000'),
				'keyword1'=>array('value'=>$data['user_demand'],   'color'=>'#000000'), 
				'keyword2'=>array('value'=>$data['user_name'],'color'=>'#000000'), 
				'keyword3'=>array('value'=>$data['time'],    'color'=>'#000000'),
				'remark'  =>array('value'=>$data['remark'],  'color'=>'#000000')
			)
		);
	}	
	
	
	//分销邀请注册批量推送
	static public function profit_register($data=null){
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