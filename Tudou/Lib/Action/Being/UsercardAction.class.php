<?php
class UsercardAction extends CommonAction{
    public function index(){
        $obj = D('Usercard');
        import('ORG.Util.Page');
        $map = array();
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['card_num|user_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
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
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('card_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
             if($Users = D('Users')->where(array('user_id'=>$val['user_id']))->find()){
                $list[$k]['user'] = $Users;
             }
			 if($Userrank = D('Userrank')->where(array('rank_id'=>$Users['rank_id']))->find()){
                $list[$k]['rank'] = $Userrank;
             }
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
	
	
	
    public function create(){
        if ($this->isPost()) {
            $card_num = $this->_post('card_num', 'htmlspecialchars');
            $user_id = (int) $this->_post('user_id');
            if(empty($card_num)) {
                $this->tuError('卡号不能为空');
            }
            if(D('Usercard')->checkCard($card_num)) {
               $this->tuError('卡号已经存在');
            }
			$end_date = $this->_post('end_date', 'htmlspecialchars');
			if(!isDate($end_date)) {
				$this->tuError('会员卡有效期不正确');
			}
			if($count = D('Usercard')->where(array('user_id'=>$user_id))->count()) {
				$this->tuError('该会员已经有绑卡');
			}
            $data = array('user_id' => $user_id, 'card_num' => $card_num,'end_date' => $end_date,'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
            if (D('Usercard')->add($data)) {
                $this->tuSuccess('录入卡号成功', U('usercard/create'));
            }
            $this->tuError('录入失败');
        } else {
            $this->display();
        }
    }
	
	
    public function edit($card_id = 0){
		if($card_id = (int) $card_id){
			if(!$detail = D('Usercard')->find($card_id)){
				$this->tuError('会员卡不存在');
			}
			if ($this->isPost()) {
				$card_num = $this->_post('card_num', 'htmlspecialchars');
				if(empty($card_num)){
					$this->tuError('卡号不能为空');
				}
				$user_id = (int) $this->_post('user_id');
				if(empty($user_id)){
					$this->tuError('会员必选');
				}
				$count = D('Usercard')->where(array('user_id'=>$user_id))->count();
				if($count >= 2) {
					$this->tuError('该会员已经有绑卡');
				}
				$end_date = $this->_post('end_date', 'htmlspecialchars');
				if(!isDate($end_date)) {
					$this->tuError('会员卡有效期不正确');
				}
				$data = array('card_id' => $card_id,'user_id' => $user_id, 'card_num' => $card_num, 'end_date' => $end_date,'update_time' => NOW_TIME);
				if(D('Usercard')->save($data)){
					$this->tuSuccess('编辑会员卡成功', U('usercard/index'));
				}
				$this->tuError('编辑失败');
			}else{
				$this->assign('detail', $detail);
				$this->assign('user', D('Users')->where(array('user_id'=>$detail['user_id']))->find());
				$this->display();
        	}
		}else{
            $this->tuError('请选择要编辑的会员卡');
        }
    }
	
    public function delete($card_id = 0){
        if (is_numeric($card_id) && ($card_id = (int) $card_id)) {
            $obj = D('Usercard');
            $obj->delete($card_id);
            $this->tuSuccess('删除成功', U('usercard/index'));
        } else {
            $card_id = $this->_post('card_id', false);
            if (is_array($card_id)) {
                $obj = D('Usercard');
                foreach ($card_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('usercard/index'));
            }
            $this->tuError('请选择要删除的会员卡');
        }
    }
}