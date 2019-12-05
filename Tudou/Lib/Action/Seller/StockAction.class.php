<?php
class StockAction extends CommonAction {
	public function _initialize() {
        parent::_initialize();
		if(!$this->_CONFIG['operation']['stock']) {
            $this->error('股权功能已关闭');die;
        }
    }
  
    public function index() {
        $st = (int) $this->_param('st');
		$this->assign('st', $st);
		$this->display(); 
    }
    //股权订单列表
    public function loaddata() {
		$Stockorder = D('Stockorder');
		import('ORG.Util.Page'); 
		$map = array('shop_id' => $this->shop_id); 
		$st = (int) $this->_param('st');
		if ($st == 0 || empty($st)) { 
			$map['status'] = 0;
		}elseif ($st == 1) {    
			$map['status'] = 1;
		}
		$count = $Stockorder->where($map)->count(); 
		$Page = new Page($count, 10); 
		$show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
            die('0');
		}
		$list = $Stockorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $v){
            if($stock = D('Stock')->where(array('stock_id'=>$v['stock_id']))->find()){
                $list[$k]['stock'] = $stock;
            }
        }
		$this->assign('list', $list); 
		$this->assign('page', $show); 
		$this->display(); 
	}

    //股权订单详情
    public function detail($order_id){
        if(!$order_id = (int)$order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('Stockorder')->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法的订单操作');
        }else{
           $detail['stock'] = D('Stock')->where(array('stock_id'=>$detail['stock_id']))->find(); 
		   $detail['usersaux'] = D('Usersaux')->where(array('user_id'=>$detail['user_id']))->find();
           $this->assign('detail',$detail);
           $this->display();
        }
    }

}
