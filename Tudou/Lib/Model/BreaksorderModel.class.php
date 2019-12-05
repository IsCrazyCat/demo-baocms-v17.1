<?php


class BreaksorderModel extends CommonModel{
    protected $pk   = 'order_id';
    protected $tableName =  'breaks_order';
	
    //更新优惠买单销售接口
    public function settlement($order_id){
		
		D('Breaksorder')->save(array('order_id' =>$order_id,'status' => 1,'pay_time' =>time())); //设置已付款
		
		$Breaksorder = D('Breaksorder')->find($order_id );//查询订单信息
		
		$Shopyouhui = D('Shopyouhui')->find($Breaksorder['shop_id']);//商家优惠信息
		
		D('Shopyouhui')->updateCount($Shopyouhui['yh_id'], 'use_count',1);
		
		$Shop = D('Shop')->find($Breaksorder['shop_id']);//商家信息
		
		
		$deduction = $this->get_deduction($Shop['shop_id'],$Breaksorder['amount'],$Breaksorder['exception']);//网站扣除金额，暂时写到购买的会员余额
		
		
		if($Shopyouhui['type_id'] == 0){//打折
			if(!empty($Shopyouhui['deduction'])){
				$money = round(($Breaksorder['need_pay'] - $deduction)*100,2);//商户实际到账
				$info = '【打折】优惠买单，订单ID【'.$order_id.'】，用户实际支付 - 网站扣除金额';
			}else{
				$money = round($Breaksorder['need_pay']*100,2);	
				$info = '【打折】优惠买单，订单ID【'.$order_id.'】，用户实际支付';
			}
		}elseif($Shopyouhui['type_id'] == 2){//客户不设置
				$money = round($Breaksorder['need_pay']*100,2);	
				$info = '【未设置优惠】优惠买单，订单ID【'.$order_id.'】，用户实际支付';
		}elseif($Shopyouhui['type_id'] == 1){//
			if(!empty($Shopyouhui['vacuum'])){
				$money = round(($Breaksorder['need_pay'] - $deduction)*100,2);//商户实际到账
				$info = '【满减】优惠买单，订单ID【'.$order_id.'】，用户实际支付 - 网站扣除金额';
			}else{
				$money = round($Breaksorder['need_pay']*100,2);	
				$info = '【满减】优惠买单，订单ID【'.$order_id.'】，用户实际支付';
			}	
		}

		D('Shopmoney')->insertData($order_id,$id ='0',$Breaksorder['shop_id'],$money,$type ='breaks',$info);//结算给商家 
		D('Sms')->breaksTZshop($order_id);//发送短信给商家
		D('Sms')->breaksTZuser($order_id);//发送短信给用户
		
        return TRUE;
    }
	
	//计算满减
    public function get_deduction($shop_id,$amount,$exception){
        $shopyouhui = D('Shopyouhui')->where(array('shop_id'=>$shop_id,'is_open'=>1,'audit'=>1))->find();
        $need = $amount - $exception;//应该计算的金额=消费总额-参与优惠
        if($shopyouhui['type_id'] == 2){
            $result_deduction = $need; //减去金额=总金额-不参与优惠金额*点数
        }elseif($shopyouhui['type_id'] == 0){
            $result_deduction = round($need *$shopyouhui['deduction']/10,2); //减去金额=总金额-不参与优惠金额*点数
        }elseif($shopyouhui['type_id'] == 1){
            $t = (int)$need/$shopyouhui['vacuum'];//$T是应付款除以网站抽成金额，比如100元，网站抽3元，这里的t就是百分之3
            $result_deduction = round($t*$need/10,2);//实际付款金额*百分比
        }
        return $result_deduction;//返回网站扣除金额
    }

					
}