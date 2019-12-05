<?php

class PaymentAction extends CommonAction{
	
	
	


	public function respond($type = '0'){
		$code = $this->_get('code');
		$id = $this->_get('id');
		
		if(empty($code)){
			$this ->error('没有该支付方式');
			die ;
		}
		
		
		$ret = D('Payment')->respond($code,$id);//去执行验证参数
	
		
		
		if($code != 'paypal' && $ret == false){
            $this->error('支付验证失败');
            die;
        }
			
		
		
		$type = D('Payment')->getType();
		$log_id = D('Payment')->getLogId();
		
		$log_id = ($code == 'paypal') ? $id : $log_id;//如果是paypal支付换一个回调模式
        $detail = D('Paymentlogs')->find($log_id);
		
		if(!empty($detail)){
			if($detail['type'] == 'ele'){
				$this->success('恭喜您外卖订单付款成功', U('user/eleorder/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'booking'){
				$this->success('恭喜您付款成功', U('user/booking/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'farm'){
				$this->success('恭喜您付款成功', U('user/farm/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'appoint'){
				$this->success('恭喜您家政支付成功啦', U('user/appoint/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'running'){
				$this->success('恭喜您付款成功', U('user/running/index'));
			}elseif($detail['type'] == 'goods'){
				if($detail['order_id']){
					$this->success('商城订单支付成功啦', U('user/goods/detail',array('order_id'=>$detail['order_id'])));
				}else{
					$this->success('商城合并付款支付成功啦', U('user/goods/index',array('aready'=>'1')));
				}
			}elseif($detail['type'] == 'edu') {
				$this->success('恭喜您支付成功啦', U('user/edu/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'community'){
				$this->success('恭喜您小区缴费成功啦',  U('user/community/order'));
			}elseif($detail['type'] == 'stock'){
				$this->success('恭喜您支付成功啦', U('user/stock/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'money'){
				$this->success('恭喜您充值成功', U('user/member/index'));
			}elseif ($detail['type'] == 'book'){
				$this->success('恭喜您付款成功', U('user/book/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'breaks'){
				$this->success('恭喜您买单付款成功', U('user/breaks/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'life'){
				$this->success('恭喜您分类信息付款成功', U('user/life/index'));
			}else{
				$this->success('恭喜您付款成功', U('user/member/index'));
			}
		}else{
			$this->success('支付成功', U('user/member/index'));
		}

	}
	
	
	public function yes($log_id,$type = '0'){
		$log_id = (int)$log_id;
		$detail = D('Paymentlogs')->find($log_id);
	
		if($detail['is_paid'] == 1){
			if($detail['type'] == 'ele'){
				$this->success('恭喜您外卖订单付款成功', U('user/eleorder/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'booking'){
				$this->success('恭喜您付款成功', U('user/booking/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'farm'){
				$this->success('恭喜您付款成功', U('user/farm/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'appoint'){
				$this->success('恭喜您家政支付成功啦', U('user/appoint/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'running'){
				$this->success('恭喜您付款成功', U('user/running/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'goods'){
				if($detail['order_id']){
					$this->success('商城订单支付成功啦', U('user/goods/detail',array('order_id'=>$detail['order_id'])));
				}else{
					$this->success('商城合并付款支付成功啦', U('user/goods/index',array('aready'=>'2')));
				}
			}elseif($detail['type'] == 'edu') {
				$this->success('恭喜您支付成功啦', U('user/edu/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'community'){
				$this->success('恭喜您小区缴费成功啦',  U('user/community/order'));
			}elseif($detail['type'] == 'stock'){
				$this->success('恭喜您支付成功啦', U('user/stock/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'money'){
				$this->success('恭喜您充值成功', U('user/member/index'));
			}elseif ($detail['type'] == 'book'){
				$this->success('恭喜您付款成功', U('user/book/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'breaks'){
				$this->success('恭喜您买单付款成功', U('user/breaks/detail',array('order_id'=>$detail['order_id'])));
			}elseif($detail['type'] == 'life'){
				$this->success('恭喜您分类信息付款成功', U('user/life/index'));
			}else{
				$this->success('恭喜您付款成功', U('user/member/index'));
			}
		}else{
			$this->success('请去会员中心查看状态', U('user/member/index'));
		}

	}

	public function payment($log_id){
		if(empty($this -> uid)) {
			header("Location:" . U('passport/login'));
			die ;
		}
		$log_id = (int)$log_id;
		$logs = D('Paymentlogs') -> find($log_id);
		if(empty($logs) || $logs['user_id'] != $this ->uid || $logs['is_paid'] == 1) {
			$this->error('没有有效的支付记录');
			die ;
		}
		$this->assign('button', D('Payment')->getCode($logs));
		$this->assign('types', D('Payment')->getTypes());
		$this->assign('logs', $logs);
		$this->assign('paytype', D('Payment')->getPayments());
		$this->assign('paytypes',$paytype =  D('PaymentLogs')->getcode($logs['code']));
		$this->display();
	}
	
	
}
