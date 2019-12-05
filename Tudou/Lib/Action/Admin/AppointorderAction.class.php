<?php
class  AppointorderAction extends CommonAction{
	
	public function _initialize(){
        parent::_initialize();
        $this->getType = D('Appointorder')->getType();
        $this->assign('types', $this->getType);
		$this->assign('orderTypes', $orderTypes = D('Appoint')->getAppoinOrderType());
    }
	
	
	
    public function index(){
        $Appointorder = D('Appointorder');
        import('ORG.Util.Page'); 
        $map = array('closed'=>0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|tel|contents'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        } 
		//按照家政筛选订单
		if($appoint_id = (int) $this->_param('appoint_id')){
            $map['appoint_id'] = $appoint_id;
            $this->assign('appoint', $Appoint = D('Appoint')->find($appoint_id));
            $this->assign('appoint_id', $appoint_id);
        }
		
		if($shop_id = (int) $this->_param('shop_id')){
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if($user_id = (int) $this->_param('user_id')){
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        }else{
            if($bg_date = $this->_param('bg_date', 'htmlspecialchars')){
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if($end_date = $this->_param('end_date', 'htmlspecialchars')){
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if(isset($_GET['status']) || isset($_POST['status'])){
            $status = (int) $this->_param('status');
            if($status != 999){
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }
		
		if(isset($_GET['orderType']) || isset($_POST['orderType'])){
            $orderType = (int) $this->_param('orderType');
            if($orderType != 999){
                $map['orderType'] = $orderType;
            }
            $this->assign('orderType', $orderType);
        }else{
            $this->assign('orderType', 999);
        }
		
		
        if($cate_id = (int) $this->_param('cate_id')){
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Appointorder ->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $list = $Appointorder->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$shop_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }		
        $this->assign('list', $list); 
        $this->assign('page', $show); 
		$this->assign('cates', D('Appointcate')->fetchAll());
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->display(); 
    }
    
    public function edit($order_id){
        if ($order_id = (int) $order_id) {
            $obj = D('Appointorder');
            if (!$detail = $obj->find($order_id)) {
                $this->boError('请选择要编辑的家政');
            }
            if ($this->isPost()) {
                $data['is_real'] = (int)$this->_post('is_real');
                $data['num']     = (int)  $this->_post('num');
                $data['money']    = (int) ($this->_post('money')*100);
                $data['order_id'] = $order_id;
                if (false !== $obj->save($data)) {
                    $this->boSuccess('操作成功', U('appointorder/index'));
                }
                $this->boError('操作失败');
            } else {
    
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->boError('请选择要编辑的活动');
        }
        
        
    }
    
     public function delete($order_id = 0) {
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Appointorder');
            $obj->delete($order_id);
            $this->boSuccess('删除成功', U('appointorder/index'));
        } else {
            $order_id = $this->_post('order_id', false);
            if (is_array($order_id)) {
                $obj = D('Appointorder');
                foreach ($order_id as $id) {
                    $obj->delete($id);
                }
                $this->boSuccess('删除成功', U('appointorder/index'));
            }
            $this->boError('请选择要删除的预约');
        }
    }
	
	
    
}