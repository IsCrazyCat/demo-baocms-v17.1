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
        $status = (int) $this->_param('status');
		$this->assign('status', $status);
		$this->display(); 
    }
    
    public function loaddata() {
		$obj = D('BookOrder');
		import('ORG.Util.Page'); 
		$map = array('closed'=>0); 
		$status = (int) $this->_param('status');
		$this->assign('status', $status);
		if ($status == 0 || empty($status)) { 
			$map['status'] = 0;
		}elseif ($status == 1) {    
			$map['status'] = 1;
		}elseif ($status == 2) {    
			$map['status'] = 2;
		}elseif ($status == 3) {    
			$map['status'] = 3;
		}elseif ($status == 4) {    
			$map['status'] = 4;
		}elseif ($status == 8) {    
			$map['status'] = 8;
		}
		$count = $obj->where($map)->count(); 
		$Page = new Page($count, 10); 
		$show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
            die('0');
		}
		$list = $obj->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $v){
            if($attr = D('Shopcateattr')->where(array('attr_id'=>$v['attr_id']))->find()){
                $list[$k]['attr'] = $attr;
            }
			if($cate = D('Shopcate')->where(array('cate_id'=>$v['cate_id']))->find()){
                $list[$k]['cate'] = $cate;
            }
        }
		$this->assign('list', $list); 
		$this->assign('page', $show); 
		$this->display(); 
	}

    
    public function detail($order_id){
        if(!$order_id = (int)$order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('BookOrder')->find($order_id)){
            $this->error('该订单不存在');
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法的订单操作');
        }else{
           $detail['attr'] = D('Shopcateattr')->where(array('attr_id'=>$detail['attr_id']))->find(); 
           $detail['cate'] = D('Shopcate')->where(array('cate_id'=>$detail['cate_id']))->find();
           $this->assign('detail',$detail);
		   $this->assign('thumb', D('Bookphoto')->getPics($order_id)); 
           $this->display();
        }
    }


	//抢单方法
    public function orders(){
        if(IS_AJAX){
            $order_id = I('order_id',0,'trim,intval');
            $obj = D('BookOrder');
			if($order_id){
				if($detail = $obj-> find($order_id)){
					if($detail['closed'] == 1){
						$this->ajaxReturn(array('status'=>'error','message'=>'该订单已关闭!'));
					}
					if($detail['status'] != 1){
						$this->ajaxReturn(array('status'=>'error','message'=>'订单状态不正确'));
					}
					if(false == $obj->book_orders($order_id,$this->shop_id)) {
						$this->ajaxReturn(array('status'=>'error','message'=>$obj->getError()));
					}else{
						$this->ajaxReturn(array('status'=>'success','message'=>'恭喜您！接单成功！请尽快进服务！'));
					}
				}else{
					$this->ajaxReturn(array('status'=>'error','message'=>'该订单没有详情，暂时无法操作'));
				}
			}else{
				$this->ajaxReturn(array('status'=>'error','message'=>'抱歉，没找到订单号'));
			}
        }
    }
	
	//服务订单完成
    public function complete(){
        if(IS_AJAX){
            $order_id = I('order_id',0,'trim,intval');
            $obj = D('BookOrder');
			if($order_id){
				if($detail = $obj->find($order_id)){
					if($detail['closed'] == 1){
						$this->ajaxReturn(array('status'=>'error','message'=>'该订单已关闭!'));
					}
					if($detail['status'] != 2){
						$this->ajaxReturn(array('status'=>'error','message'=>'订单状态不正确'));
					}
					if(false == $obj->book_complete($order_id,$this->shop_id)) {
						$this->ajaxReturn(array('status'=>'error','message'=>$obj->getError()));
					}else{
						$this->ajaxReturn(array('status'=>'success','message'=>'恭喜您已完成配送'));
					}
				}else{
					$this->ajaxReturn(array('status'=>'error','message'=>'订单详情不存在'));
				}
			}else{
				$this->ajaxReturn(array('status'=>'error','message'=>'没找到订单号'));
			}
        }
    }
	
	
  
}
