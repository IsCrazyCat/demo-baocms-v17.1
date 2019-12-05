<?php
class UserrankAction extends CommonAction{
    private $create_fields = array('rank_name', 'number', 'discount', 'reward', 'icon', 'icon1','integral', 'prestige', 'rebate', 'price','photo');
    private $edit_fields = array('rank_name', 'number', 'discount', 'reward', 'icon', 'icon1','integral', 'prestige', 'rebate','price', 'photo');
    public function index(){
        $obj = D('Userrank');
        import('ORG.Util.Page');
        $map = array();
        $count = $obj->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('rank_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Userrank');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('userrank/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['rank_name'] = htmlspecialchars($data['rank_name']);
        if (empty($data['rank_name'])) {
            $this->tuError('等级名称不能为空');
        }
        $data['number'] = htmlspecialchars($data['number']);
		$data['discount'] = htmlspecialchars($data['discount']);
		$data['reward'] = htmlspecialchars($data['reward']);
        $data['integral'] = (int) $data['integral'];
		$data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        }
		$data['price'] = (int) ($data['price']*100); 
        return $data;
    }
    public function edit($rank_id = 0){
        if ($rank_id = (int) $rank_id) {
            $obj = D('Userrank');
            if (!($detail = $obj->find($rank_id))) {
                $this->tuError('请选择要编辑的会员等级');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['rank_id'] = $rank_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('userrank/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的会员等级');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['rank_name'] = htmlspecialchars($data['rank_name']);
        if (empty($data['rank_name'])) {
            $this->tuError('等级名称不能为空');
        }
        $data['number'] = htmlspecialchars($data['number']);
		$data['discount'] = htmlspecialchars($data['discount']);
		$data['reward'] = htmlspecialchars($data['reward']);
        $data['integral'] = (int) $data['integral'];
		$data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('缩略图格式不正确');
        }
		$data['price'] = (int) ($data['price']*100); 
        return $data;
    }
    public function delete($rank_id = 0){
		$rank_id = (int) $rank_id;
        if ($rank_id) {
            $obj = D('Userrank');
			$detail = D('Users')->where(array('rank_id'=>$rank_id))->find();
			if($detail['user_id']){
				$this->tuError('会员ID【'.$detail['user_id'].'】，昵称【'.$detail['nickname'].'】还在使用该等级，暂时无法删除');
			}
			if($obj->delete($rank_id)){
				$obj->cleanCache();
            	$this->tuSuccess('删除成功', U('userrank/index'));
			}else{
				$this->tuError('操作失败');
			}
        } else {
            $this->tuError('请选择要删除的会员等级');
        }
    }
}