<?php 
class StockAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
		//$this->assign('areas', $areas = D('Area')->fetchAll());
		//$this->assign('bizs', $biz = D('Business')->fetchAll());
    }
	
    public function index(){
        $type = (int) $this->_param('type');
        if (!empty($type)) {
            $this->assign('type', $type);
        }
		$area_id = (int) $this->_param('area_id');
		if (!empty($area_id)) {
			$this->assign('area_id', $area_id);
		}
		$order = $this->_param('order','htmlspecialchars');
		if (!empty($order)) {
			$this->assign('order', $order);
		}
        $this->assign('nextpage', linkto('stock/loaddata', array('area_id'=>$area_id,'order'=>$order,'t' => NOW_TIME, 'p' => '0000')));
        $this->display();
    }
    public function loaddata(){
        $Stock = D('Stock');
        import('ORG.Util.Page');
	    $map = array('audit' => 1, 'closed' => 0);
        $type = (int) $this->_param('type');
        if (!empty($type)) {
            $map['type'] = $type;
            $this->assign('type', $type);
        }
		$area_id = (int) $this->_param('area_id');
        if ($area_id) {
            $map['area_id'] = $area_id;
			$this->assign('area_id', $area_id);
        }
		//排序重写
		$order = $this->_param('order','htmlspecialchars');
		switch ($order) {
            case 'p':
                $orderby = array('create_time' => 'desc');
                break;
            case 'v':
                $orderby = array('price' => 'asc', 'stock_id' => 'desc');
                break;
            case 's':
                $orderby = array('views' => 'desc');
                break;
        }
        $count = $Stock->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $var = c('VAR_PAGE') ? c('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Stock->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	//购买股权
    public function stockbuy(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $stock_id = (int) $_POST['stock_id'];
        $detail = D('Stock')->find($stock_id);
        if (empty($detail)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '该股权商品不存在'));
        }
        $Stock = D('Stock');
        $obj = D('Stockorder');
        if (IS_AJAX) {
            $num = (int) $_POST['num'];
            if (empty($num)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '数量不能为空'));
            }
            if ($num % 100 != 0) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '数量不正确'));
            }
            $detail = $Stock->find($stock_id);
			list($order_id,$need_pay_price) = $Stock->get_order_id($stock_id,$this->uid,$num);
            if($order_id){
				if($this->member['money'] >= $need_pay_price){//如果有余额就扣费
					if (!$Stock->pay_stock_status($stock_id,$this->uid,$num,$order_id)) {
						$this->ajaxReturn( array( "status" => "error", "msg" => $Stock->getError()));
					}else{
						$this->ajaxReturn( array("status" => "error","msg" => "股权购买成功，正在为您跳转","url" => U("user/stock/detail", array('order_id' => $order_id))));
					}
				}else{
					$this->ajaxReturn( array("status" => "error","msg" => "您股权成功，正在为您跳转付款页面","url" => U("stock/pay", array('order_id' => $order_id))));
				}	
            }else{
                $this->ajaxReturn( array( "status" => "error", "msg" => $Stock->getError()));
            }
        }
    }
	//股权详情页
    public function detail($stock_id){
        if ($stock_id = (int) $stock_id) {
            $obj = D('Stock');
            if (!($detail = $obj->find($stock_id))) {
                $this->error('没有该商品');
            }
            if ($detail['closed'] != 0 || $detail['audit'] != 1) {
                $this->error('没有该商品');
            }
			$obj->updateCount($msg_id, 'views');
            $thumb = unserialize($detail['thumb']);
            $this->assign('thumb', $thumb);
            $this->assign('detail', $detail);
            $this->display();
        } else {
            $this->error('没有该商品');
        }
    }
	
	//股权直接支付页面
	 public function pay(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Stockorder')->find($order_id);
        if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->error('该订单不存在');
            die;
        }
		
		$this->assign('order', $order);
        $this->assign('payment', D('Payment')->getPayments_running(ture));
        $this->display();
    }
	 //股权去付款
	 public function pay2(){
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $order_id = (int) $this->_get('order_id');
        $order = D('Stockorder')->find($order_id);
         if (empty($order) || $order['status'] != 0 || $order['user_id'] != $this->uid) {
            $this->tuMsg('该订单不存在');
            die;
        }
        if (!($code = $this->_post('code'))) {
            $this->tuMsg('请选择支付方式');
        }
        if ($code == 'wait') {
             $this->tuMsg('暂不支持货到付款，请重新选择支付方式');
        } else {
            $payment = D('Payment')->checkPayment($code);
            if (empty($payment)) {
                $this->tuMsg('该支付方式不存在，请稍后再试试');
            }
			$need_pay_price = $order['need_pay_price'];//再更新防止篡改支付日志
			if(!empty($need_pay_price)){
				$logs = array(
					'type' => 'stock', 
					'user_id' => $this->uid, 
					'order_id' => $order_id, 
					'code' => $code, 
					'need_pay' => $need_pay_price, 
					'create_time' => NOW_TIME, 
					'create_ip' => get_client_ip(), 
					'is_paid' => 0
				);
                $logs['log_id'] = D('Paymentlogs')->add($logs);
				if($logs['log_id']){
					$this->tuMsg('创建订单成功，下一步将跳转到付款页面',U('payment/payment', array('log_id' => $logs['log_id'])));
				}else{
					$this->tuMsg('写入支付日志表失败');
				}
			}else{
				$this->tuMsg('非法操作');
			}
        }
    }
	
	//股权查询系统
    public function query($order_id){
		if (empty($this->uid)) {
            $this->error('请先登录', U('passport/login'));
        }else{
			if($Usersaux = D('Usersaux')->where(array('user_id'=>$this->uid))->find()){
				if($Usersaux['audit'] != 1){
					$this->error('您还未通过实名认证', U('user/usersaux/index'));
				}elseif(!$order_id = (int)$order_id){
					$this->error('股权订单参数出现错误');
				}elseif(!$detail = D('Stockorder')->find($order_id)){
					$this->error('该订单不存在');
				}elseif($detail['status'] != 1){
					$this->error('订单未生效');
				}elseif($detail['user_id'] != $this->uid){
					$this->error('非法的订单操作');
				}else{
				   $detail['stock'] = D('Stock')->where(array('stock_id'=>$detail['stock_id']))->find(); 
				   $detail['usersaux'] = D('Usersaux')->where(array('user_id'=>$detail['user_id']))->find();
				   $detail['users'] = D('Users')->where(array('user_id'=>$detail['user_id']))->find();
				   $detail['shop'] = D('Shop')->where(array('shop_id'=>$detail['shop_id']))->find();
				   $this->assign('detail',$detail);
				   $this->display();
				}
			}else{
				$this->error('请先实名认证', U('user/usersaux/index'));
			}
		}
    }
}