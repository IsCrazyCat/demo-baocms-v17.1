<?php
class CityAction extends CommonAction {
    private $create_fields = array('name', 'user_id','pinyin','photo', 'is_open', 'lng', 'lat','orderby','theme','first_letter','domain');
    private $edit_fields = array('name','user_id', 'pinyin','photo', 'is_open', 'lng', 'lat','orderby','theme','first_letter','domain');
    public function index() {
        $City = D('City');
        import('ORG.Util.Page');
        $map = array();
        $keyword = $this->_param('keyword','htmlspecialchars');
        if($keyword){
            $map['name|pinyin'] = array('LIKE', '%'.$keyword.'%');
        }    
        $this->assign('keyword',$keyword);
		 if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if (isset($_GET['is_open']) || isset($_POST['is_open'])) {
            $is_open = $this->_param('is_open', 'htmlspecialchars');
            if ($is_open == 1) {
                $map['is_open'] = 1;
            }else{
				$map['is_open'] = 0;
			}
            $this->assign('is_open', $is_open);
        } else {
            $this->assign('is_open', 999);
        }
        $count = $City->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $City->where($map)->order(array('city_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$agent_ids = $user_ids = array();
		foreach ($list as $k => $val) {
            $val['shop_num'] = $City->get_shop_num($val['city_id']);
			$val['area_num'] = $City->get_area_num($val['city_id']);
			$user_ids[$val['user_id']] = $val['user_id'];
			$agent_ids[$val['agent_id']] = $val['agent_id'];
			$list[$k] = $val;
        }
		$this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('agents', D('Cityagent')->itemsByIds($agent_ids));
        $this->assign('list', $list);
        $this->assign('page', $show); 
        $this->display();
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('City');
            if ($obj->add($data)) {
                 $obj->cleanCache();
                $this->tuSuccess('添加成功', U('city/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('themes',D('Template')->fetchAll());
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('城市名称不能为空');
        } 
		$data['user_id'] = (int) $data['user_id'];
		if(empty($data['user_id'])) {
           $this->tuError('请先选管理账户');
        }
		if(!D('Users')->find($data['user_id'])) {
            $this->tuError('当前账户不存在，请重新选择');
        }
		$data['pinyin'] = htmlspecialchars($data['pinyin']);
        if (empty($data['pinyin'])) {
            $this->tuError('城市拼音不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->tuError('请上传logo');
        }
        $data['is_open'] = (int)($data['is_open']);
		$data['domain'] = (int)($data['domain']);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['first_letter'] = htmlspecialchars($data['first_letter']);
        $data['theme'] = htmlspecialchars($data['theme']);
        $data['orderby'] = (int)($data['orderby']);
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    public function edit($city_id = 0) {
        if ($city_id = (int) $city_id) {
            $obj = D('City');
            if (!$detail = $obj->find($city_id)) {
                $this->tuError('请选择要编辑的城市站点');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['city_id'] = $city_id;
                if (false !== $obj->save($data)) {
                     $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('city/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
				$this->assign('user', D('Users')->where(array('user_id'=>$detail['user_id']))->find());
                $this->assign('themes',D('Template')->fetchAll());
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的城市站点');
        }
    }
	 public function is_open($city_id = 0) {
        if ($city_id = (int) $city_id) {
			if(D('City')->is_open($city_id)){
				$this->tuSuccess('审核成功', U('city/index'));
			}else{
				$this->tuError(D('City')->getError());
			}
        } else {
            $this->tuError('请选择你要审核的站点');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('城市名称不能为空');
        } 
		$data['user_id'] = (int) $data['user_id'];
		if(empty($data['user_id'])) {
           $this->tuError('请先选管理账户');
        }
		if(!D('Users')->find($data['user_id'])) {
            $this->tuError('当前账户不存在，请重新选择');
        }
		$data['pinyin'] = htmlspecialchars($data['pinyin']);
        if (empty($data['pinyin'])) {
            $this->tuError('城市拼音不能为空');
        }
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->tuError('请上传logo');
        }
        $data['is_open'] = (int)($data['is_open']);
		$data['domain'] = (int)($data['domain']);
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['first_letter'] = htmlspecialchars($data['first_letter']);
        $data['orderby'] = (int)($data['orderby']);
        $data['theme'] = htmlspecialchars($data['theme']);
        return $data;
    }

    public function delete($city_id = 0) {
        if ($city_id = (int) $city_id) {
            $obj = D('City');
			if(!D('Area')->where(array('city_id'=>$city_id))->find()){
				if($obj->delete($city_id)){
					$obj->cleanCache();
           		    $this->tuSuccess('删除成功', U('city/index'));
				}else{
					$this->tuError('删除城市更新数据库失败');
				}
			}else{
				$this->tuError('当前城市下面还有地区');
			}
        } else {
            $this->tuError('请选择要删除的城市站点');
        }
    }

	//城市入驻
	public function apply($city_id = 0){
        if ($city_id = (int) $city_id){
            $obj = D('City');
			if ($this->isPost()) {
				$agent_id = (int) $this->_post('agent_id');
				if (empty($agent_id)) {
					$this->tuError('请选择代理商');
				}
				if($Cityagent = D('Cityagent')->find($agent_id)){
					if($Cityagent['parent_id'] == 0){
						$this->tuError('不能选择一级代理');
					}else{
						if($obj->save(array('city_id' => $city_id, 'agent_id' => $agent_id))){
							$this->tuSuccess('选择代理商成功', U('city/index'));
						}else{
							$this->tuError('操作失败');
						}
					}
				}else{
					$this->tuError('代理商不存在');
				}
			} else {
        		$this->assign('agents', $agents = D('Cityagent')->fetchAll());
				$this->assign('city_id', $city_id);
				$this->display();
			}
        } else {
            $this->tuError('参数错误');
        }
    }
	
}
