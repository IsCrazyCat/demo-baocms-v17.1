<?php
class StockteamAction extends CommonAction {
    private $create_fields = array('user_id', 'team_name','photo', 'intro', 'orderby');
    private $edit_fields = array('user_id', 'team_name','photo', 'intro', 'orderby');
    public function index() {
        $obj = D('Stockteam');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword','htmlspecialchars');
        if($keyword){
            $map['team_name'] = array('LIKE', '%'.$keyword.'%');
        }    
        $this->assign('keyword',$keyword);
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('team_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $user_ids[$val['user_id']] = $val['user_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if ($user_ids) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show); 
        $this->display();
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Stockteam');
            if ($obj->add($data)) {
                 $obj->cleanCache();
                $this->tuSuccess('添加成功', U('stockteam/index'));
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
            $this->tuError('管理员不能为空');
        }
        $data['team_name'] = htmlspecialchars($data['team_name']);
        if (empty($data['team_name'])) {
            $this->tuError('队伍名称不能为空');
        } 
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->tuError('队伍LOGO不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('活动简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('活动简介含有敏感词：' . $words);
        }
        $data['orderby'] = (int)($data['orderby']);
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    public function edit($team_id = 0) {
        if ($team_id = (int) $team_id) {
            $obj = D('Stockteam');
            if (!$detail = $obj->find($team_id)) {
                $this->tuError('请选择要编辑的队伍');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['team_id'] = $team_id;
                if (false !== $obj->save($data)) {
                     $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('stockteam/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
				$this->assign('user', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的队伍');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('管理员不能为空');
        }
        $data['team_name'] = htmlspecialchars($data['team_name']);
        if (empty($data['team_name'])) {
            $this->tuError('队伍名称不能为空');
        } 
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->tuError('队伍LOGO不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('活动简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('活动简介含有敏感词：' . $words);
        }
        $data['orderby'] = (int)($data['orderby']);
        return $data;
    }

    public function delete($team_id = 0) {
        if (is_numeric($team_id) && ($team_id = (int) $team_id)) {
            $obj = D('Stockteam');
            $obj->save(array('team_id' => $team_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('stockteam/index'));
        } else {
            $team_id = $this->_post('team_id', false);
            if (is_array($team_id)) {
                $obj = D('Stockteam');
                foreach ($team_id as $id) {
                    $obj->save(array('team_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('stockteam/index'));
            }
            $this->tuError('请选择要删除的队伍');
        }
    }

}
