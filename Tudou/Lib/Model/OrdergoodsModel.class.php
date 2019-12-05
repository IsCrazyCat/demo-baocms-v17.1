<?php

class OrdergoodsModel extends CommonModel {
    protected $pk = 'id';
    protected $tableName = 'order_goods';
    protected $types = array(
        0 => '等待发货',
        1 => '已经捡货',
        8 => '已完成配送',
    );

    public function getType() {
        return $this->types;
    }
	
	//根据订单ID获取商城订单名称
	public function get_mall_order_goods_name($order_id){
		    $Order = D('Order')->find($order_id);
			$goods_ids = D('Ordergoods')->where("order_id={$order_id}")->getField('goods_id', true);
			$goods_ids = implode(',', $goods_ids);
			$map = array('goods_id' => array('in', $goods_ids));
			$goods_name = D('Goods')->where($map)->getField('title', true);
			$goods_name = implode(',', $goods_name);
			return $goods_name;
		 
    }
	
	
   //第一次更新商品运费
   public function calculation_express_price($uid,$kuaidi_id,$num,$goods_id,$pc_order) {
		$obj = D('Paddress');
		$addressCount = $obj->where(array('user_id' => $uid)) -> count();//统计客户的收货地址
		if ($addressCount == 0) {
			if($pc_order ==1){
				$this->error = '客户还没收货地址';
			   	return false;
			}else{
				$this->error = '客户还没收货地址';
			   	return false;
			}
		} else {
			$defaultCount = $obj->where(array('user_id' => $uid, 'default' =>1))->count();//统计默认地址
			if ($defaultCount == 0) {
				$detail = $obj->where(array('user_id' => $uid))->order("id desc")->find();//没有默认地址
			} else {
			    $detail = $obj->where(array('user_id' => $uid,'default' => 1))->find();//找到默认地址
			}
		}
	   return $this->replace_add_express_price($uid,$detail['id'],$num,$goods_id); //获得运费，会员id，地址id，商品数量，商品id
    }
	
