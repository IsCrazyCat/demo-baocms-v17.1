<?php


class FarmOrderModel extends CommonModel{
    
    protected $pk   = 'order_id';
    protected $tableName =  'farm_order';

    public function cancel($order_id){

        if(!$order_id = (int)$order_id){
            return false;
        }elseif(!$detail = $this->find($order_id)){
            return false;
        }else{
            if($detail['order_status'] == 1){
                $detail['is_fan'] = 1;
            }
            if(false !== $this->save(array('order_id'=>$order_id,'order_status'=>-1))){
                if($detail['is_fan'] == 1){
                    D('Users')->addMoney($detail['user_id'],(int)$detail['amount']*100,'农家乐订单取消,ID:'.$order_id.'，返还余额');
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 5,$status = 11);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 5,$status = 11);
                }
                return true;
            }else{
                return false;
            }
            
        }  
    }
    
    //新版农家乐结算
    public function complete($order_id){
        if(!$order_id = (int)$order_id){
            return false;
        }elseif(!$detail = $this->find($order_id)){
            return false;
        }else{
            if($detail['order_status'] == 1){
                $detail['is_fan'] = 1;
            }
			$farm = D('Farm')->where(array('farm_id'=>$detail['farm_id']))->find();
            if(false !== $this->save(array('order_id'=>$order_id,'order_status'=>2))){
                if($detail['is_fan'] == 1){
					$info = '农家乐【'.$farm['farm_name'].'】订单ID：'.$order_id.'完成，结算金额：'.$detail['jiesuan_amount'].'元';
					D('Shopmoney')->insertData($order_id,$id ='0',$farm['shop_id'],$detail['jiesuan_amount']*100,$type ='farm',$info);//结算给商家
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 5,$status = 8);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 5,$status = 8);
					return true;
                 }
                return true;
            }else{
                return false;
            }
            
        }  
    }
     
}