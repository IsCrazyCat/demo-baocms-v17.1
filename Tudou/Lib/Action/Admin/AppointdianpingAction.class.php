<?php
class AppointdianpingAction extends CommonAction {

    private $create_fields = array('user_id', 'reply','order_id', 'appoint_id', 'score','d1', 'd2','d3', 'content', 'show_date');
    private $edit_fields = array('user_id', 'reply', 'order_id','appoint_id', 'score','d1', 'd2','d3', 'content', 'show_date');

    public function index() {
        $Appointdianping = D('Appointdianping');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0);
       
        if ($dianping_id = (int) $this->_param('dianping_id')) {
            $map['dianping_id'] = $dianping_id;
            $this->assign('dianping_id', $dianping_id);
        }

        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $user = D('Users')->find($user_id);
            $this->assign('nickname', $user['nickname']);
            $this->assign('user_id', $user_id);
        }
		
        $count = $Appointdianping->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Appointdianping->where($map)->order(array('dianping_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
           
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Appointdianping');
            if ($dianping_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                $local = array();
                foreach ($photos as $val) {
                    if (isImage($val))
                        $local[] = $val;
                }
                if (!empty($local))
                    D('Appointdianpingpics')->upload($dianping_id, $local,$data['order_id']);
                $this->tuSuccess('添加成功', U('Appointdianping/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
        $data['order_id'] = (int) $data['order_id'];
        if (empty($data['order_id'])) {
            $this->tuError('家政订单号不能为空');
        }
        if (!$order = D('Appointorder')->find($data['order_id'])) {
            $this->tuError('家政订单不存在');
        }
		$data['shop_id'] = $order['shop_id'];
		$data['appoint_id'] = $order['appoint_id'];
        $data['score'] = (int) $data['score'];
        if (empty($data['score'])) {
            $this->tuError('评分不能为空');
        }
        if ($data['score'] > 5 || $data['score'] < 1) {
            $this->tuError('评分为1-5之间的数字');
        }
		
		$config = $config = D('Setting')->fetchAll();
		$data['d1'] = (int) $data['d1'];
		if(empty($data['d1'])){
			$this->tuError($config['appoint']['d1'].'评分不能为空');
		}
		if($data['d1'] > 5 || $data['d1'] < 1){
			$this->tuError($config['appoint']['d1'].'格式不正确');
		}
		$data['d2'] = (int) $data['d2'];
		if(empty($data['d2'])){
			$this->tuError($config['appoint']['d2'].'评分不能为空');
		}
		if($data['d2'] > 5 || $data['d2'] < 1){
			$this->tuError($config['appoint']['d2'].'格式不正确');
		}
		$data['d3'] = (int) $data['d3'];
		if(empty($data['d3'])){
			$this->tuError($config['appoint']['d3'].'评分不能为空');
		}
		if($data['d3'] > 5 || $data['d3'] < 1){
			$this->tuError($config['appoint']['d3'].'格式不正确');
		}

        $data['contents'] = htmlspecialchars($data['contents']);
        if (empty($data['contents'])) {
            $this->tuError('评价内容不能为空');
        }
        $data['show_date'] = htmlspecialchars($data['show_date']);
        if (empty($data['show_date'])) {
            $this->tuError('生效日期不能为空');
        }
        if (!isDate($data['show_date'])) {
            $this->tuError('生效日期格式不正确');
        }
        $data['reply'] = htmlspecialchars($data['reply']);
		if($data['reply']){
			$data['reply_time'] = time();
        	$data['reply_ip'] = get_client_ip();
		}
		
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

       public function edit($dianping_id = 0) {
        if ($dianping_id = (int) $dianping_id) {
            $obj = D('Appointdianping');
            if (!$detail = $obj->find($dianping_id)) {
                $this->tuError('请选择要编辑的家政点评');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['dianping_id'] = $dianping_id;
                if (false !== $obj->save($data)) {
                    $photos = $this->_post('photos', false);
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val))
                            $local[] = $val;
                    }
                    if (!empty($local))
                        D('Appointdianpingpics')->upload($dianping_id, $local,$detail['order_id']);
						D('Users')->prestige($this->uid, 'dianping');
                        D('Users')->updateCount($this->uid, 'ping_num');
					
                    $this->tuSuccess('操作成功', U('Appointdianping/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('photos', D('Appointdianpingpics')->getPics($dianping_id));
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的家政点评');
            
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
		$data['dianping_id'] = $dianping_id;
        if (empty($data['user_id'])) {
            $this->tuError('用户不能为空');
        }
        $data['score'] = (int) $data['score'];
        if (empty($data['score'])) {
            $this->tuError('评分不能为空');
        }
		
		
		$config = $config = D('Setting')->fetchAll();
		$data['d1'] = (int) $data['d1'];
		if(empty($data['d1'])){
			$this->tuError($config['appoint']['d1'].'评分不能为空');
		}
		if($data['d1'] > 5 || $data['d1'] < 1){
			$this->tuError($config['appoint']['d1'].'格式不正确');
		}
		$data['d2'] = (int) $data['d2'];
		if(empty($data['d2'])){
			$this->tuError($config['appoint']['d2'].'评分不能为空');
		}
		if($data['d2'] > 5 || $data['d2'] < 1){
			$this->tuError($config['appoint']['d2'].'格式不正确');
		}
		$data['d3'] = (int) $data['d3'];
		if(empty($data['d3'])){
			$this->tuError($config['appoint']['d3'].'评分不能为空');
		}
		if($data['d3'] > 5 || $data['d3'] < 1){
			$this->tuError($config['appoint']['d3'].'格式不正确');
		}
		
		

        $data['contents'] = htmlspecialchars($data['contents']);
        if (empty($data['contents'])) {
            $this->tuError('评价内容不能为空');
        }
        $data['show_date'] = htmlspecialchars($data['show_date']);
        if (empty($data['show_date'])) {
            $this->tuError('生效日期不能为空');
        }
        if (!isDate($data['show_date'])) {
            $this->tuError('生效日期格式不正确');
        }
		
		
        $data['reply'] = htmlspecialchars($data['reply']);
		if($data['reply']){
			$data['reply_time'] = time();
        	$data['reply_ip'] = get_client_ip();
		}
		
		
		
        return $data;
    }


	
	 public function delete($dianping_id = 0) {
        if (is_numeric($dianping_id) && ($dianping_id = (int) $dianping_id)) {
            $obj = D('Appointdianping');
            $obj->save(array('dianping_id' => $dianping_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('Appointdianping/index'));
        } else {
            $dianping_id = $this->_post('dianping_id', false);
            if (is_array($dianping_id)) {
                $obj = D('Appointdianping');
                foreach ($dianping_id as $id) {
                    $obj->save(array('dianping_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('批量删除成功', U('Appointdianping/index'));
            }
            $this->tuError('请选择要删除的点评');
        }
    }

}
