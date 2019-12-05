<?php
    class PaymentlogsModel extends CommonModel{
        protected $pk   = 'log_id';
        protected $tableName =  'payment_logs';
        protected $type = array(
            goods => '商城购物',
            appoint => '家政购买',
            money => '余额充值',
            ele => '外卖订餐',
            booking => '订座定金',
			breaks => '优惠买单',
            pintuan => '拼团',
			crowd => '众筹',
			donate => '打赏',
			running => '跑腿',
			hotel => '酒店订单',
			farm => '农家乐预订',
			cloud => '云购',
			zhe => '五折卡',
			edu => '课程订单',
			stock => '股权订单',
			community => '小区缴费',
			market => '菜市场',
			store => '便利店',
			shop => '商家入驻',
        );
        protected $is_paid = array(
            0 => '未支付',
            1 => '已支付',
        );

        protected $code = array(
            weixin => '微信',
            alipay => '支付宝支付',
            money => '余额支付',
            native => '微信扫码支付',
			opay => '欧付宝',
			weixinh5 => '微信H5支付',
			integral => '积分支付',

        );

        public function getType() {
            return $this->type;
        }

        public function getis_paid() {
            return $this->is_paid;
        }

        public function getcode() {
            return $this->code;
        }
		
   //返回商户订单表的支付类型
	public function get_payment_logs_type($type) {
		$types = D('Payment')->getTypes();
		$result = array_flip($types);//反转数组
		$types = array_search($type, $result);
		if(!empty($types)){
			return $types;
		}else{
			return false;
		}
        return false;
	}

        public function getLogsByOrderId($type,$order_id){
            $order_id = (int)$order_id;
            $type = addslashes($type);
            return $this->find(array('where'=>array('type'=>$type, 'order_id'=>$order_id)));
			//return $this->where('type = '.$type.' and order_id ='.$order_id)->find();
        }
    }