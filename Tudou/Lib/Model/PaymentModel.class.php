<?php

class PaymentModel extends CommonModel{
   protected $pk = 'payment_id';
    protected $tableName = 'payment';
    protected $token = 'payment';
    protected $types = array(
        'goods' => '商城购物',
        'appoint' => '家政购买',
        'tuan' => '生活购物',
        'money' => '余额充值',
        'ele' => '在线订餐',
        'booking'  => '订座定金',
        'hotel'=> '酒店订单',
        'breaks'=>'优惠买单',
		'pintuan' => '拼团',//拼团添加
		'crowd' =>'众筹',
		'donate' =>'打赏',
		'running'=>'跑腿',
		'farm'=>'农家乐预订',
		'cloud'=>'云购',
		'zhe'=>'五折卡',
		'life'=>'分类信息',
		'edu'=>'课程付款',
		'stock'=>'股权',
		'community' =>'小区缴费',
		'book'=>'服务预约',
		'ktv'=>'KTV',
		'market'=>'菜市场',
		'store'=>'便利店',
		'rank'=>'会员等级购买',
		'shop'=>'商家入驻',
    );

    protected $type = null;
    protected $log_id = null;
    public function getType(){
        return $this->type;
    }

    public function getLogId(){
        return $this->log_id;
    }

    public function getTypes(){
        return $this->types;
    }

