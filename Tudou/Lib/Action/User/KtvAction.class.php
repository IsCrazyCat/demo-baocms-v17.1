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
		$map = array('user_id' => $this->uid,'closed'=>0); 
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
			if($room = D('KtvRoom')->where(array('room_id'=>$v['room_id']))->find()){
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
        }elseif($detail['user_id'] != $this->uid){
            $this->error('非法的订单操作');
        }else{
           $detail['room'] = D('KtvRoom')->where(array('room_id'=>$detail['room_id']))->find(); 
           $detail['ktv'] = D('Ktv')->where(array('ktv_id'=>$detail['ktv_id']))->find();
           $this->assign('detail',$detail);
           $this->display();
        }
    }


	//KTV订单已付款二维码
	 public function qrcode(){
        $order_id = $this->_get('order_id');
        if (!($detail = D('KtvOrder')->find($order_id))) {
            $this->error('没有该订单');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error("非法操作！");
        }
        if ($detail['status'] != 1 || $detail['is_used_code'] != 0) {
            $this->error('该订单未付款或者已验证');
        }
        $url = U('seller/ktv/used', array('order_id' => $order_id, 't' => NOW_TIME, 'sign' => md5($order_id . C('AUTH_KEY') . NOW_TIME)));
        $token = 'ktv_order_id_' . $order_id;
        $file = tuQrCode($token, $url);
        $this->assign('file', $file);
        $this->assign('detail', $detail);
        $this->display();
    }
   //删除订单
   public function delete($order_id){
	   $obj = D('KtvOrder');
       if(!$order_id = (int)$order_id){
           $this->tuMsg('订单不存在');
       }elseif(!$detail = $obj->find($order_id)){
           $this->tuMsg('订单不存在');
       }elseif($detail['user_id'] != $this->uid){
           $this->tuMsg('非法操作订单');
       }elseif($detail['status'] != 0){
           $this->tuMsg('该订单无法删除');
       }else{
           if($obj->where(array('order_id'=>$order_id))->save(array('closed'=>1))){
			    D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 1,$type = 8,$status = 11);
			    D('Weixinmsg')->weixinTmplOrderMessage($order_id,$cate = 2,$type = 8,$status = 11);
               $this->tuMsg('订单删除成功');
           }else{
               $this->tuMsg('订单删除失败');
           }
       }
   }
   
   //最新封装退款
    public function refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $obj = D('KtvOrder');
		if(!$detail = $obj->where('order_id =' . $order_id)->find()) {
           $this->tuMsg('错误');
        }elseif($detail['user_id'] != $this->uid) {
           $this->tuMsg('请不要操作他人的订单');
        }elseif($detail['status'] == 8 || $detail['status'] == 4 || $detail['status'] == 3) {
           $this->tuMsg('当前订单状态不支持退款');
        }else{
			if(false == $obj->ktv_user_refund($order_id)) {//更新什么什么的
				$this->tuMsg($obj->getError());
			}else{
				$this->tuMsg('申请退款成功', U('ktv/index', array('status' => 3)));
			}
		}
    }
   
  
}
