<?php
class BookAction extends CommonAction {
   public function _initialize() {
        parent::_initialize();
        $this->assign('getTypes', D('BookOrder')->getType());//订单状态
    }
	
	//服务预约订单
	public function order(){
        $obj = M('BookOrder'); 
        import('ORG.Util.Page');
		$map = array('closed'=>0);
		if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
		if (isset($_GET['status']) || isset($_POST['status'])) {
            $status = (int) $this->_param('status');
            if ($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        } else {
            $this->assign('status', 999);
        }
        $count = $obj->where($map)->count();
        $Page  = new Page($count,25);
        $show  = $Page->show();
        $list = $obj->where($map)->order('order_id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($list as $k => $v){
            $attr = D('Shopcateattr') -> where(array('attr_id'=>$v['attr_id'])) -> find();
            $list[$k]['attr'] = $attr;
			$cate = D('Shopcate') -> where(array('cate_id'=>$v['cate_id'])) -> find();
            $list[$k]['cate'] = $cate;
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display(); 
    }
	//取消服务预约订单
	 public function order_delete($order_id){
		$obj = D('BookOrder');
        if($order_id = (int) $order_id){
            if(!$order = $obj->find($order_id)){
                $this->tuError('订单不存在');
            }elseif($order['status'] != 0){
                $this->tuError('该订单已删除');
            }else{
				if(false == $obj->book_delete($order_id)) {
					$this->tuError($obj->getError());
				}else{
					$this->tuSuccess('订单删除成功',U('book/order'));
				}
            }
        }else{
            $this->tuError('请选择要删除的订单');
        }
    }
	
	 //网站后台同意退款操作
    public function order_agree_refund($order_id){
        if($order_id = (int) $order_id){
			$obj = D('BookOrder');
			if(!$detail = $obj->where('order_id =' . $order_id)->find()) {
			   $this->tuError('没有找到该订单');
			}elseif($detail['status'] != 3) {
			   $this->tuError('当前状态不永许退款');
			}else{
				if(false == $obj->book_agree_refund($order_id)) {
					$this->tuError($obj->getError());
				}else{
					$this->tuSuccess('退款操作成功', U('book/order'));
				}
			}
		}else{
			$this->tuError('请选择要退款的订单');
		}
    }
	

}
