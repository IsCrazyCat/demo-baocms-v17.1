<?php

class BookOrderModel extends CommonModel{
    protected $pk   = 'order_id';
    protected $tableName =  'book_order';
	
	public function getError() {
        return $this->error;
    }
	
	
    public function getType(){
        return array(
            0  => '未付款',
            1  => '已付款',
            2  => '已抢单',
			3  => '退款中',
			4  => '已退款',
			8  => '已完成',
        );
    }
	
	//在线支付回调
	public function save_book_logs_status($order_id){
        $detail = $this->where(array('order_id'=>$order_id))->find();
        if (!empty($detail)) {
			if ($this->save(array('order_id' => $order_id, 'status' => '1','pay_time'=>NOW_TIME))){
				D('Shopcateattr')->where(array('attr_id'=>$detail['attr_id']))->setDec('attr_book_num',1);
				D('Shopcate')->where(array('cate_id'=>$detail['cate_id']))->setDec('book_num',1);
				return true; 
			}
        }else{
			return true; 
        }
    }
	
	//预约服务退款逻辑封装
	public function book_user_refund($order_id){
		if(!$detail = $this->where('order_id =' . $order_id)->find()) {
           $this->error = '没有找到订单';
		   return false;
        }else{
			if($this->where('order_id =' . $order_id)->setField('status', 3)){
				return true;
			}else{
				$this->error = '更新退款状态失败';
				return false;
			}
        }
    }
	
	
	//预约服务删除订单逻辑封装
	public function book_delete($order_id){
		if($obj->where(array('order_id'=>$order_id))->save(array('closed'=>1))){
			D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 11);
			D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 11);
            return true;
        }else{
            $this->error = '更新数据库失败';
			return false;
        }
    }

	//预约服务退款给用户逻辑封装
    public function book_agree_refund($order_id){
		if($order_id = (int)$order_id){
			if($detail = $this->find($order_id)){
				if(false !== $this->save(array('order_id'=>$order_id,'status'=>4))){
					D('Shopcateattr')->where(array('attr_id'=>$detail['attr_id']))->setDec('attr_book_num',1);
					D('Users')->addMoney($detail['user_id'], $detail['price'], '家政服务申请退款，订单号：'.$order_id);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 4);
				    D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 4);
					return true;
				}else{
					$this->error = '更新退款状态失败';
					return false;
				}
			}else{
				$this->error = '当前订单不存在';
				return false;
			}
		}else{
			$this->error = '没找到订单编号';
			return false;
		}
     }  
	 
	 //商家抢单
	public function book_orders($order_id,$shop_id){
		if(!$detail = $this->find($order_id)) {
           $this->error = '没有找到订单';
		   return false;
        }else{
			if($this->save(array('order_id'=>$order_id,'shop_id'=>$shop_id,'status'=>2,'orders_time'=>NOW_TIME))){
				return true;
			}else{
				$this->error = '更新抢单状态失败';
				return false;
			}
        }
    }
	
	 //商家订单已完成
	public function book_complete($order_id,$shop_id){
		if(!$detail = $this->find($order_id)) {
           $this->error = '没有找到订单';
		   return false;
        }else{
			if($detail['shop_id'] != $shop_id ){
				$this->error = '非法操作';
				return false;
			}
			if($this->save(array('order_id'=>$order_id,'status'=>8,'complete_time'=>NOW_TIME))){
				return true;
			}else{
				$this->error = '更新抢单状态失败';
				return false;
			}
        }
    }
	
	
   	//预约服务验证完成
    public function complete($order_id){
        if(!$order_id = (int)$order_id){
            return false;
        }elseif(!$detail = $this->find($order_id)){
            return false;
        }else{
			$Shop = D('Shop')->find($detail['shop_id']);
            if($detail['status'] == 1){
                if ($this->save(array('order_id'=>$order_id,'status'=>8,'complete_time'=>NOW_TIME))) {
					if($detail['price'] > 0){
						$info = '服务预约订单ID:'.$order_id.'完成，结算金额'.round($detail['price']/100,2);
						D('Shopmoney')->insertData($order_id,$id ='0',$detail['shop_id'],$detail['price'],$type ='book',$info);//结算给商家
						D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 8);
				    	D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 9,$status = 8);
					}
                   }else{
					   return false;
					}
            }else{
				 return false;
			}
        }  
   }
}