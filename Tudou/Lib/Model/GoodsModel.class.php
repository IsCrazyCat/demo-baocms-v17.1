<?php

class GoodsModel extends CommonModel{
    protected $pk   = 'goods_id';
    protected $tableName =  'goods';
	
	protected $_validate = array(
        array(),
        array(),
        array()
    );
	
	public function getError(){
        return $this->error;
    }

    public function _format($data){
        $data['save'] =  round(($data['price'] - $data['mall_price'])/100,2);
        $data['price'] = round($data['price']/100,2);
		//多属性开始
		$data['mobile_fan'] = round($data['mobile_fan']/100,2);
		//多属性结束
        $data['mall_price'] = round($data['mall_price']/100,2); 
        $data['settlement_price'] = round($data['settlement_price']/100,2); 
        $data['commission'] = round($data['commission']/100,2); 
        $data['discount'] = round($data['mall_price'] * 10 / $data['price'],1);
        return $data;
    }
	
	
	//获取首页轮播数据
	public function getScroll(){
		$config = D('Setting')->fetchAll();
		$limit = isset($config['goods']['limit']) ? (int)$config['goods']['limit'] : 6;
		$order = isset($config['goods']['order']) ? (int)$config['goods']['order'] : 1;
		switch ($order) {
            case '1':
                $orderby = array('create_time' =>'desc','order_id' =>'desc');
                break;
            case '2':
                $orderby = array('total_price' =>'desc');
                break;
            case '3':
                $orderby = array('order_id' =>'desc');
                break;
        }
		$list = D('Order')->order($orderby)->limit(0,$limit)->select();
		foreach($list as $k => $v){
            if($user = D('Users')->where(array('user_id'=>$v['user_id']))->find()){
                $list[$k]['user'] = $user;
            }
        }
        return $list;
    }
	
	
	public function top_time($goods_id,$type){
		$config = D('Setting')->fetchAll();
		$goods = $this->find($goods_id);
		$shop = D('Shop')->find($goods['shop_id']);
		if(!$shop){
			$this->error = '没找到商家';
			return false;
		}
		$Users = D('Users')->find($shop['user_id']);
		if(!$Users){
			$this->error = '会员状态不正常';
			return false;
		}
		$money = $type * $config['goods']['top'] * 100;
		if($Users['money'] < $money) {
			$this->error = '您的会员账户余额不足，请先充值后操作';
			return false;
		}
		if(D('Users')->addMoney($Users['user_id'], -$money, '置顶商品ID【'.$goods_id.'】' . $type . '天')) {
			if($this->save(array('goods_id'=>$goods_id,'top_time'=>NOW_TIME + ($type*3600)))) {
				return true;
			}else{
				$this->error = '操作失败';
				return false;
			}
		}else{
			$this->error = '扣费失败';
			return false;
		}
   }
	
	//计算用户下单返回多少积分传2个参数，商品id商品类型
    public function get_forecast_integral_restore($id,$type){
        $config = D('Setting')->fetchAll();
		if($config['integral']['is_restore'] == 1){
			if($type == 'goods'){
				$Goods = D('Goods')->find($id);
				if($config['integral']['is_goods_restore'] == 1){
					if($config['integral']['restore_type'] == 1){
						$integral = $Goods['mall_price'];
					}elseif($config['integral']['restore_type'] == 2){
						$integral = $Goods['settlement_price'];
					}elseif($config['integral']['restore_type'] == 3){
						$integral = $Goods['mall_price']- $Goods['settlement_price'];
					}else{
						$integral = 0;
					}
				}else{
					return false;
				}
			}
			
			if($integral > 0){
				if($config['integral']['restore_points'] > 100){
					if($config['integral']['restore_points']){
						$integral = $integral - (($integral * $config['integral']['restore_points'])/100);
						return int($integral/100);
					}else{
						return false;
					}
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
		
    }
	
	//这里暂时没有判断多属性的问题，后期再判断
	public function check_add_use_integral($use_integral,$mall_price){
        $config = D('Setting')->fetchAll();
        $integral = $config['integral']['buy'];
		if($integral == 0){
			if($use_integral % 100 != 0) {
				$this->error = '积分必须为100的倍数';
				return false;
			}
			if($use_integral >= $mall_price){
				$this->error = '积分兑换数量必须小于'.$mall_price.','.'并是100的倍数';
				return false;
			}
		}elseif($integral == 10){
			if($use_integral % 10 != 0){
				$this->error = '积分必须为10的倍数';
			}
			if($use_integral*10 >= $mall_price){
				$this->error = '积分兑换数量必须小于'.($mall_price/10).','.'并是10的倍数';
				return false;
			}
		}elseif($integral == 100){
			if($use_integral % 1 != 0){
				$this->error = '积分必须为1的倍数';
				return false;
			}
			if($use_integral*100 >= $mall_price) {
				$this->error = '积分兑换数量必须小于'.($mall_price/100).','.'并是1的倍数';
				return false;
			}	
		}else{
			$this->error = '后台设置的消费抵扣积分比例不合法';
			return false;
		}
		return true;
    }

}