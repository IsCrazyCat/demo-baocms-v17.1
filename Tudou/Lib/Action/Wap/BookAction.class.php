<?php
class BookAction extends CommonAction{
    protected $shopcate = array();
    public function _initialize(){
        parent::_initialize();
		if(empty($this->_CONFIG['operation']['book'])){
			$this->error('预约功能已关闭');die;
		}
        $this->shopcate = D('Shopcate')->fetchAll();
        $this->assign('shopcate', $this->shopcate);
        $this->cates = D('Shopcate')->fetchAll();
        $this->assign('cates', $this->cates);
    }
    public function index(){
        $this->display();
    }
	//预约页面
	public function order($order_id = 0){
		if(empty($this->uid)) {
            $this->error('登录状态失效',U('passport/login'));
            die;
        }
		$cate_id = (int) $this->_param('cate_id');
        if(!($cates = D('Shopcate')->find($cate_id))){
            $this->error('该分类不存在');
        }
		if($order_id = (int) $this->_param('order_id')){
			$detail = D('BookOrder')->find($order_id);
			if (empty($detail) || $detail['status'] != 0 || $detail['user_id'] != $this->uid) {
				$this->error('该订单不存在');
				die;
			}
			$this->assign('detail', $detail);
		}
        $this->assign('attrs', D('Shopcateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
		$this->assign('payment', $payment = D('Payment')->getPayments(true));
		$this->assign('cates', $cates);
        $this->display();
    }
	
	
	public function getAttrPrice(){
		$obj = D('Shopcateattr');
        if ($this->isPost()) {
            $attr_id = $this->_post('attr_id');
			if(!($detail = D('Shopcateattr')->find($attr_id))){
				$this->ajaxReturn(array('status' => 'error', 'msg' => '分类不存在'));
			}else{
				$this->ajaxReturn(array('status' => 'success', 'msg' => round($detail['attr_price']/100,2)));
			}
        }
	}
	//付款页面
    public function pay($order_id = 0){
        if(empty($this->uid)) {
            $this->tuMsg('您还没有登录',U('passport/login'));
            die;
        }
		$data = I('data');
		$gotime = I('gotime',0,'trim');
		$data['gotime'] = strtotime(trim($data['gotime']));
		$data['attr_id'] = intval(trim($data['attr_id']));
		if(!$data['attr_id']){
           $this->tuMsg('没有选择类型');
        }
		if(!($attr = D('Shopcateattr')->find($data['attr_id']))){
			$this->tuMsg('选择的类型不存在');
		}
		if($attr['price'] < 0){
			$this->tuMsg('类型价格配置错误，请选择其他类型');
		}	
		$data['cate_id'] = $attr['cate_id'];	
		if(!$data['gotime']){
           $this->tuMsg('没有选择时间');
        }	
		$data['name'] = htmlspecialchars(trim($data['name']));	
		if(!$data['name']){
             $this->tuMsg('没有填写联系人');
        }	
		$data['mobile'] = htmlspecialchars(trim($data['mobile']));		
        if(!$data['mobile'] || !isMobile($data['mobile'])){
            $this->tuMsg('手机号码不正确！'.$data['mobile']);
        } 
		$data['addr'] = htmlspecialchars(trim($data['addr']));	
		$data['lng'] = htmlspecialchars(trim($data['lng']));	
		$data['lat'] = htmlspecialchars(trim($data['lat']));	
		$data['introduce'] = htmlspecialchars(trim($data['introduce']));	
		$data['code'] = htmlspecialchars(trim($data['code']));	
		if(!$data['code']){
         $this->tuMsg('请选择支付方式');
        }
            
		$obj = D('BookOrder');
		$data['city_id'] = $this->city_id;
        $data['user_id'] = $this->uid;
        $data['price'] = $attr['attr_price'];
		$data['status'] = 0;
        $data['create_time'] = time();
        $data['create_ip'] = get_client_ip();
        if($order_id = $obj->add($data)){
			$photos = $this->_post('photos', false);
			if(!empty($photos)){
				D('Bookphoto')->upload($order_id, $photos);
			}
			$logs = D('Paymentlogs')->getLogsByOrderId('book', $order_id);
			if(empty($logs)){
				$logs = array(
					'type' => 'book',
					'user_id' => $this->uid,
					'order_id' => $order_id,
					'code' => $data['code'],
					'need_pay' => $attr['attr_price'],
					'create_time' => NOW_TIME,
					'create_ip' => get_client_ip(),
					'is_paid' => 0
				);
				$logs['log_id'] = D('Paymentlogs')->add($logs);
			}else{
				$logs['need_pay'] = $attr['attr_price'];
				$logs['code'] = $data['code'];
				D('Paymentlogs')->save($logs);
			}
			D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 9,$status = 0);
			$this->tuMsg('选择支付方式成功！下面请进行支付', U('payment/payment', array('log_id' => $logs['log_id'])));
        }
     }
	 
	 
	 
	//再次付款页面
	public function order2($order_id = 0){
		if(empty($this->uid)) {
            $this->error('登录状态失效',U('passport/login'));
            die;
        }
		if($order_id = (int) $this->_param('order_id')){
			$detail = D('BookOrder')->find($order_id);
			if (empty($detail) || $detail['status'] != 0 || $detail['user_id'] != $this->uid) {
				$this->error('该订单不存在');
				die;
			}
		}
		$this->assign('payment', $payment = D('Payment')->getPayments(true));
        $detail['attr'] = D('Shopcateattr')->where(array('attr_id'=>$detail['attr_id']))->find(); 
        $detail['cate'] = D('Shopcate')->where(array('cate_id'=>$detail['cate_id']))->find();
        $this->assign('detail',$detail);
		$this->assign('thumb', D('Bookphoto')->getPics($order_id)); 
        $this->display();
    }
	
	 
	 //提交付款
    public function pay2(){
        if (empty($this->uid)) {
           $this->tuMsg('您还没有登录',U('passport/login'));
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('BookOrder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->tuMsg('该订单不存在');
            die;
        }
        if (!$code = $this->_post('code')) {
            $this->tuMsg('请选择支付方式');
        }
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->tuMsg('该支付方式不存在');
        }
        $logs = D('Paymentlogs')->getLogsByOrderId('book', $order_id);
        if (empty($logs)) {
            $logs = array(
                'type' => 'book',
                'user_id' => $this->uid,
                'order_id' => $order_id,
                'code' => $code,
                'need_pay' => $order['price'],
                'create_time' => NOW_TIME,
                'create_ip' => get_client_ip(),
                'is_paid' => 0
            );
            $logs['log_id'] = D('Paymentlogs')->add($logs);
        } else {
            $logs['need_pay'] = $order['price'];
            $logs['code'] = $code;
            D('Paymentlogs')->save($logs);
        }
        $this->tuMsg('选择支付方式成功，正在为您跳转', U('payment/payment', array('log_id' => $logs['log_id'])));
    }
    
	
	
        
}