<?php
class StockgroupAction extends CommonAction {
    private $create_fields = array('group_name','group_id','jury_id','intro','orderby');
    private $edit_fields = array('group_name','group_id','jury_id','intro','orderby');
	private $team_id = '';
    public function _initialize(){
        parent::_initialize();
        $this->jury_id = (int) $_REQUEST['jury_id'];
        if (!$this->jury_id) {
            $this->error('请选择对应的团队');
        }
        $this->assign('jury_id', $this->jury_id);
    }
    public function index() {
        $obj = D('Stockgroup');
        import('ORG.Util.Page');
        $map = array(array('jury_id' => $this->jury_id));
        $keyword = $this->_param('keyword','htmlspecialchars');
        if($keyword){
            $map['group_name'] = array('LIKE', '%'.$keyword.'%');
        }    
        $this->assign('keyword',$keyword);
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('group_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$jury_ids = $team_ids = array();
        foreach ($list as $key => $val) {
            $jury_ids[$val['jury_id']] = $val['jury_id'];
			$team_ids[$val['team_id']] = $val['team_id'];
        }
        $this->assign('teams', D('Stockteam')->itemsByIds($team_ids));
		$this->assign('jurys', D('Stockjury')->itemsByIds($jury_ids));
        $this->assign('list', $list);
        $this->assign('page', $show); 
        $this->display();
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Stockgroup');
            if ($obj->add($data)) {
                 $obj->cleanCache();
                $this->tuSuccess('添加成功', U('Stockgroup/index',array('jury_id' => $this->jury_id)));
            }
            $this->tuError('操作失败');
        } else {
			$this->assign('jurys', D('Stockjury')->fetchAll());
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['group_name'] = htmlspecialchars($data['group_name']);
        if (empty($data['group_name'])) {
            $this->tuError('群名称不能为空');
        } 
		$data['jury_id'] = $this->jury_id;
        if (empty($data['jury_id'])) {
            $this->tuError('所在群不能为空');
        }
		$data['team_id'] = D('Stockjury')->create_get_team_id($data['jury_id']);
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('群简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('群简介含有敏感词：' . $words);
        }
        $data['orderby'] = (int)($data['orderby']);
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    public function edit($group_id = 0) {
        if ($group_id = (int) $group_id) {
            $obj = D('Stockgroup');
            if (!$detail = $obj->find($group_id)) {
                $this->tuError('请选择要编辑的群');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['group_id'] = $group_id;
                if (false !== $obj->save($data)) {
                     $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('Stockgroup/index',array('jury_id' => $this->jury_id)));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
				$this->assign('jurys', D('Stockjury')->fetchAll());
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的群');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['group_name'] = htmlspecialchars($data['group_name']);
        if (empty($data['group_name'])) {
            $this->tuError('群名称不能为空');
        } 
		$data['jury_id'] = $this->jury_id;
        if (empty($data['jury_id'])) {
            $this->tuError('所在群不能为空');
        }
		$data['team_id'] = D('Stockjury')->create_get_team_id($data['jury_id']);
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('群简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('群简介含有敏感词：' . $words);
        }
        $data['orderby'] = (int)($data['orderby']);
        return $data;
    }

    public function delete($group_id = 0) {
        if (is_numeric($group_id) && ($group_id = (int) $group_id)) {
            $obj = D('Stockgroup');
            $obj->save(array('group_id' => $group_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('Stockgroup/index',array('jury_id' => $this->jury_id)));
        } else {
            $group_id = $this->_post('group_id', false);
            if (is_array($group_id)) {
                $obj = D('Stockgroup');
                foreach ($group_id as $id) {
                    $obj->save(array('group_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('Stockgroup/index',array('jury_id' => $this->jury_id)));
            }
            $this->tuError('请选择要删除的群');
        }
    }

}
