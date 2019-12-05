<?php

class MarketorderModel extends CommonModel {
    protected $pk = 'order_id';
    protected $tableName = 'market_order';
    protected $cfg = array(
        0 => '待付款',
        1 => '待接单',
        2 => '配送中',
		3 => '退款中',
		4 => '已退款',		
        8 => '已完成',
    );
	public function getError() {
        return $this->error;
    }
    public function checkIsNew($uid, $shop_id) {
        $uid = (int) $uid;
        $shop_id = (int) $shop_id;
        return $this->where(array('user_id' => $uid, 'shop_id' => $shop_id, 'closed' => 0))->count();
    }

    public function getCfg() {
        return $this->cfg;
    }
	
	
	//检测用户收获地址是否超区
	public function getAddrDistance($addr_id,$shop_id){
		$Useraddr = D('Useraddr')->where(array('addr_id'=>$addr_id))->find();
		$Shop = D('Shop')->find($shop_id);
		$Market = D('Market')->where(array('shop_id'=>$shop_id))->find();
		$getAddrDistance = getAddrDistance($Useraddr['lat'], $Useraddr['lng'], $Shop['lat'], $Shop['lng']);
		
		
		if(empty($Market['is_radius'])){
			$radius = 5000;
		}else{
			$radius = $Market['is_radius']*1000;
		}
		
		if($getAddrDistance >= $radius){
			return false;
		}
		return true;
	}

		
		
		
	//取消，删除订单逻辑封装
	public function cancel($order_id,$user_id){
		if($detail = $this->find($order_id)){
			$Shop = D('Shop')->find($detail['shop_id']);
			$obj = D('DeliveryOrder');
			if($Shop['is_market_pei'] == 1){
            	$do = $obj->where(array('type_order_id' => $order_id, 'type' => 3))->find();
				if($do){
					if($do['status'] == 2) {
						$this->error = '配送员已经抢单，无法删除';
						return false;
					}elseif($do['status'] == 8){
						$this->error = '配送员已经完成配置了，无法删除';
						return false;
					}elseif($do['closed'] == 1){
						$this->error = '该订单配送状态不正确';
						return false;
					}
					if(!$obj->where(array('type_order_id' => $order_id, 'type' => 3))->save(array('closed'=>1))){
						$this->error = '抢单模式更新配送数据库失败';
						return false;
					}
				}
			}
			if($this->where(array('order_id'=>$order_id))->save(array('closed'=>1))){
				D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 11);
				D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 11);
			}else{
				$this->error = '更新数据库失败';
				return false;
			}
		}else{
			$this->error = '订单信息错误';
			return false;
		}
	}
	//删除过期菜市场订单,商家id，会员ID，可选
	public function past_due_market_order($shop_id ,$user_id){
		$config = D('Setting')->fetchAll();
		$past_due_market_order_time = isset($config['market']['past_due_market_order_time']) ? (int)$config['market']['past_due_market_order_time'] : 15;
        $time = NOW_TIME - $past_due_market_order_time * 60;
		$list = $this->where(array('closed'=>0,'status'=>0))->select();
		foreach ($list as $key => $val){
            if($val['create_time'] < $time){ 
                $this->cancel($val['order_id']);
            }
        }
		return true;
	}
	
	
	//根据订单ID获取菜市场订单名称
	public function get_market_order_product_name($order_id){
		    $order = D('Marketorder')->find($order_id);
            $product_ids = D('Marketorderproduct')->where('order_id=' . $order_id)->getField('product_id', true);
            $product_ids = implode(',', $product_ids);
            $map = array('product_id' => array('in', $product_ids));
            $product_name = D('Marketproduct')->where($map)->getField('product_name', true);
            $product_name = implode(',', $product_name);
			return $product_name;
		 
    }
		
	//退款逻辑封装
	public function market_user_refund($order_id){
		$detail = $this->where('order_id =' . $order_id)->find();
		if(!$detail = $this->where('order_id =' . $order_id)->find()){
           $this->error = '没有找到订单';
		   return false;
        }else{
			if(!$Shop = D('Shop')->find($detail['shop_id'])){
			   $this->error = '没有找到该订单的商家信息';
			   return false;
			}else{
				if($Shop['is_market_pei'] == 1){
					$do = D('DeliveryOrder')->where(array('type_order_id' => $order_id,'type' =>3,'closed'=>0))->find();
					if($do && $do['status'] != 1){
						$this->error = '当前配送状态不支持退款';
						return false;
					}
					if($do){
						if(!$res = D('DeliveryOrder')->where(array('type_order_id' => $order_id, 'type' =>3))->setField('closed', 1)){
							$this->error = '申请退款更新配送信息错误，请稍后再试';
							return false;
						}
					}
			     }
				if($this->where('order_id =' . $order_id)->setField('status', 3)){
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 3);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 3);
					return true;
				}else{
					$this->error = '更新退款状态失败';
					return false;
				}
			}
        }
    }
	 


    public function overOrder($order_id) {
		if($detail = D('Marketorder')->find($order_id)){
			if($detail['status'] != 2){
				return false;
			}else{
				$Market = D('Market')->find($detail['shop_id']);
				if (D('Marketorder')->save(array('order_id' => $order_id, 'status' => 8,'end_time' => NOW_TIME))) { //防止并发请求
					$Intro = '菜市场订单结算';//获取结算说明
					D('Shopmoney')->insertData($order_id,$id ='0',$detail['shop_id'],$detail['settlement_price'],$type ='market',$Intro);//结算给商家
					if($detail['settlement_price'] > 0) {
						D('Userguidelogs')->AddMoney($detail['shop_id'], $detail['settlement_price'], $order_id,$type = 'market');//推荐员分成
						D('Users')->integral_restore_user($detail['user_id'],$order_id,$id ='0',$detail['settlement_price'],$type ='market');//菜市场购物返利积分
					}
					D('Marketorder')->AddDeliveryIogistics($order_id);//结算配送费给配送员
					D('Marketorderproduct')->updateByOrderId($order_id);
					D('Market')->updateCount($detail['shop_id'], 'sold_num'); //这里是订单数
					D('Market')->updateMonth($detail['shop_id']);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 8);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 8);
					return true;
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
		
    }
	//给配送员给钱
	public function AddDeliveryIogistics($order_id){
		if($detail = D('Marketorder')->find($order_id)){
			$Market = D('Market')->find($detail['shop_id']);
        	$Shop = D('Shop')->find($detail['shop_id']);
			if($Shop['is_market_pei'] == 1){
				if($detail['logistics_full_money']){//如果扣除的配送费一样分成
					D('Runningmoney')->add_delivery_logistics($order_id,$detail['logistics_full_money'],1);//配送费接口
					return true;
				}else{
					D('Runningmoney')->add_delivery_logistics($order_id,$Market['logistics'],1);//配送费接口
					return true;
				}
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	
	public function market_print($order_id,$addr_id) {	
			$order_id = (int) $order_id;
			$addr_id = (int) $addr_id;	
			$order = D('Marketorder')->find($order_id);
			if (empty($order))//没有找到订单返回假
            return false;
			if($order['is_daofu'] == 1){
				$fukuan = '货到付款';
			}else{
				$fukuan = '在线支付';
			}
            $member = D('Users')->find($order['user_id']);//会员信息
			if(!empty($addr_id)){
				$addr_id = $addr_id;	
			}else{
				$addr_id = $order['addr_id'];
			}
			$user_addr = D('Useraddr')->where(array('addr_id'=>$addr_id))->find();
			$shop_print = D('Shop')->where(array('shop_id'=> $order['shop_id']))->find();//商家信息
            $msg .= '@@2点菜清单__________NO:' . $order['order_id'] . '\r';
            $msg .= '店名：' . $shop_print['shop_name'] . '\r';
            $msg .= '联系人：' . $user_addr['name'] . '\r';
            $msg .= '电话：' . $user_addr['mobile'] . '\r';
            $msg .= '客户地址：' . $user_addr['addr'] . '\r';
            $msg .= '用餐时间：' . date('Y-m-d H:i:s', $order['create_time']) . '左右\r';
            $msg .= '用餐地址：' . $shop_print['addr'] . '\r';
            $msg .= '商家电话：' . $shop_print['tel'] . '\r';
            $msg .= '----------------------\r';
            $msg .= '@@2菜品明细\r';
            $products = D('Marketorderproduct')->where(array('order_id' => $order['order_id']))->select();
            foreach ($products as $key => $value) {
                $product = D('Marketproduct')->where(array('product_id' => $value['product_id']))->find();
                $msg		  .= ($key+1).'.'.$product['product_name'].'—'.($product['price']/100).'元'.'*'.$value['num'].'份\r';
            }
            $msg .= '----------------------\r';
            $msg .= '@@2支付方式：' . $fukuan . '\r';
            $msg .= '外送费用：' . $order['logistics'] / 100 . '元\r';
			
			$msg .= '菜品金额：' .'总价'. round($order['total_price'] / 100). '元-新单立减'.round($order['new_money'] / 100).'元-免配送费'.round($order['logistics_full_money'] / 100).'元-满减优惠'.round($order['full_reduce_price'] / 100).'元=应付金额'.round($order['need_pay'] / 100). '元\r';
			
            $msg .= '应付金额：' . $order['need_pay'] / 100 . '元\r';
			$msg .= '留言：'.$order['message'].'\r';
			return $msg;//返回数组
   }
   //打印接口中间件
   public function combination_market_print($order_id,$addr_id) {	
  		    $order = D('MarketOrder') -> where('order_id =' . $order_id) -> find();
			$shops = D('Shop') -> find($order['shop_id']);
			//菜市场打印开始
			if($shops['is_ele_print'] ==1){
			  $msg = $this->market_print($order['order_id'],$order['addr_id']);
			  $result = D('Print')->printOrder($msg, $shops['shop_id']);
			  $result = json_decode($result);
			  $backstate = $result -> state;
			  $market = D('Market') -> find($order['shop_id']);
			  if($market['is_print_deliver'] ==1){//如果开启自动打印
				  if ($backstate == 1) {
						if($shops['is_market_pei'] ==1){//1代表没开通配送确认发货步骤
							D('MarketOrder')->where(array('order_id' =>$order_id)) -> save(array('status' => 2,'is_print'=>1,'orders_time' => NOW_TIME));
						}else{//如果是配送配送只改变打印状态
							 D('MarketOrder') -> save(array('is_print'=>1), array("where" => array('order_id' => $order['order_id'])));
						}
					}	
			 }	
				
		    }
		  return true;
	  }
						
						
   public function market_delivery_order($order_id,$wait = 0) {	
   			$order_id = (int) $order_id;
			if($wait == 0){
				$status = 1;
			}else{
				$status = 0;
			}
  			$order = D('Marketorder')->find($order_id);
			if (empty($order)){
				 return false;//没有找到订单返回假
			}
		
			$res = D('DeliveryOrder')->where(array('type'=>'3','type_order_id'=>$order_id))->find();//查询是不是已经插入了
			
			$DeliveryOrder = D('DeliveryOrder');
            $shops = D('Shop')->where(array('shop_id'=>$order['shop_id']))->find();
			
			if (!$Useraddr = D('Useraddr')->find($order['addr_id'])) {
				return false;//没有找到用户地址返回假
			}
			
			if ($market = D('Market')->find($order['shop_id'])) {
				if(!empty($market['given_distribution'])){
					$is_appoint = 1;
				}else{
					$is_appoint  = 0;
				}
			}else{
				return false;//没有找到菜市场商家返回假
			}
			if($order['logistics_full_money'] > 0){
				$logistics_price = $order['logistics_full_money'];
			}else{
				$logistics_price = $order['logistics'];
			}
			if ($shops['is_market_pei'] == 1 && !$res) {
				$deliveryOrder_data = array(
						'type' => 3, 
						'type_order_id' => $order['order_id'], 
						'delivery_id' => 0, 
						'shop_id' => $order['shop_id'],
						'city_id' => $shops['city_id'],
						'area_id' => $shops['area_id'], 
						'business_id' => $shops['business_id'],  
						'lat' => $shops['lat'], 
						'lng' => $shops['lng'],  
						'user_id' => $order['user_id'], 
						'shop_name' => $shops['shop_name'],
						'name' => $Useraddr['name'],
						'mobile' => $Useraddr['mobile'],
						'addr' => $Useraddr['addr'],
						'addr_id' => $order['addr_id'], 
						'address_id' => $order['address_id'], 
						'logistics_price' => $logistics_price, //订单配送费
						'intro' => $order['message'], //订单备注
						'is_appoint' => $is_appoint, //状态1位指定配送员
						'appoint_user_id' => $market['given_distribution'], //指定配送员ID
						'create_time' => time(), 
						'update_time' => 0, 
						'status' => $status,
						'closed'=>0
					);
				$order_id = D('DeliveryOrder')->add($deliveryOrder_data);
			}
			
			D('Sms')->sms_delivery_user($order_id,$type=3);//短信通知配送员
			D('Weixintmpl')->delivery_tz_user($order_id,$type=3);//微信消息全局通知
			return TRUE;
	}
	
	public function market_month_num($order_id) {	
   	   $order_id = (int) $order_id;
       $Marketorderproduct = D('Marketorderproduct')->where('order_id =' . $order_id)->select();
       foreach ($Marketorderproduct as $k => $v) {
       	 D('Marketproduct')->updateCount($v['product_id'], 'sold_num', $v['num']);
		 D('Market')->updateCount($v['shop_id'], 'sold_num', $v['num']);
       }
      return TRUE;
	}
	//订单导出获取订单状态
	public function get_export_market_order_status($order_id) {	
   	   $order = D('Marketorder')->find($order_id);
       if($order['is_daofu'] ==1){
		   return '货到付款';
		}else{
			return $this->cfg[$order['status']];
		}
	}

	
	//订单导出获取订单的商品信息
	public function get_export_market_order_product($order_id) {	
   	  $Marketorderproduct = D('Marketorderproduct')->where(array('order_id'=>$order_id))->select();
	  foreach ($Marketorderproduct as $k => $v) {
       	 $Marketorderproduct[$k]['name'] = $this->get_market_product_name($v['product_id']);
		 $Marketorderproduct[$k]['num'] = $v['num'];
		 $Marketorderproduct[$k]['total_price'] = $v['total_price'];
      }
	  return  $Marketorderproduct[$k]['name'].'*'.$Marketorderproduct[$k]['num'].'='.$Marketorderproduct[$k]['total_price'];
	}
	
	//订单导出获取订单状态
	public function get_market_product_name($product_id) {	
   	   $Marketproduct = D('Marketproduct')->find($product_id);
       return $Marketproduct['product_name'];
	}
	
	//获取用户等待时间
	public function get_wait_time($order_id) {	
   	   $Marketorder = D('Marketorder')->find($order_id);
       if($Marketorder){
		   $now_time = time();
		   $cha_time = $now_time-$Marketorder['pay_time'];
		   return  ele_wait_Time($cha_time);
		}else{
		   return  false;
		}
	}
	
	//获取用户等待时间分钟数
	public function get_wait_time_minutes($order_id) {	
   	   $Marketorder = D('Marketorder')->find($order_id);
       if($Marketorder){
		   $now_time = time();
		   $cha_time = $now_time-$Marketorder['pay_time'];
		   return  $cha_time/60;
		}else{
		   return  false;
		}
	}
	
	//获取当前订单是否达到免邮条件
	public function get_logistics($total_money,$shop_id){	
	   $Market = D('Market')->find($shop_id);
	   if($Market['logistics_full'] > 10){
		   if($total_money >= $Market['logistics_full']){
			   return  $Market['logistics'];
			}else{
				return false; 
		    }
	   }else{
		  return false; 
	   }
	}
	
	//获取当前订单满减
	public function get_full_reduce_price($total_money,$shop_id){	
	   $Market = D('Market')->find($shop_id);
	   if($Market['is_full'] == 1){
		   //第一种可能
		   if(!empty($Market['order_price_full_1']) && !empty($Market['order_price_full_2'])){
			   //中间
			   if($total_money >= $Market['order_price_full_1'] && $total_money <= $Market['order_price_full_2']){
				   if($Market['order_price_reduce_1'] > 0){
					  return $Market['order_price_reduce_1'];   
				   }
				}
				//大于第二个满减
				if($total_money >= $Market['order_price_full_2']){
				   if($Market['order_price_reduce_2'] > 0){
					  return $Market['order_price_reduce_2'];   
				   }
				}
				if($total_money <= $Market['order_price_full_1']){
				   return 0; //不返回
				}
			}
			//第二种可能
			if(!empty($Market['order_price_full_1'])){
			   if($total_money >= $Market['order_price_full_1']){
				   if($Market['order_price_reduce_1'] > 0){
					  return $Market['order_price_reduce_1'];   
				   }
				}
			   if($total_money <= $Market['order_price_full_1']){
				   return 0; //不返回
				}
			}
			return 0; 
	   }else{
		  return 0; 
	   }
	}

	
	
}

