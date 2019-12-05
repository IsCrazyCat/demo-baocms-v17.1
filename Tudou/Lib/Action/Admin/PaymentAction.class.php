<?php
class PaymentAction extends CommonAction{
	
	
    public function index(){
        $Payment = D('Payment');
        import('ORG.Util.Page');
        $count = $Payment->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Payment->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function uninstall(){
        $payment_id = (int) $this->_get('payment_id');
        $payments = D('Payment')->fetchAll();
        if (!$payments[$payment_id]) {
            $this->tuError('没有该支付方式');
        }
        $datas = array('payment_id' => $payment_id, 'is_open' => 0);
        D('Payment')->save($datas);
        D('Payment')->cleanCache();
        $this->tuSuccess('卸载支付方式成功', U('payment/index'));
    }
	
	
    public function install(){
        $payment_id = (int) $this->_get('payment_id');
        $payments = D('Payment')->fetchAll();
        if(!$payments[$payment_id]){
            $this->error('没有该支付方式');
            die;
        }
        if($payments[$payment_id]['code'] == 'money'){
            D('Payment')->save(array('payment_id' => $payment_id, 'is_open' => 1));
            $this->success('余额支付安装成功', U('payment/index'));
            die;
        }
		if($payments[$payment_id]['code'] == 'integral'){
            D('Payment')->save(array('payment_id' => $payment_id, 'is_open' => 1));
            $this->success('积分支付安装成功', U('payment/index'));
            die;
        }
		
		
        if($this->isPost()){
			$CONFIG = D('Setting')->fetchAll();
			$data = $this->_post('data', false);
			$safety = $data['safety'];
			
			
			
			
            
            $datas = array('payment_id' => $payment_id, 'setting' => serialize($data), 'is_open' => 1);
            D('Payment')->save($datas);
            D('Payment')->cleanCache();
            $this->tuSuccess('恭喜您安装支付方式成功', U('payment/index'));
        }else{
            $this->assign('detail', $payments[$payment_id]);
            $this->display($payments[$payment_id]['code']);
        }
    }
	
	
}