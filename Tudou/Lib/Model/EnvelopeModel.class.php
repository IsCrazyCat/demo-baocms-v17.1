<?php
class EnvelopeModel extends CommonModel{
    protected $pk   = 'envelope_id';
    protected $tableName =  'envelope';
    
    public function getType(){
        return array(
			'1' => '平台红包', 
			'2' => '商家红包', 
		);
    }
	
	 public function getOrderType(){
        return array(
			'1' => '商城订单', 
			'2' => '外卖订单', 
			'3' => '家政订单', 
			'4' => '预定订单', 
			'5' => '商家收银台', 
			'6' => '平台红包',
		);
    }
	//获取平台红包或者商家红包
	public function getArray($orderType,$shop_id,$pay_time){
		$envelope = M('Envelope')->where(array('bg_date' => array('ELT', TODAY),'closed'=>'0','type'=>'2','shop_id'=>$shop_id))->find();
   		if(!$envelope){
			$envelope = M('Envelope')->where(array('bg_date' => array('ELT', TODAY),'closed'=>'0','type'=>'1'))->find();
		}
		$date = date("Y-m-d",$pay_time);
		$arr['show'] = ($date >= $envelope['bg_date']) ? '1' : '0';
		$arr['envelope_id']= $envelope['envelope_id'];
    	$arr['intro']= $envelope['type'] == 1 ? '平台' : '商家';
        $arr['shop_name'] = M('Shop')->where(array('shop_id'=>$shop_id))->getField('shop_name');
		$arr['photo'] = M('Shop')->where(array('shop_id'=>$shop_id))->getField('photo');
        $shopdetails = D('Shopdetails')->where(array('shop_id'=>$shop_id))->getField('details');
		$arr['shopdetails'] = tu_msubstr(cleanhtml($shopdetails),0,60);
		return $arr;
	}
	
	
}