	 //商城万能打印接口
    public function combination_goods_print($order_ids) {
        if (is_array($order_ids)) {
            $order_ids = join(',', $order_ids);
            $Order = D('Order')->where("order_id IN ({$order_ids})")->select();
            foreach ($Order as $k => $v) {
                $this->goods_order_print($v['order_id']);
            }
        } 
	
	}
	//三维数组拆分重组转二维
	private function three_to_tow($arr){
		foreach($arr as $k=>$val){
			foreach($val as $k => $v){
				$arr_tow[] = $v;
			}
		}
		return $arr_tow;
	}
	
	
   //商城合并付款新运费，跟单个商品付款不重复，二开：286、099、981
   public function merge_update_express_price($uid,$type,$log_id,$address_id) {
	    $log_id = (int)$log_id;
		$Paymentlogs = D('Paymentlogs')->where(array('log_id'=>$log_id))->find();
		
	    if (!empty($Paymentlogs['order_ids'])) {
           $order_ids = explode(',', $Paymentlogs['order_ids']);
		   $Ordergoods = D('Ordergoods');
		   foreach($order_ids as $v){
			    $ordergoods_list_all[]= $Ordergoods->where(array('order_id'=>$v))->select();
		    }
			
		   $ordergoods_list = $this -> three_to_tow($ordergoods_list_all);
			
		   if (empty($ordergoods_list)) {
				return false;   
		   }else{
			   //更新订单物流ID
			   foreach($order_ids as $v){
				 D('Order')->save(array('address_id' => $address_id), array('where' => array('order_id' => $v))); 	   
			   }
			   
			   //更新订单表ID
			   foreach ($ordergoods_list as $k => $val) {
				 $Ordergoods->save(array('kuaidi_id' => $address_id), array('where' => array('id' => $val['id']))); 
			   }
			  //计算运费 
			   foreach ($ordergoods_list as $k => $v) {
				 $v['express_price'] ; //以前的运费
				 $express_price = $this->replace_add_express_price($uid,$address_id,$v['num'],$v['goods_id']);//现在的运费
				 $total_price = $v[total_price] -  $v['express_price']  + $express_price ;
				 $conbine_total_price += $total_price;//所有的 $total_price总和
				 $Ordergoods->save(array('total_price'=>$total_price,'express_price' =>  $express_price), array('where' => array('id' => $v['id']))); 
				 D('Order')->save(array('express_price' =>$express_price,'address_id' => $address_id), array('where' => array('order_id' =>$v['order_id']))); 
				 
			   }
			   //这里是更新日志总运费
			   D('Paymentlogs')->save(array('need_pay'=>$conbine_total_price),array('where'=>array('log_id'=>$log_id)));
			   return true; 	      			   
			}
        } else{
			 return false;   
		}
		return true; 
    }

	
   //用户更换收货地址更新配送费，并写入日志后再支付
   public function update_express_price($uid,$type,$order_id,$address_id) {
	   $order_id = (int)$order_id;
	   $Ordergoods = D('Ordergoods');
	   $ordergoods_list = $Ordergoods->where('order_id =' . $order_id)->select();
	   if (empty($ordergoods_list) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            return false;   
       }else{
		   //更新订单物流ID
		   D('Order')->save(array('address_id' => $address_id), array('where' => array('order_id' => $order_id))); 
		   foreach ($ordergoods_list as $k => $val) {
			 $Ordergoods->save(array('kuaidi_id' => $address_id), array('where' => array('id' => $val['id']))); 
		   }
		   //更新运费
		   foreach ($ordergoods_list as $k => $v) {
			 $replace_order_express_price = $this->replace_add_express_price($uid,$address_id,$v['num'],$v['goods_id']);
			 $Ordergoods->save(array('express_price' => $replace_order_express_price,'update_time'=>NOW_TIME), array('where' => array('id' => $v['id']))); 
		   }
		   $total_express_price = $Ordergoods->where('order_id =' . $order_id)->sum('express_price');//统计单个商品总运费
		   //更新总表运费
		   D('Order')->save(array('express_price' => $total_express_price,'address_id' => $address_id), array('where' => array('order_id' => $order_id))); 
		}
		return true; 
    }
	
	//更换地址更新商品运费
   public function replace_add_express_price($uid,$id,$num,$goods_id){
	   
	   $Paddress = D('Paddress')->where(array('user_id' => $uid, 'id' => $id))->find();
	   $detail = D('Goods')->where(array('goods_id'=>$goods_id))->find();
	
	   
	   if($detail['is_reight'] ==1){
		   
		    $Pyunfeiprovinces = D('Pyunfeiprovinces')->where(array('province_id' => $Paddress['city_id'],'kuaidi_id'=>$detail['kuaidi_id']))->find();//找到
		
		    if($Pyunfeiprovinces && $Pyunfei = D('Pyunfei')->find($Pyunfeiprovinces['yunfei_id'])){
				
				 if($num == 1){
					$reduce = $detail['weight'] - 1000;
					if($reduce >= 1){//如果大于1KG，只收首重费，比如商品重量1200g-1000g=200g
						$weight = $detail['weight'] - 1000; //返回200g
					}else{
						$weight = 0; //返回0g
					}
				 }else{
					$weights = $num *($detail['weight']) - 1000;  //10*1200g-1000g=11000g
					if($weights > 0){
						$weight = $weights; //12000g-1000g=11000g//扣除首重，返回可数
					}else{
						$weight = 0; 
					}
				 }
				 
				 $price = $Pyunfei['shouzhong'] + (($weight * $Pyunfei['xuzhong'])/1000);//返回费用
				 return $price; 
			}else{
				return 0; //没找到运费
			}
	   }
	   return 0; //商品不开启配送费
    }
	
}