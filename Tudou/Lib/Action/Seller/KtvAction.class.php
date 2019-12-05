<?php
class KtvAction extends CommonAction {
	protected function _initialize(){
       parent::_initialize();
	   $this->assign('getTypes', D('KtvOrder')->getType());//订单状态
    }
  
    public function index() {
        $status = (int) $this->_param('status');
		$this->assign('status', $status);
		$this->display(); 
    }
    
    public function loaddata() {
		$obj = D('KtvOrder');
		import('ORG.Util.Page'); 
		$map = array('closed' => 0,'shop_id'=>$this->shop_id);  
		$status = (int) $this->_param('status');
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
            if($ktv = D('Ktv')->where(array('ktv_id'=>$v['ktv_id']))->find()){
                $list[$k]['ktv'] = $ktv;
            }
			if($room = D('KtvRoom')->where(array('room'=>$v['room']))->find()){
                $list[$k]['room'] = $room;
            }
        }
		$this->assign('list', $list); 
		$this->assign('page', $show); 
		$this->display(); 
	}

    
    public function detail($order_id){
        if(!$order_id = (int)$order_id){
            $this->error('该订单不存在');
        }elseif(!$detail = D('KtvOrder')->find($order_id)){
            $this->error('该订单不存在');
        }else{
           $detail['room'] = D('KtvRoom')->where(array('room_id'=>$detail['room_id']))->find(); 
           $detail['ktv'] = D('Ktv')->where(array('ktv_id'=>$detail['ktv_id']))->find();
           $this->assign('detail',$detail);
           $this->display();
        }
    }


   //删除订单
   public function delete($order_id){
	   $obj = D('KtvOrder');
       if(!$order_id = (int)$order_id){
           $this->tuMsg('订单不存在');
       }elseif(!$detail = $obj->find($order_id)){
           $this->tuMsg('订单不存在');
       }elseif($detail['shop_id'] != $this->shop_id){
           $this->tuMsg('非法操作订单');
       }elseif($detail['status'] != 0){
           $this->tuMsg('该订单无法删除');
       }else{
           if($obj->where(array('order_id'=>$order_id))->save(array('closed'=>1))){
			    D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 8,$status = 11);
				D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 1,$status = 11);
               $this->tuMsg('订单删除成功');
           }else{
               $this->tuMsg('订单删除失败');
           }
       }
   }
   
   //验证码
    public function check() {
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            if (empty($code)) {
                $this->tuMsg('请输入验证码');
            }
            $obj = D('KtvOrder');
            if(!$detail = $obj->where(array('code'=> $code))->find()){
			   $this->tuMsg('该订单不存在或者验证码错误');
		    }
			if($detail['status'] !=1){
			   $this->tuMsg('该订单状态不正确');
		    }
			if($detail['is_used_code'] !=0){
			   $this->tuMsg('该验证码已使用');
		    }
			if($detail['shop_id'] != $this->shop_id){
			   $this->tuMsg('非法操作');
		    }
			if(false !== $obj->complete($detail['order_id'])){
				$this->tuMsg('验证成功', U('ktv/index',array('status'=>1)));
			}else{
				$this->tuMsg('操作失败');
		    }
        } else {
            $this->display();
        }
    }
	
	//核销二维码
	public function used(){
        $order_id = (int) $this->_param('order_id');
        $obj = D('KtvOrder');
		if(!$detail = $obj->where(array('order_id'=> $order_id))->find()){
		   $this->error('订单不存在');
		}
		if($detail['status'] !=1){
			$this->error('该订单状态不正确');
		}
		if($detail['is_used_code'] !=0){
			$this->error('该验证码已使用');
		}
		if($detail['shop_id'] != $this->shop_id){
			$this->error('非法操作');
		}
		if(false !== $obj->complete($detail['order_id'])){
			$this->error('验证成功', U('ktv/index',array('status'=>1)));
		}else{
			$this->error('操作失败');
		}
    }
    //最新商家同意封装退款
    public function agree_refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $obj = D('KtvOrder');
		if(!$detail = $obj->where(array('order_id'=>$order_id))->find()) {
		   $this->tuMsg('错误');
        }elseif($detail['shop_id'] != $this->shop_id) {
           $this->tuMsg('请不要操作他人的订单');
        }elseif($detail['status'] != 3) {
           $this->tuMsg('当前订单不在退款状态');
        }else{
			if(false == $obj->ktv_agree_refund($order_id)) {//更新什么什么的
				$this->tuMsg($obj->getError());
			}else{
				$this->tuMsg('恭喜您同意退款成功，款项已退还到会员余额', U('ktv/index', array('status' => 4)));
			}
		}
    }
  
}