    public function getPayments($mobile = false){
        $datas = $this->fetchAll();
        $return = array();
        foreach($datas as $val){
            if($val['is_open']){
                if ($mobile == false){
                    if (!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else{
                   if($val['code'] != 'tenpay' && $val['code'] != 'native' && $val['code'] != 'micro'){
                      $return[$val['code']] = $val;
                   }
                }
            }
        }
        if(!is_weixin()){
            unset($return['weixin']);
        }
        if(is_weixin()){
            unset($return['alipay']);
        }
        return $return;
    }

	//外卖关闭在线支付

	 public function getPayments_delivery($mobile = false){
        $datas = $this->fetchAll();
        $return = array();
        foreach($datas as $val){
            if($val['is_open']){
                if($mobile == false){
                    if(!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else{
                    if($val['code'] != 'tenpay'){
                        $return[$val['code']] = $val;
                    }
                }
            }
        }
        unset($return['money']);
		unset($return['tenpay']);
		unset($return['native']);
        unset($return['weixin']);
        unset($return['alipay']);
        return $return;
    }

	

	//订座关闭WAP扫码支付

	 public function getPayments_booking($mobile = false){
        $datas = $this->fetchAll();
        $return = array();
        foreach($datas as $val){
            if($val['is_open']){
                if($mobile == false){
                    if(!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else{
                    if($val['code'] != 'tenpay'){
                        $return[$val['code']] = $val;
                    }
                }
            }
        }
        if(!is_weixin()){
            unset($return['weixin']);
			unset($return['native']);
        }
        if(is_weixin()){
            unset($return['alipay']);
			unset($return['native']);
        }
        return $return;
    }

	//跑腿直接只能在线支付

	 public function getPayments_running($mobile = false){
        $datas = $this->fetchAll();
        $return = array();
        foreach($datas as $val){
            if($val['is_open']){
                if($mobile == false){
                    if(!$val['is_mobile_only'])
                        $return[$val['code']] = $val;
                }else{
                    if($val['code'] != 'tenpay'){
                        $return[$val['code']] = $val;
                    }
                }
            }
        }
        if(!is_weixin()){
            unset($return['weixin']);
			unset($return['native']);
        }
        if(is_weixin()){
            unset($return['alipay']);
			unset($return['native']);
        }
        return $return;
    }
	
    public function _format($data){
        $data['setting'] = unserialize($data['setting']);
        return $data;
    }	
	
	//全站回调	
	public function respond($code,$id = 0){
        $payment = $this->checkPayment($code);
        if(empty($payment))
            return false;
		if($code == 'native' || $code == 'micro' ){
			  require_cache( APP_PATH . 'Lib/Payment/' . $code . '.weixin' . '.class.php');//扫码支付
		}elseif(defined('IN_MOBILE')) {
            require_cache(APP_PATH . 'Lib/Payment/' . $code . '.mobile.class.php');
        }else{
            require_cache(APP_PATH . 'Lib/Payment/' . $code . '.class.php');
        }
        $obj = new $code();
        return $obj->respond($id);//传一个参数2018年4月新增这里的ID是日志ID
    }

	public function getCode($logs){
        $CONFIG = D('Setting')->fetchAll();
        $datas = array(
            'subject' => $CONFIG['site']['sitename'] . $this->types[$logs['type']],
            'logs_id' => $logs['log_id'],
            'logs_amount' => $logs['need_pay'] / 100,
        );
        $payment = $this->getPayment($logs['code']);
		if($logs['code'] == 'native' || $logs['code'] == 'micro' ){
			 require_cache( APP_PATH . 'Lib/Payment/' . $logs['code'] . '.weixin' . '.class.php' );//扫码支付
		}elseif (defined('IN_MOBILE')) {
            require_cache(APP_PATH . 'Lib/Payment/' . $logs['code'] . '.mobile.class.php');
        }else{
            require_cache(APP_PATH . 'Lib/Payment/' . $logs['code'] . '.class.php');
        }
        $obj = new $logs['code']();
        return $obj->getCode($datas, $payment);
    }	



    public function checkMoney($logs_id,$money){
        $money = (int) ($money );
        $logs = D('Paymentlogs')->find($logs_id);
        if($logs['need_pay'] == $money)
            return true;
        return false;
    }

	public function checkPayment($code){
        $datas = $this->fetchAll();
        foreach($datas as $val){
            if($val['code'] == $code)
                return $val;
        }
        return array();
    }

    public function getPayment($code){
        $datas = $this->fetchAll();
        foreach($datas as $val){
            if($val['code'] == $code)
                return $val['setting'];
        }
        return array();

    }

    public function logsPaid($logs_id) {
        $this->log_id = $logs_id; //用于外层回调
        $logs = D('Paymentlogs')->find($logs_id);
		
        if (!empty($logs) && !$logs['is_paid']){
            $data = array('log_id' => $logs_id,'is_paid' => 1,);
            if (D('Paymentlogs')->save($data)){ //总之 先更新 然后再处理逻辑  这里保障并发是安全的
                $ip = get_client_ip();
                D('Paymentlogs')->save(array('log_id' => $logs_id,'pay_time' => NOW_TIME,'pay_ip' => $ip));//更新付款时间
                $this->type = $logs['type'];
				
                if ($logs['type'] == 'appoint'){
					D('Appointorder')->updateOrder($logs['order_id']);//家政订单回调
                    return true;
                }elseif($logs['type'] == 'rank'){   
				     //提升会员等级
					$Userrank = D('Userrank')->where(array('rank_id'=>$logs['types']))->find();
					D('Users')->save(array('user_id'=>$logs['user_id'],'rank_id'=>$logs['types']));
					if($this->_CONFIG['profit']['profit_buy_user_rank']){
						D('Userprofitlogs')->pay_rank_profit_user($logs['user_id'],$logs['need_pay'],$Userrank['rank_name']);//会员购买等级对接三级分销
					}
					return true;
				}elseif($logs['type'] == 'breaks'){   
					//优惠买单
					D('Breaksorder')->settlement($logs['order_id']);//优惠买单回调
					return true;
				}elseif($logs['type'] == 'shop'){   
					//商家入驻更新费用
					D('Shop')->save(array('shop_id'=>$logs['order_id'],'shop_apply_prrice'=>$logs['need_pay']));
					return true;
				}elseif($logs['type'] == 'life'){   
					//分类信息订单回调
					D('Life')->updateLife($logs['order_id'],$logs['need_pay']);//分类信息
					return true;
				}elseif($logs['type'] == 'money'){
					D('Users')->updateCount($logs['user_id'], 'money', $logs['need_pay']);
					
					$payment = D('Payment')->where(array('code'=>$logs['code']))->find();//获取支付
					
					D('Usermoneylogs')->add(array(
						'user_id' => $logs['user_id'], 
						'money' => $logs['need_pay'], 
						'create_time' => NOW_TIME, 
						'create_ip' => $ip, 
						'intro' => '余额充值【'.round($logs['need_pay']/100,2).'】支付ID'.$logs['log_id'].'支付方式【'.$payment['name'].'】', 
					));
					D('Users')->return_recharge_integral_prestige($logs_id,$logs['user_id'], $logs['need_pay']);//充值余额送积分，威望，忽略错误
					D('Users')->Recharge_Full_Gvie_User_Money($logs['user_id'], $logs['need_pay']);//充值满送，忽略错误
					//file_put_contents(APP_PATH . 'Lib/Model/a.txt', var_export($logs, true));
					return true;
                }elseif($logs['type'] == 'tuan'){
					//抢购都是发送抢购券
                    $member = D('Users')->find($logs['user_id']);
					$codes = array();
					$obj = D('Tuancode');
					$order = D('Tuanorder')->find($logs['order_id']);
					$tuan = D('Tuan')->find($order['tuan_id']);
					//结束
					for($i = 0; $i < $order['num']; $i++){
						$local = $obj->getCode();
						$insert = array(
							'user_id' => $logs['user_id'], 
							'shop_id' => $tuan['shop_id'], 
							'order_id' => $order['order_id'], 
							'tuan_id' => $order['tuan_id'], 
							'code' => $local, 
							'price' => $tuan['price'], 
							'real_money' => (int)($order['need_pay'] / $order['num']), //退款的时候用
							'real_integral' => (int)($order['use_integral'] / $order['num']), //退款的时候用
							'fail_date' => $tuan['fail_date'], 
							'settlement_price' => $tuan['settlement_price'], 
							'create_time' => NOW_TIME, 
							'create_ip' => $ip, 
						);
						$codes[] = $local;
						$obj -> add($insert);
					}
					D('Tuanorder')->save(array('order_id' => $order['order_id'], 'status' => 1));//设置已付款
					D('Sms')->sms_tuan_user($member['user_id'],$order['order_id']);//团购商品通知用户
					D('Tuan')->updateCount($tuan['tuan_id'], 'sold_num');//更新卖出产品
					D('Tuan')->updateCount($tuan['tuan_id'], 'num', -$order['num']);
					D('Sms')->tuanTZshop($tuan['shop_id']);//发送短信通知商家
					D('Users')->prestige($member['user_id'], 'tuan');
					D('Tongji')->log(1, $logs['need_pay']);//统计//分销
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 1,$type = 4,$status = 1);
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 2,$type = 4,$status = 1);
					return true;
                }elseif ($logs['type'] == 'ele'){
					//餐饮订餐
                    D('Eleorder')->save(array('order_id' => $logs['order_id'], 'status' => 1, 'is_pay' => 1,'pay_time' => NOW_TIME));
					$order = D('EleOrder')->where('order_id =' . $logs['order_id'])-> find();
					D('Eleorder')->ele_month_num($logs['order_id']);//更新外卖销量
					D('Eleorder')->ele_delivery_order($logs['order_id'],0);//外卖配送接口
					D('Tongji')->log(3, $logs['need_pay']);//统计
					D('Sms')->eleTZshop($logs['order_id']);//通知商家
					D('Eleorder')->combination_ele_print($logs['order_id'],$order['addr_id']);//外卖打印万能接口
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 1,$type = 1,$status = 1);
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 2,$type = 1,$status = 1);
					return true;
                }elseif($logs['type'] == 'market'){
					
					//菜市场
                    D('Marketorder')->save(array('order_id' => $logs['order_id'],'status' => 1,'is_pay' => 1,'pay_time' => NOW_TIME));
					$order = D('Marketorder')->where('order_id =' . $logs['order_id'])->find();
					D('Marketorder')->market_month_num($logs['order_id']);//更新菜市场销量
					D('Sms')->marketTZshop($logs['order_id']);//通知商家
					D('Marketorder')->combination_market_print($logs['order_id'],$order['addr_id']);//菜市场打印万能接口
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 1,$type = 9,$status = 1);
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 2,$type =9,$status = 1);
					return true;
                }elseif($logs['type'] == 'store'){
					//便利店订餐
                    D('Storeorder')->save(array('order_id' => $logs['order_id'],'status' => 1,'is_pay' => 1,'pay_time' => NOW_TIME));
					$order = D('Storeorder')->where('order_id =' . $logs['order_id'])->find();
					D('Storeorder')->store_month_num($logs['order_id']);//更新便利店销量
					D('Sms')->storeTZshop($logs['order_id']);//通知商家
					D('Storeorder')->combination_store_print($logs['order_id'],$order['addr_id']);//便利店打印万能接口
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 1,$type = 10,$status = 1);
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 2,$type = 10,$status = 1);
					return true;
                }elseif($logs['type'] == 'hotel'){   
				    //酒店预订
                    $order = D('Hotelorder')->find($logs['order_id']);
                    $room = D('Hotelroom')->find($order['room_id']);
                    $hotel = D('Hotel')->find($order['hotel_id']);
                    $shop = D('Shop')->find($hotel['shop_id']);
                    D('Hotelorder')->save(array('order_id' => $logs['order_id'], 'order_status' => 1)); //设置已付款
					D('Sms')->sms_hotel_user($logs['order_id']);//短信通知用户
					D('Sms')->sms_hotel_shop($logs['order_id']);//短信通知酒店商家
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 1,$type = 6,$status = 1);
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 2,$type = 6,$status = 1);
                    return true;
                }elseif ($logs['type'] == 'crowd'){
					//众筹
                    D('Crowdorder')->save(array('order_id' => $logs['order_id'],'status' => 1 ));
					D('Crowd')->update_crowd_order_status($logs['order_id']);
					D('Sms')->sms_crowd_user($logs['order_id']);//短信通知会员
					D('Sms')->sms_crowd_uid($logs['order_id']);//通知众筹发起人
					return true;
                }elseif ($logs['type'] == 'farm'){   
				    //农家乐预订
                    $order = D('FarmOrder')->find($logs['order_id']);
                    $f = D('FarmPackage')->find($order['pid']);
                    $farm = D('Farm')->find($order['farm_id']);
                    $shop = D('Shop')->find($farm['shop_id']);
                    D('FarmOrder')->save(array('order_id' => $logs['order_id'], 'order_status' => 1)); //设置已付款
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 1,$type = 5,$status = 1);
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 2,$type = 5,$status = 1);
                    return true;
                }elseif($logs['type'] == 'booking'){
					D('Bookingorder')->updateBookingOrder($logs['order_id'],$logs['code']);//货到付款
					return true;
                }elseif ($logs['type'] == 'community'){
					//小区回调
                    D('Communityorder')->orderpay($logs['log_id']);
					return true;
                }  elseif ($logs['type'] == 'running'){
					//跑腿
                    D('Running')->save(array('running_id' => $logs['order_id'],'status' => 1 ));
					D('Sms')->sms_running_user($logs['order_id']);
					D('Sms')->sms_delivery_user($logs['order_id'],2);
					return true;
                } elseif ($logs['type'] == 'cloud'){
					//元购
                    D('Cloudgoods')->save_cloud_order_status($logs['order_id']);
					return true;
                } elseif ($logs['type'] == 'zhe'){
					//五折卡回调
                    D('Zheorder')->save_zhe_logs_status($logs['order_id']);
					return true;
                }elseif ($logs['type'] == 'stock'){
					//股权频道回调
                    D('Stock')->save_stock_logs_status($logs['order_id']);
					return true;
                }elseif ($logs['type'] == 'edu'){
					//教育频道回调
                    D('EduOrder')->save_edu_logs_status($logs['order_id']);
					return true;
                }elseif ($logs['type'] == 'book'){
					//预约
                    D('BookOrder')->save_book_logs_status($logs['order_id']);
					return true;
                }elseif ($logs['type'] == 'ktv'){
					//KTV频道回调
                    D('KtvOrder')->save_ktv_logs_status($logs['order_id']);
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 1,$type = 8,$status = 1);
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 2,$type = 8,$status = 1);
					return true;
                }else { // 商城购物
                    if (empty($logs['order_id']) && !empty($logs['order_ids'])){
						//合并付款
                        $order_ids = explode(',', $logs['order_ids']);
                        D('Order')->save(array('status' => 1), array('where' => array('order_id' => array('IN', $order_ids))));
                        D('Sms')->mallTZshop($order_ids); //通知商家
                        D('Order')->mallSold($order_ids);//更新销售接口
                        D('Order')->mallPeisong(array($order_ids),0);//更新配送接口
						D('Order')->combination_goods_print($order_ids);//万能商城订单打印
                    } else {
                        D('Order')->save(array('order_id' => $logs['order_id'],'status' => 1));
                        D('Order')->mallPeisong(array($logs['order_id']),0);//更新配送接口
                        D('Order')->mallSold($logs['order_id']);//更新销售接口
                        D('Sms')->mallTZshop($logs['order_id']);//通知商家
						D('Coupon')->change_download_id_is_used($logs['order_id']);//如果有优惠劵就修改优惠劵的状态，合并付款暂时不做
						D('Order')->combination_goods_print($logs['order_id']);//万能商城订单打印
                    }
                    D('Tongji')->log(2, $logs['need_pay']); //统计
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 1,$type = 2,$status = 1);
					D('Weixinmsg')->weixinTmplOrderMessage($logs['order_id'],$cate = 2,$type = 2,$status = 1);
                }
				
            }
        return true;
      }

   }

   

}



