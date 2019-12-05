<?php
//积分公用
class IntegralAction extends CommonAction{
	
	private $library_create_fields = array('user_id', 'integral_library', 'integral_library_surplus', 'integral_library_total', 'intro');
    private $library_edit_fields = array('user_id', 'integral_library', 'integral_library_surplus', 'integral_library_total', 'intro');
	
	
    public function library(){
        $obj = D('Userintegrallibrary');
        import('ORG.Util.Page');
        $map = array('closed'=>0);
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
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('library_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	//添加
	 public function library_create(){
        if ($this->isPost()) {
            $data = $this->library_createCheck();
            $obj = D('Userintegrallibrary');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('integral/library'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }

    private function library_createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->library_create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
        $data['integral_library'] = (int) $data['integral_library'];
		if (empty($data['integral_library'])) {
            $this->tuError('积分库不能为空');
        }
		$data['integral_library_surplus'] = (int) $data['integral_library'];//剩余积分库相同
		
		$data['integral_library_total'] = (int) $data['integral_library_total'];
		if (empty($data['integral_library_total'])) {
            $this->tuError('返还总天数不能为空');
        }
		$data['integral_library_day'] = round(($data['integral_library']/$data['integral_library_total']),2);//剩余积分库相同
		if(($data['integral_library_day']*$data['integral_library_total']) != $data['integral_library']){
			$this->tuError('填写的积分总数除以天数不为整数');
		}
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('活动简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('活动简介含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function library_edit($library_id = 0){
        if ($library_id = (int) $library_id) {
            $obj = D('Userintegrallibrary');
            if (!($detail = $obj->find($library_id))) {
                $this->tuError('请选择要编辑的活动');
            }
            if ($this->isPost()) {
                $data = $this->library_editCheck();
                $data['library_id'] = $library_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('integral/library'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的活动');
        }
    }
	
	 public function library_delete($library_id = 0){
        $library_id = (int) $library_id;
		if($library_id){
            $obj = D('Userintegrallibrary');
			if($detail = $obj->find($library_id)){
				if($detail['integral_library_total_success'] != 0){
					$this->tuError('积分已经开始返还不能再删除');
				}
				if($obj->save(array('library_id' => $library_id, 'closed' => 1))){
					$this->tuSuccess('删除成功', U('integral/library'));
				}else{
					$this->tuError('删除失败');
				}
			}else{
				$this->tuError('没有找到积分库');
			}
        }else{
            $this->tuError('请选择要删除的活动');
        }
    }
	
    private function library_editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->library_edit_fields);
         $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
        $data['integral_library'] = (int) $data['integral_library'];
		if (empty($data['integral_library'])) {
            $this->tuError('积分库不能为空');
        }
		$data['integral_library_surplus'] = (int) $data['integral_library'];//剩余积分库相同
		
		$data['integral_library_total'] = (int) $data['integral_library_total'];
		if (empty($data['integral_library_total'])) {
            $this->tuError('返还总天数不能为空');
        }
		$data['integral_library_day'] = round(($data['integral_library']/$data['integral_library_total']),2);//剩余积分库相同
		if(($data['integral_library_day']*$data['integral_library_total']) != $data['integral_library']){
			$this->tuError('填写的积分总数除以天数不为整数');
		}
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('活动简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('活动简介含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	//返还列表
	 public function restore($library_id = 0){
		$library_id = (int) $library_id;
        $obj = D('Userintegralrestore');
        import('ORG.Util.Page');
        $map = array('closed'=>0);
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
		if ($library_id) {
            $library = D('Userintegrallibrary')->find($library_id);
            $this->assign('library', $library);
            $map['library_id'] = $library_id;
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('restore_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	//商家核销积分列表
	 public function cancel(){
        $obj = D('Userintegralcancel');
        import('ORG.Util.Page');
        $map = array('closed'=>0);
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
		if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('cancel_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
			$shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
}