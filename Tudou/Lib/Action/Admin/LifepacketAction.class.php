<?php


class LifepacketAction extends CommonAction{
	
	public function _initialize(){
        parent::_initialize();
        $this->assign('statuss',$getStatus = D('LifePacket')->getStatus());
    }
	
	
    public function index(){
        $obj = D('LifePacket');
        import('ORG.Util.Page');
        $map = array();
		if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['packet_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if($user_id = (int) $this->_param('user_id')) {
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
		
		if(isset($_GET['packet_is_command']) || isset($_POST['packet_is_command'])){
            $packet_is_command = (int) $this->_param('packet_is_command');
            if($packet_is_command != 999) {
                $map['packet_is_command'] = $packet_is_command;
            }
            $this->assign('packet_is_command', $packet_is_command);
        }else{
            $this->assign('packet_is_command', 999);
        }
		
		
		if(isset($_GET['status']) || isset($_POST['status'])) {
            $status = (int) $this->_param('status');
            if($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        } else {
            $this->assign('status', 999);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('packet_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $life_ids = $user_ids = array();
        foreach ($list as $val) {
            $life_ids[$val['life_id']] = $val['life_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('lifes', D('Life')->itemsByIds($life_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	public function logs(){
        $obj = D('LifePacketLogs');
        import('ORG.Util.Page');
        $map = array();
		if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['log_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
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
		if($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $life_ids = $user_ids = array();
        foreach ($list as $val) {
            $life_ids[$val['life_id']] = $val['life_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('lifes', D('Life')->itemsByIds($life_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	//红包退款
	public function refund($packet_id= 0){
        if($packet_id = (int) $packet_id){
            $detail = D('LifePacket')->find($packet_id);
            if($detail['status'] != 3){
                $this->tuError('红包状态不正确');
            }
			if($detail['packet_surplus_money'] <= 0){
                $this->tuError('红包里面没余额了');
            }
			if($detail['packet_sold_num'] == $detail['packet_num']){
				$this->tuError('该红包不能申请退款');
			}
            if($detail['status'] == 3){
                if(D('LifePacket')->save(array('packet_id' => $packet_id, 'status' => 4))) {
                    if($detail['packet_surplus_money'] > 0) {
                        D('Users')->addMoney($detail['user_id'], $detail['packet_surplus_money'], '红包ID'.$packet_id.'退余款');
						 $this->tuSuccess('恭喜您，红包退出成功', U('lifepacket/index'));
                    }else{
						$this->tuError('该红包退款金额不正确');
					}
                }
            }
			$this->tuError('退款失败');
        }
       
    }
	
	
	
}