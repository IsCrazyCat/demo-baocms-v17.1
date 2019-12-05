<?php
class UsersBackersDiscountLogModel extends CommonModel{
    protected $pk   = 'log_id';
    protected $tableName =  'user_backers_discount_log';
	
	//推手会员等级折扣返利
	public function UsersBackersDiscount($order_id,$type){
		if($type == 'goods'){
			$Order = D('Order')->find($order_id);
			$Shop = D('Shop')->find($Order['shop_id']);
			$user_id = $Order['user_id'];
			$shop_id = $Order['shop_id'];
			$goods_name = D('Ordergoods')->get_mall_order_goods_name($order_id);
			$Users = D('Users')->find($Order['user_id']);
			$Userrank = D('Userrank')->where(array('rank_id'=>$Users['rank_id']))->find();
			if($Shop['is_goods_backers']){
				$money = $Order['need_pay'] - (($Order['need_pay']*$Userrank['discount'])/100);
			}else{
				$money = 0;
			}
			$intro = '【推手】商城推订单ID：【'.$order_id.'】等级折扣优惠';
		}elseif($type == 'ele'){
			$Eleorder = D('Eleorder')->find($order_id);
			$Shop = D('Shop')->find($Eleorder['shop_id']);
			$user_id = $Eleorder['user_id'];
			$shop_id = $Eleorder['shop_id'];
			$goods_name = D('Eleorder')->get_ele_order_product_name($order_id);
			$Users = D('Users')->find($Eleorder['user_id']);
			$Userrank = D('Userrank')->where(array('rank_id'=>$Users['rank_id']))->find();
			if($Shop['is_ele_backers']){
				$money = $Eleorder['need_pay'] - (($Eleorder['need_pay']*$Userrank['discount'])/100);
			}else{
				$money = 0;
			}
			$intro = '【推手】外卖订单ID：【'.$order_id.'】等级折扣优惠';
		}elseif($type == 'market'){
			$Marketorder = D('Marketorder')->find($order_id);
			$Shop = D('Shop')->find($Marketorder['shop_id']);
			$user_id = $Marketorder['user_id'];
			$shop_id = $Marketorder['shop_id'];
			$goods_name = D('Marketorder')->get_market_order_product_name($order_id);
			$Users = D('Users')->find($Marketorder['user_id']);
			$Userrank = D('Userrank')->where(array('rank_id'=>$Users['rank_id']))->find();
			if($Shop['is_ele_backers']){
				$money = $Marketorder['need_pay'] - (($Marketorder['need_pay']*$Userrank['discount'])/100);
			}else{
				$money = 0;
			}
			$intro = '【推手】菜市场订单ID：【'.$order_id.'】等级折扣优惠';
		}elseif($type == 'store'){
			$Storeorder = D('Storeorder')->find($order_id);
			$Shop = D('Shop')->find($Storeorder['shop_id']);
			$user_id = $Storeorder['user_id'];
			$shop_id = $Storeorder['shop_id'];
			$goods_name = D('Storeorder')->get_store_order_product_name($order_id);
			$Users = D('Users')->find($Storeorder['user_id']);
			$Userrank = D('Userrank')->where(array('rank_id'=>$Users['rank_id']))->find();
			if($Shop['is_ele_backers']){
				$money = $Storeorder['need_pay'] - (($Storeorder['need_pay']*$Userrank['discount'])/100);
			}else{
				$money = 0;
			}
			$intro = '【推手】便利店订单ID：【'.$order_id.'】等级折扣优惠';
		}
		
		$Users = D('Users')->find($user_id);
		
		if($Users['is_backers'] == 2){
			if($money > 0){
				$data = array();
				$data['user_id'] = $user_id;
				$data['shop_id'] = $shop_id;
				$data['order_id'] = $order_id;
				$data['goods_name'] = $goods_name;
				$data['money'] = $money;
				$data['intro'] = $intro;
				$data['create_time'] = NOW_TIME;
				$data['create_ip'] = get_client_ip();
				$this->add($data);//写入数据库
				D('Users')->addMoney($user_id,$money,$intro);
				return $money;
		   }
		   
	  }
	  return 0;
	}
	
}