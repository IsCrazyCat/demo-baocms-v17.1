<?php
class AgentAction extends CommonAction{
	public function _initialize(){
        parent::_initialize();
    }
    public function index(){
        $obj = D('UsersAgentApply');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($order_id = (int) $this->_param('order_id')) {
            $map['order_id'] = $order_id;
            $this->assign('order_id', $order_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
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
        if (isset($_GET['type']) || isset($_POST['type'])) {
            $type = (int) $this->_param('type');
            if ($type != 999) {
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        } else {
            $this->assign('type', 999);
        }
        $count =  $obj ->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list =  $obj ->where($map)->order(array('apply_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $agent_ids = $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
			$agent_ids[$val['agent_id']] = $val['agent_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('agents', D('Cityagent')->itemsByIds($agent_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	public function delete($apply_id = 0){
        if ($apply_id = (int) $apply_id){
            $obj = D('UsersAgentApply');
			if(!$detail = $obj->find($apply_id)){
				$this->tuError('申请订单不存在');
		    }elseif($detail['type'] == 1){
				$this->tuError('余额付款不支持删除');
			}else{
				if($obj->save(array('apply_id' => $apply_id, 'closed' => 1))){
					$this->tuSuccess('取消订单成功', U('agent/index'));
				}else{
					$this->tuError('更新数据库失败');
				}
			}
        } else {
            $this->tuError('请选择要取消的代理申请');
        }
    }
	
	public function audit($apply_id = 0){
        if ($apply_id = (int) $apply_id){
            $obj = D('UsersAgentApply');
			if(!$detail = $obj->find($apply_id)){
				$this->tuError('不存在的申请');
		    }elseif($detail['closed'] == 1){
				$this->tuError('该申请已删除');
			}else{
				if(false != $obj->AgentApplyAudit($apply_id)){
					$this->tuSuccess('审核申请成功', U('agent/index'));
				}else{
					$this->tuError($obj->getError());
				}
			}
        } else {
            $this->tuError('请选择要审核的代理申请');
        }
    }
	
	
}