<?php
class TemplateOrderModel extends CommonModel {
    protected $pk = 'order_id';
    protected $tableName = 'template_order';
	
	public function getError() {
        return $this->error;
    }
	
	//获取订单状态函数
	public function getTemplateOrderState($template_id,$shop_id){
        $TemplateOrder = D('TemplateOrder')->where(array('template_id'=>$template_id,'status'=>1,'shop_id'=>$shop_id))->find();
		if($TemplateOrder){
			 return true;	
		}else{
			return false;		
		}
		return false;	
    }
	
	
	//购买模板封装函数
	public function shop_pay_template($template_id,$shop_id){
        $Shop = D('Shop')->find($shop_id);
		$Users = D('Users')->find($Shop['user_id']);
		$Template = D('Template')->find($template_id);//准备购买的商家等级
		
		if($TemplateOrder = D('TemplateOrder')->where(array('template_id'=>$template_id,'status'=>'1'))->find()){
			$this->error = '您选择的模板已经购买无需再次购买';
			return false;
		}
		
		if(empty($Template)){
			$this->error = '您购买的模板不存在';
			return false;
		}elseif($Template['closed'] == 1){
			$this->error = '您购买的模板已经被管理员删除';
			return false;
	    }elseif($Template['type'] != 2){
			$this->error = '您购买的模板类型错误';
			return false;
	    }elseif($Users['money'] < $Template['price']){
			$this->error = '您的会员余额不足，无法购买，请先到会员中心充值后购买';
			return false;
		}
		$data = array();
		$data['shop_id'] = $shop_id;
		$data['user_id'] = $Users['user_id']; 
		$data['template_id'] = $template_id; 
		$data['price'] = $Template['price'];
		$data['status'] = 1;
		$data['shop_name'] = $Shop['shop_name'];
		$data['create_time'] = NOW_TIME;
		$data['create_ip'] = get_client_ip;

	   if($order_id = $this->add($data)){
		   if($Template['price'] > 0){
			   if(false == D('Users')->addMoney($Users['user_id'], -$Template['price'], '购买模板【' . $Template['name'] . '】扣费成功')){
				 	$this->error = '扣费失败请重试';
					return false;  
			   }
		   }
		   if($Template['is_mobile'] == 1){
			  D('Shop')->save(array('shop_id' => $shop_id, 'wap_template_id' => $template_id));
		   }else{
			  D('Shop')->save(array('shop_id' => $shop_id, 'pc_template_id' => $template_id));
		   }
		   D('Template')->where(array('template_id' => $template_id))->setInc('sold_num',1);
		   return true; 
		}else{
			$this->error = '订单处理非法错误，请稍后再试试';
			return false;
		}
    }

}
