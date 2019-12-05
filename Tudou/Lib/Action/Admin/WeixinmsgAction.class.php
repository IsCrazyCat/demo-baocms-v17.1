<?php
class WeixinmsgAction extends CommonAction{
	 public function index() {
        $Weixinmsg = D('Weixinmsg');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($msg_id = (int) $this->_param('msg_id')) {
            $map['msg_id'] = $msg_id;
            $this->assign('msg_id', $msg_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if (($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) && ($end_date = $this -> _param('end_date', 'htmlspecialchars'))) {
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$map['create_time'] = array( array('ELT', $end_time), array('EGT', $bg_time));
			$this -> assign('bg_date', $bg_date);
			$this -> assign('end_date', $end_date);
		} else {
			if ($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) {
				$bg_time = strtotime($bg_date);
				$this -> assign('bg_date', $bg_date);
				$map['create_time'] = array('EGT', $bg_time);
			}
			if ($end_date = $this -> _param('end_date', 'htmlspecialchars')) {
				$end_time = strtotime($end_date);
				$this -> assign('end_date', $end_date);
				$map['create_time'] = array('ELT', $end_time);
			}
		}
        $count = $Weixinmsg->where($map)->count(); 
        $Page = new Page($count, 100); 
        $show = $Page->show(); 
        $list = $Weixinmsg->where($map)->order(array('msg_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
			if($serial = D('Weixintmpl')-> where(array('serial'=>$val['serial']))->find()){
				  $list[$k]['serial'] = $serial;
			}
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	
	 public function delete($msg_id = 0) {
        if (is_numeric($msg_id) && ($msg_id = (int) $msg_id)) {
            $obj = D('Weixinmsg');
            $obj->delete($msg_id);
            $this->tuSuccess('删除模板消息成功', U('weixinmsg/index'));
        } else {
            $msg_id = $this->_post('msg_id', false);
            if (is_array($msg_id)) {
                $obj = D('Weixinmsg');
                foreach ($msg_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除模板消息成功', U('weixinmsg/index'));
            }
            $this->tuError('请选择要删除模板消息');
        }
    }
	public function delete_drop() {
        D('Weixinmsg')->where('msg_id','gt',0)->delete();
        $this->tuSuccess('清空模板消息成功', U('weixinmsg/index'));
    }
}