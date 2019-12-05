<?php
class StockjuryAction extends CommonAction {
    private $create_fields = array('jury_name','jury_id','team_id','intro','orderby');
    private $edit_fields = array('jury_name','jury_id','team_id','intro','orderby');
	private $team_id = '';
    public function _initialize(){
        parent::_initialize();
        //
    }
	
    public function index($team_id = 0) {
		$team_id = (int) $lteam_id;
        $obj = D('Stockjury');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword','htmlspecialchars');
        if($keyword){
            $map['jury_name'] = array('LIKE', '%'.$keyword.'%');
        } 
		if ($team_id) {
            $map['team_id'] = $team_id;
        }   
        $this->assign('keyword',$keyword);
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('jury_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$team_ids = array();
        foreach ($list as $key => $val) {
            $team_ids[$val['team_id']] = $val['team_id'];
        }
        $this->assign('teams', D('Stockteam')->itemsByIds($team_ids));
        $this->assign('list', $list);
        $this->assign('page', $show); 
        $this->display();
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Stockjury');
            if ($obj->add($data)) {
                 $obj->cleanCache();
                $this->tuSuccess('添加成功', U('Stockjury/index'));
            }
            $this->tuError('操作失败');
        } else {
			$this->assign('teams', D('Stockteam')->fetchAll());
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['jury_name'] = htmlspecialchars($data['jury_name']);
        if (empty($data['jury_name'])) {
            $this->tuError('团队名称不能为空');
        } 
		$data['team_id'] = (int)$data['team_id'];
        if (empty($data['team_id'])) {
            $this->tuError('所在队伍不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('团队简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('团队简介含有敏感词：' . $words);
        }
        $data['orderby'] = (int)($data['orderby']);
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    public function edit($jury_id = 0) {
        if ($jury_id = (int) $jury_id) {
            $obj = D('Stockjury');
            if (!$detail = $obj->find($jury_id)) {
                $this->tuError('请选择要编辑的团队');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['jury_id'] = $jury_id;
                if (false !== $obj->save($data)) {
                     $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('Stockjury/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
				$this->assign('teams', D('Stockteam')->fetchAll());
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的团队');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['jury_name'] = htmlspecialchars($data['jury_name']);
        if (empty($data['jury_name'])) {
            $this->tuError('团队名称不能为空');
        } 
		$data['team_id'] = (int)$data['team_id'];
        if (empty($data['team_id'])) {
            $this->tuError('所在队伍不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('团队简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('团队简介含有敏感词：' . $words);
        }
        $data['orderby'] = (int)($data['orderby']);
        return $data;
    }

    public function delete($jury_id = 0) {
        if (is_numeric($jury_id) && ($jury_id = (int) $jury_id)) {
            $obj = D('Stockjury');
            $obj->save(array('jury_id' => $jury_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('Stockjury/index'));
        } else {
            $jury_id = $this->_post('jury_id', false);
            if (is_array($jury_id)) {
                $obj = D('Stockjury');
                foreach ($jury_id as $id) {
                    $obj->save(array('jury_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('Stockjury/index'));
            }
            $this->tuError('请选择要删除的团队');
        }
    }

}
