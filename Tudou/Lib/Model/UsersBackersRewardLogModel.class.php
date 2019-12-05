<?php
class UsersBackersRewardLogModel extends CommonModel{
    protected $pk   = 'log_id';
    protected $tableName =  'user_backers_reward_log';
	
	
	//下级会员购买后给上级分成
	public function UsersBackersReward($order_id,$type){
		if($type == 'goods'){
			$Order = D('Order')->find($order_id);
			$Shop = D('Shop')->find($Order['shop_id']);
			$user_id = $Order['user_id'];
			$shop_id = $Order['shop_id'];
			$goods_name = D('Ordergoods')->get_mall_order_goods_name($order_id);
			$Users = D('Users')->find($Order['user_id']);
			$Userrank = D('Userrank')->where(array('rank_id'=>$Users['rank_id']))->find();
			if($Shop['is_goods_backers']){
				$money = (int)(($Order['need_pay']*$Userrank['reward'])/100);
			}else{
				$money = 0;
			}
			$intro = '【推手】商城订单ID：【'.$order_id.'】下级购买';
		}elseif($type == 'ele'){
			$Eleorder = D('Eleorder')->find($order_id);
			$Shop = D('Shop')->find($Eleorder['shop_id']);
			$user_id = $Eleorder['user_id'];
			$shop_id = $Eleorder['shop_id'];
			$goods_name = D('Eleorder')->get_ele_order_product_name($order_id);
			$Users = D('Users')->find($Eleorder['user_id']);
			$Userrank = D('Userrank')->where(array('rank_id'=>$Users['rank_id']))->find();
			if($Shop['is_ele_backers']){
				$money = (int)(($Eleorder['need_pay']*$Userrank['reward'])/100);
			}else{
				$money = 0;
			}
			$intro = '【推手】外卖订单ID：【'.$order_id.'】下级购买';
		}elseif($type == 'market'){
			$Marketorder = D('Marketorder')->find($order_id);
			$Shop = D('Shop')->find($Marketorder['shop_id']);
			$user_id = $Marketorder['user_id'];
			$shop_id = $Marketorder['shop_id'];
			$goods_name = D('Marketorder')->get_market_order_product_name($order_id);
			$Users = D('Users')->find($Marketorder['user_id']);
			$Userrank = D('Userrank')->where(array('rank_id'=>$Users['rank_id']))->find();
			$money = (int)(($Marketorder['need_pay']*$Userrank['reward'])/100);
			$intro = '【推手】菜市场订单ID：【'.$order_id.'】下级购买';
		}elseif($type == 'store'){
			$Storeorder = D('Storeorder')->find($order_id);
			$Shop = D('Shop')->find($Storeorder['shop_id']);
			$user_id = $Storeorder['user_id'];
			$shop_id = $Storeorder['shop_id'];
			$goods_name = D('Storeorder')->get_store_order_product_name($order_id);
			$Users = D('Users')->find($Storeorder['user_id']);
			$Userrank = D('Userrank')->where(array('rank_id'=>$Users['rank_id']))->find();
			$money = (int)(($Storeorder['need_pay']*$Userrank['reward'])/100);
			$intro = '【推手】便利店订单ID：【'.$order_id.'】下级购买';
		}
		
		list($fuid,$type)= $this->getFuid($user_id);
		
		if($money > 0){
			$data = array();
			$data['user_id'] = $user_id;
			$data['fuid'] = $fuid;
			$data['shop_id'] = $shop_id;
			$data['order_id'] = $order_id;
			$data['goods_name'] = $goods_name;
			$data['money'] = $money;
			$data['intro'] = $intro.''.$type;
			$data['create_time'] = NOW_TIME;
			$data['create_ip'] = get_client_ip();
			$this->add($data);
			D('Users')->addMoney($fuid,$money,$intro.''.$type);
			return $money;
		}
		return 0;
	}
	
	
	//循环获取分成，第一推荐人，第二推荐人，第三推荐人
	public function getFuid($user_id){
		$obj = D('Users');
		$Users = $obj->find($user_id);
		if($Users['fuid1']){
			$fuid = $obj->find($Users['fuid1']);
			if($fuid['is_backers'] == 2){
				return array($Users['fuid1'],$type = '【一级分成】');
			}
		}elseif($Users['fuid2']){
			$fuid = $obj->find($Users['fuid2']);
			if($fuid['is_backers'] == 2){
				return array($Users['fuid2'],$type = '【二级分成】');
			}
		}elseif($Users['fuid3']){
			$fuid = $obj->find($Users['fuid3']);
			if($fuid['is_backers'] == 2){
				return array($Users['fuid3'],$type = '【三级分成】');
			}
			
		}
		return true;
	}
	
}