<?php
class LifereportAction extends CommonAction{
    public function index(){
        $obj = D('Lifereport');
        import('ORG.Util.Page');
        $map = array();
		if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['content'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if(isset($_GET['type']) || isset($_POST['type'])) {
            $type = (int) $this->_param('type');
            if($type != 999) {
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        } else {
            $this->assign('type', 999);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
	
	 public function delete($id = 0){
        if (is_numeric($id) && ($id = (int) $id)) {
            $obj = D('Lifereport');
            $obj->delete($id);
            $this->tuSuccess('删除成功', U('lifereport/index'));
        } else {
            $id = $this->_post('id', false);
            if (is_array($id)) {
                $obj = D('Lifereport');
                foreach ($id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('lifereport/index'));
            }
            $this->tuError('请选择要删除的举报信息');
        }
    }
}