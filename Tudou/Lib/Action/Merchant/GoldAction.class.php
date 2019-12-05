<?php
if (!defined('BASE_PATH')) {
    exit('Access Denied');
}
class GoldAction extends CommonAction{
    public function index(){
        $this->assign('payment', D('Payment')->getPayments());
        $this->display();
    }
    public function pay(){
        $gold = (int) $this->_post('gold');
        $code = $this->_post('code', 'htmlspecialchars');
        if ($gold <= 0) {
            $this->error('请填写正确的金块数');
            die;
        }
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->error('该支付方式不存在');
            die;
        }
        $logs = array(
			'user_id' => $this->uid, 
			'type' => 'gold', 
			'code' => $code, 
			'order_id' => 0, 
			'need_pay' => $gold * 100, 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip()
		);
        $logs['log_id'] = D('Paymentlogs')->add($logs);
        $this->assign('button', D('Payment')->getCode($logs));
        $this->assign('gold', $gold);
        $this->display();
    }
    public function logs(){
        $Usergoldlogs = D('Usergoldlogs');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Usergoldlogs->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Usergoldlogs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}