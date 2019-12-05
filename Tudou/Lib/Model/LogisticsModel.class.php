<?php

class LogisticsModel extends CommonModel{
    protected $pk   = 'express_id';
    protected $tableName =  'logistics';
	
	public function get_order_express($order_id){
		import('ORG.Util.Express');//引入类
			
		$config = D('Setting')->fetchAll();
			
			
		$express_obj = new Express();
		$express_obj->type = $config['config']['express_api_type'];//传入查询类型
		$express_obj->keys = $config['config']['express_api_key'];//传入KEY
		$express_obj->customer = $config['config']['express_api_customer'];//传入KEY
		$Order = D('Order')->where(array('order_id' => $order_id))->find();//订单
		$Logistics = D('Logistics')->where(array('express_id' => $Order['express_id']))->find();
		$express_obj->company = $Logistics['express_com'];//传入快递编号
		$express_obj->num = $Order['express_number'];//传入快递单号
		$data = $express_obj->getContent();//获取数组
		return $data;
	}

}