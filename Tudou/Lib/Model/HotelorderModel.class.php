<?php
class HotelorderModel extends CommonModel{
    protected $pk   = 'order_id';
    protected $tableName =  'hotel_order';
    
	
	public function getError() {
        return $this->error;
    }
	
    public function cancel($order_id){
        if(!$order_id = (int)$order_id){
            return false;
        }elseif(!$detail = $this->find($order_id)){
            return false;
        }else{
            if($detail['online_pay'] == 1&&$detail['order_status'] == 1){
                $detail['is_fan'] = 1;
            }
            $room = D('Hotelroom')->find($detail['room_id']);
            if(!$room['is_cancel']){
                return false;
            }
            if(false !== $this->save(array('order_id'=>$order_id,'order_status'=>-1))){
                if($detail['is_fan'] == 1){
                    D('Users')->addMoney($detail['user_id'],(int)$detail['amount']*100,'酒店订单取消,ID:'.$order_id.'，返还余额');
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 6,$status = 11);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 6,$status = 11);
                }
                D('Hotelroom')->updateCount($detail['room_id'],'sku',$detail['num']);
                return true;
            }else{
                return false;
            }
            
        }  
    }
     
    public function plqx($hotel_id){
        if($hotel_id = (int)$hotel_id){
            $ntime = date('Y-m-d',NOW_TIME);
            $map['stime'] = array('LT',$ntime);
            $map['hotel_id'] = $hotel_id;
            $order = $this->where($map)->select();
            foreach ($order as $k=>$val){
                $this->cancel($val['order_id']);
            }
            return true;
        }else{
            return false;
        }
    }
	
	
    //酒店结算
    public function complete($order_id){
		$order_id = (int)$order_id;
		if(empty($order_id)){
			 $this->error = '必要的参数order_id没有传入';
			 return false;
		}
		$detail = $this->find($order_id);
		if(!empty($detail)){
			$Hotel = D('Hotel')->find($detail['hotel_id']);
            if($detail['online_pay'] == 1&&$detail['order_status'] == 1){
                $detail['is_fan'] = 1;
            }
			
            $room = D('Hotelroom')->find($detail['room_id']);
			$shop = D('Shop')->find($detail['shop_id']);
			$commission = (int)(($detail['amount'] * $shop['commission'])/100);
			if(!$commission){
				$this->error = '商户佣金设置不正确';
			   	return false;
			}
			$jiesuan_amount = $detail['amount'] - $commission;
			
            if(false !== $this->save(array('order_id'=>$order_id,'order_status'=>2))){
				
                if($detail['is_fan'] == 1 && $jiesuan_amount > 1){
					
					$info = '酒店订单号【'.$order_id.'】结算，房间名称【'.$room['title'].'】当前结算佣金比例【'.$shop['commission'].'%】订单总价【'.$detail['amount'].'】元';
					D('Shopmoney')->insertData($order_id,$id ='0',$Hotel['shop_id'],$jiesuan_amount*100,$type ='hotel',$info);//结算给商家
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 6,$status = 8);
					D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 6,$status = 8);
                }else{
					 $this->error = '订单状态不正确';
			   		 return false;
				}
            }else{
                $this->error = '更新酒店订单已完成数据库操作失败';
			   	return false;
            }
		}else{
			$this->error = '没有找到该订单详情';
			return false;
		}
    }  
	//酒店退款给用户封装
    public function hotel_refund_user($order_id){
		$order_id = (int)$order_id;
		if(empty($order_id)){
			 return false;
		}
		$detail = $this->find($order_id);
		if(!empty($detail)){
            if(false !== $this->save(array('order_id'=>$order_id,'order_status'=>4))){
				D('Sms')->sms_hotel_refund_user($order_id);//酒店退款通知用户手机
				$info = '酒店订单号：【'.$order_id.'】，申请退款，退资金'.$detail['amount'].'元';
                D('Users')->addMoney($detail['user_id'], $detail['amount']*100, $info);//给用户增加金额
				D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 6,$status = 4);
			    D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 6,$status = 4);
                return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
     }  
}