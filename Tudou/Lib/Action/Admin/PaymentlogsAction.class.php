<?php
class PaymentlogsAction extends CommonAction{
	public function _initialize() {
        parent::_initialize(); 
		$this->assign('types', $types = D('Paymentlogs')->getType());
		$this->assign('codes', $codes = D('Paymentlogs')->getcode());
    }
	
    public function index(){
        $Paymentlogs = D('Paymentlogs');
        import('ORG.Util.Page');
        $map = array();
        if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time|pay_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time|pay_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time|pay_time'] = array('ELT', $end_time);
            }
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
		if (isset($_GET['type']) || isset($_POST['type'])) {
            $type = $this->_param('type', 'htmlspecialchars');
            if (!empty($type) && $type !=999 ) {
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        } else {
            $this->assign('type', 999);
        }
		if (isset($_GET['code']) || isset($_POST['code'])) {
            $code = $this->_param('code', 'htmlspecialchars');
            if (!empty($code) && $code !=999 ) {
                $map['code'] = $code;
            }
            $this->assign('code', $code);
        } else {
            $this->assign('code', 999);
        }
		if (isset($_GET['status']) || isset($_POST['status'])) {
            $status = $this->_param('status', 'htmlspecialchars');
            if ($status == 1) {
                $map['is_paid'] = 1;
            }else{
				$map['is_paid'] = 0;
			}
            $this->assign('status', $status);
        } else {
            $this->assign('status', 999);
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['order_id|log_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		//p($map);die;
        $count = $Paymentlogs->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Paymentlogs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
			$type = $Paymentlogs->get_payment_logs_type($val['type']);
            $list[$k]['type'] = $type;
			
        }
		
		$this->assign('money_is_paid_0',$money_is_paid_0 = $Paymentlogs->where(array('is_paid'=>0))->sum('need_pay'));
		$this->assign('money_is_paid_1',$money_is_paid_0 = $Paymentlogs->where(array('is_paid'=>1))->sum('need_pay'));
		$map['is_paid'] = 0;
		$this->assign('sum_0', $sum = $Paymentlogs->where($map)->sum('need_pay'));
		$map['is_paid'] = 1;
		$this->assign('sum_1', $sum = $Paymentlogs->where($map)->sum('need_pay'));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}