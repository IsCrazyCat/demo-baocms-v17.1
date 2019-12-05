<?php

class WeixinAction extends CommonAction {
	public function _initialize() {
        parent::_initialize();
		if($this->workers['tuan'] != 1){
          $this->error('对不起，您无权限，请联系掌柜开通');
        }
		
    }


    public function tuan() {
		$json = $_POST["snstr"];
		$jsonarr = explode('/',$json);
		if(!empty($json)){
			$code_id = $jsonarr['7'];
		}else{
			$code_id = (int) $this->_param('code_id');
		}
		if(empty($code_id)){
			$this->error('抢购劵ID不存在', U('index/index'));
		}
		
		$obj = D('Tuancode');
		$detail = $obj->find(array('where' => array('code_id' => $code_id)));
		
		$Shopworker = D('Shopworker')->where(array('user_id'=>$this->uid))->find();
		if(!$Shopworker){
			$this->error('您还不属于商家员工哦', U('index/index'));
		}
		if($Shopworker['status'] !=1){
			$this->error('您的员工信息还是待审核状态', U('index/index'));
		}
		if($Shopworker['shop_id'] != $detail['shop_id']){
			$this->error('非法操作', U('index/index'));
		}
		if($Shopworker['tuan']!= 1){
			$this->error('您没有管理抢购的资质', U('index/index'));
		}
		
		if($detail){
		 	$shop = D('Shop')->find(array('where' => array('shop_id' => $detail['shop_id'])));
            if (!empty($detail) && $detail['shop_id'] == $this->shop_id && (int) $detail['is_used'] == 0 && (int) $detail['status'] == 0) {
				$data = array();
				$data['is_used'] = 1;
				$data['worker_id'] = $this->uid;
				$data['used_time'] = NOW_TIME;
				$data['used_ip'] = get_client_ip();
             	if($obj->where(array('code_id'=>$detail['code_id']))->save($data)){
					 $res = $obj->saveShopMoney($detail,$shop);//统一更新
                     if($res == 1){
						$return[$var] = $var;
                        $this->success('团购券【'.$code_id.'】消费成功！',U('index/index'));
                     } else {
                         $this->success('到店付团购券【'.$code_id.'】消费成功！',U('index/index'));
                     }
                } else {
					$this->error('该抢购券无效');
               }
		}else{
			$this->error('未知错误');
		}
     }
  }
	
    public function coupon($download_id = 0) {
       
		$Shopworker = D('Shopworker')->where(array('user_id'=>$this->uid))->find();
		if(!$Shopworker){
			$this->error('您还不属于商家员工哦', U('index/index'));
		}
		if($Shopworker['status'] !=1){
			$this->error('您的员工信息还是待审核状态', U('index/index'));
		}
		
		$download_id = (int) $this->_param('download_id');
		$obj = D('Coupondownload');
		$detail = $obj->find($download_id);
		
		if(empty($detail)){
			$this->error('没有找到对应的优惠券信息', U('index/index'));
		}
		if($detail['shop_id'] != $Shopworker['shop_id']){
			$this->error('您不属于该公司的授权员工，无法进行管理', U('index/index'));
		}
		if($detail['is_used'] == 0){
			$result = $obj->save(array('download_id' => $detail['download_id'], 'is_used' => 1, 'used_time' => time(), 'used_ip' => get_client_ip()));
			if($result){
				$this->success('优惠劵ID【'.$download_id.'】验证成功！',U('index/index'));
			}else{
				$this->error('该优惠券验证失败！',U('index/index'));
			}
		}else{
			$this->error('该优惠券已经使用过了，验证失败！',U('index/index'));
		}
    }
	
	

}
