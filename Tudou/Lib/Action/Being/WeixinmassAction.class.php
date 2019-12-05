<?php
class WeixinmassAction extends CommonAction{
	 public function index() {
        $obj = D('WeixinMass');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($mass_id = (int) $this->_param('mass_id')) {
            $map['mass_id'] = $mass_id;
            $this->assign('mass_id', $mass_id);
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
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 100); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('mass_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
	
	 public function delete($mass_id = 0) {
        if (is_numeric($mass_id) && ($mass_id = (int) $mass_id)) {
            $obj = D('WeixinMass');
            $obj->delete($mass_id);
            $this->tuSuccess('删除群发消息成功', U('weixinmass/index'));
        } else {
            $mass_id = $this->_post('mass_id', false);
            if (is_array($mass_id)) {
                $obj = D('WeixinMass');
                foreach ($mass_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除群发消息成功', U('weixinmass/index'));
            }
            $this->tuError('请选择要删除群发消息');
        }
    }
	//预览
	public function preview($mass_id = 0) {
		if($mass_id = (int) $mass_id){
			$obj = D('WeixinMass');
			if(false == $obj->preview($mass_id)) {
				$this->tuError($obj->getError());
			}else{
				$this->tuSuccess('预览成功，请到手机上查看', U('weixinmass/index'));
			}
		}else{
			$this->tuError('请选择您要预览的信息');
		}
    }
	
	//发送
	public function send($mass_id = 0) {
		if($mass_id = (int) $mass_id){
			$obj = D('WeixinMass');
			if(false == $obj->send($mass_id)) {
				$this->tuError($obj->getError());
			}else{
				$this->tuSuccess('发送成功', U('weixinmass/index'));
			}
		}else{
			$this->tuError('发送失败');
		}
      
    }
}