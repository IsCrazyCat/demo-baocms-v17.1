<?php
class BookAction extends CommonAction {
	protected function _initialize(){
       parent::_initialize();
	   if(empty($this->_CONFIG['operation']['book'])) {
            $this->error('预约功能已关闭');
            die;
        }
	   $this->assign('getTypes', D('BookOrder')->getType());//订单状态
    }
  
    public function index() {
        $st = (int) $this->_param('st');
		$this->assign('st', $st);
        $this->display();
    }

    public function loaddata() {
		$obj = D('Bookingorder');
		import('ORG.Util.Page'); 
		$map = array('user_id' => $this->uid); 
		
		
		$st = I('st', '', 'trim,intval');
		if($st == 999){
			$map['order_status'] = array('in',array(0,-1,1,2,8));
		}elseif($st == 0 || $st == ''){
			$map['order_status'] = 0;
		}else{
			$map['order_status'] = $st;
		}
		$this->assign('st',$st);
    
	
		$count = $obj->where($map)->count(); 
          
		$Page = new Page($count, 10); 
		$show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
		$list = $obj->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = $room_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$room_ids[$val['room_id']] = $val['room_id'];
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Booking')->itemsByIds($shop_ids));
        }
		if (!empty($room_ids)) {
            $this->assign('room', D('Bookingroom')->itemsByIds($room_ids));
        }
		$this->assign('list', $list); 
		$this->assign('page', $show); 
		$this->display(); 
	}

    
     public function detail($order_id){
		$Bookingorder = D('Bookingorder');
        if(!$order_id = (int) $order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = $Bookingorder->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法操作');
        }else{
            $shop = D('Booking')->find($detail['shop_id']);
            $list = D('Bookingordermenu')->where(array('order_id'=>$order_id))->select();
            $menu_ids = array();
            foreach($list as $k=>$val){
                $menu_ids[$val['menu_id']] = $val['menu_id'];
            }
            if($menu_ids){
                $this->assign('menus',D('Bookingmenu')->itemsByIds($menu_ids));
            }
            $log = D('Paymentlogs')->where(array('type'=>'ding','order_id'=>$order_id))->find();
			$this->assign('room', D('Bookingroom')->find($detail['room_id']));
            $this->assign('log',$log);
            $this->assign('list',$list);
            $this->assign('shop',$shop);
            $this->assign('detail',$detail);
            $this->display();
        }
	}


   //删除订单
   public function delete($order_id){
	   $obj = D('BookOrder');
       if(!$order_id = (int)$order_id){
           $this->tuMsg('订单不存在');
       }elseif(!$detail = $obj->find($order_id)){
           $this->tuMsg('订单不存在');
       }elseif($detail['user_id'] != $this->uid){
           $this->tuMsg('非法操作订单');
       }elseif($detail['status'] != 0){
           $this->tuMsg('该订单无法删除');
       }else{
		    if(false == $obj->book_delete($order_id)) {
				$this->tuMsg($obj->getError());
			}else{
				$this->tuMsg('订单删除成功', U('book/index', array('status' => 1)));
			}
       }
   }
   
   //最新封装退款
    public function refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $obj = D('BookOrder');
		if(!$detail = $obj->where('order_id =' . $order_id)->find()) {
           $this->tuMsg('错误');
        }elseif($detail['user_id'] != $this->uid) {
           $this->tuMsg('请不要操作他人的订单');
        }elseif($detail['status'] == 8 || $detail['status'] == 4 || $detail['status'] == 3) {
           $this->tuMsg('当前订单状态不支持退款');
        }else{
			if(false == $obj->book_user_refund($order_id)) {//更新什么什么的
				$this->tuMsg($obj->getError());
			}else{
				$this->tuMsg('申请退款成功', U('book/index', array('status' => 3)));
			}
		}
    }
   
  
}
