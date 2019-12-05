<?php
class TransactionAction extends CommonAction{
    public function _initialize(){
        parent::_initialize();
        
    }
    public function index(){
	    $this->assign('shop_count',$shop_count = D('Shop')->where(array('closed'=>'0'))->count());
        $shop_list = D('Shop')->where()->select();
		foreach ($shop_list as $k => $val) {
			$area = D('Area')->find($val['area_id']);
            $shop_list[$k]['area'] = $area['area_name'];
        }
		$shop_list = (array_chunk($shop_list,12));
		$this->assign('shop_list',$shop_list);//商家列表
	   
	    $this->assign('goodscate_count',$goodscate_count = D('Goodscate')->count());//产品种类
	   	$this->assign('order_count',$order_count = D('Order')->count());//交易次数
		$this->assign('order_price_count',$order_count = D('Order')->sum('need_pay'));//交易金额
	
        $this->display();
    }
   
}