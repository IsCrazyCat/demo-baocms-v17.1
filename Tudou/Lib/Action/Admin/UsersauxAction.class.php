<?php
class UsersauxAction extends CommonAction{
     private $create_fields = array('user_id','city_id', 'area_id', 'business_id','team_id', 'jury_id', 'group_id', 'card_photo', 'name', 'mobile','card_id','weixin','addr_str', 'addr_info', 'guarantor_name', 'guarantor_mobile');
	private $edit_fields = array('user_id','city_id', 'area_id', 'business_id','team_id', 'jury_id', 'group_id', 'card_photo', 'name', 'mobile','card_id','weixin','addr_str', 'addr_info', 'guarantor_name', 'guarantor_mobile');
    public function index(){
        $Usersaux = D('Usersaux');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|mobile|card_id|guarantor_name|guarantor_mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if ($team_id = (int) $this->_param('team_id')) {
            $map['team_id'] = $team_id;
            $this->assign('team_id', $team_id);
        }
        if ($jury_id = (int) $this->_param('jury_id')) {
            $map['jury_id'] = $jury_id;
            $this->assign('jury_id', $jury_id);
        }
        if (isset($_GET['audit']) || isset($_POST['audit'])) {
            $audit = (int) $this->_param('audit');
            if ($st != 999) {
                $map['audit'] = $audit;
            }
			$map['audit'] = array('IN',array(0,1));
            $this->assign('audit', $audit);
        } else {
			$map['audit'] = array('IN',array(0,1));
            $this->assign('audit', 999);
        }
		if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		
		
        $count = $Usersaux->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Usersaux->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
        $team_ids = $jury_ids = $group_ids = $user_ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $user_ids[$val['user_id']] = $val['user_id'];
            }
			$team_ids[$val['team_id']] = $val['team_id'];
			$jury_ids[$val['jury_id']] = $val['jury_id'];
			$group_ids[$val['group_id']] = $val['group_id'];
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if ($user_ids) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
		$this->assign('teams', D('Stockteam')->itemsByIds($team_ids));
		$this->assign('jurys', D('Stockjury')->itemsByIds($jury_ids));
		$this->assign('groups', D('Stockgroup')->itemsByIds($group_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Usersaux');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('usersaux/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('添加认证会员不能为空');
        }
		$data['guide_id'] = (int) $data['guide_id'];
        $data['card_photo'] = htmlspecialchars($data['card_photo']);
        if (empty($data['card_photo'])) {
            $this->tuError('请上传身份证');
        }
        if (!isImage($data['card_photo'])) {
            $this->tuError('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('真实名字不能为空');
        }
		$data['card_id'] = htmlspecialchars($data['card_id']);
        if (empty($data['card_id'])) {
            $this->tuError('身份证号码不能为空');
        }
		$data['weixin'] = htmlspecialchars($data['weixin']);
        if (empty($data['weixin'])) {
            $this->tuError('微信号码不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuError('手机号不能为空');
        }
		if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->tuError('手机号码格式不正确');
        }
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->tuError('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->tuError('地区不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->tuError('商圈不能为空');
        }
		
		$data['team_id'] = (int) $data['team_id'];
        if (empty($data['team_id'])) {
            $this->tuMsg('队伍不能为空');
        }
        $data['jury_id'] = (int) $data['jury_id'];
        if (empty($data['jury_id'])) {
            $this->tuMsg('团队不能为空');
        }
        $data['group_id'] = (int) $data['group_id'];
        if (empty($data['group_id'])) {
            $this->tuMsg('群不能为空');
        }
		
		
		
		$city = D('City')->find($data['city_id']);
		$area = D('Area')->find($data['area_id']);
		$Busines = D('Business')->find($data['business_id']);
		$data['addr_str'] = $city['name'] . " " . $area['area_name'] . " " . $Busines['business_name'];
        $data['addr_info'] = htmlspecialchars($data['addr_info']);
        if (empty($data['addr_info'])) {
            $this->tuError('详细地址不能为空');
        }
		$data['guarantor_name'] = htmlspecialchars($data['guarantor_name']);
        if (empty($data['guarantor_name'])) {
            $this->tuError('担保人姓名不能为空');
        }
		$data['guarantor_mobile'] = htmlspecialchars($data['guarantor_mobile']);
        if (empty($data['guarantor_mobile'])) {
            $this->tuError('担保人电话不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($user_id = 0){
        if ($user_id = (int) $user_id) {
            $obj = D('Usersaux');
            if (!($detail = $obj->find($user_id))) {
                $this->tuError('请选择要编辑的会员认证');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['user_id'] = $user_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('usersaux/index'));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的会员认证');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('添加认证会员不能为空');
        }
		$data['guide_id'] = (int) $data['guide_id'];
        $data['card_photo'] = htmlspecialchars($data['card_photo']);
        if (empty($data['card_photo'])) {
            $this->tuError('请上传身份证');
        }
        if (!isImage($data['card_photo'])) {
            $this->tuError('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('真实名字不能为空');
        }
		$data['card_id'] = htmlspecialchars($data['card_id']);
        if (empty($data['card_id'])) {
            $this->tuError('身份证号码不能为空');
        }
		$data['weixin'] = htmlspecialchars($data['weixin']);
        if (empty($data['weixin'])) {
            $this->tuError('微信号码不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuError('手机号不能为空');
        }
		if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->tuError('手机号码格式不正确');
        }
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->tuError('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->tuError('地区不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->tuError('商圈不能为空');
        }
		
		$data['team_id'] = (int) $data['team_id'];
        if (empty($data['team_id'])) {
            $this->tuMsg('队伍不能为空');
        }
        $data['jury_id'] = (int) $data['jury_id'];
        if (empty($data['jury_id'])) {
            $this->tuMsg('团队不能为空');
        }
        $data['group_id'] = (int) $data['group_id'];
        if (empty($data['group_id'])) {
            $this->tuMsg('群不能为空');
        }
		
		$city = D('City')->find($data['city_id']);
		$area = D('Area')->find($data['area_id']);
		$Busines = D('Business')->find($data['business_id']);
		$data['addr_str'] = $city['name'] . " " . $area['area_name'] . " " . $Busines['business_name'];
        $data['addr_info'] = htmlspecialchars($data['addr_info']);
        if (empty($data['addr_info'])) {
            $this->tuError('详细地址不能为空');
        }
		$data['guarantor_name'] = htmlspecialchars($data['guarantor_name']);
        if (empty($data['guarantor_name'])) {
            $this->tuError('担保人姓名不能为空');
        }
		$data['guarantor_mobile'] = htmlspecialchars($data['guarantor_mobile']);
        if (empty($data['guarantor_mobile'])) {
            $this->tuError('担保人电话不能为空');
        }
        return $data;
    }
	//删除
    public function delete($user_id = 0){
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            $obj = D('Usersaux');
            $obj->save(array('user_id' => $user_id, 'closed' => 1));
            D('Users')->save(array('user_id' => $user_id, 'is_aux' => 0));
            $this->tuSuccess('删除成功', U('usersaux/index'));
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                $obj = D('Usersaux');
                foreach ($user_id as $id) {
                    $obj->save(array('user_id' => $id, 'closed' => 1));
					D('Users')->save(array('user_id' => $id, 'is_aux' => 0));
                }
                $this->tuSuccess('批量删除成功', U('usersaux/index'));
            }
            $this->tuError('请选择要删除的会员认证');
        }
    }
	//审核
    public function audit($user_id = 0){
        $user_id = (int) $user_id;
		$obj = D('Usersaux');
		if($user_id){
            if($detail = D('Usersaux')->find($user_id)){
				$data = array();
				$data['mobile'] = $detail['mobile'];
				$data['weixin'] = $detail['weixin'];
				$data['id_no'] = $detail['card_id'];
				$data['real_name'] = $detail['name'];
				$data['is_aux'] = 1;
				if(D('Users')->where(array('user_id'=>$user_id))->save($data)){
					if($obj->save(array('user_id' => $user_id, 'audit' => 1))){
						D('Users')->integral($user_id, 'useraux');
						D('Users')->prestige($user_id, 'useraux');
						$this->tuSuccess('审核成功', U('usersaux/index'));
					}else{
						$this->tuError('更新审核信息失败');
					}
				}else{
					$this->tuError('更新会员表失败');
				}
			}else{
				$this->tuError('该认证信息不存在');
			}
        } else{
			$this->tuError('请选择你要审核的认证会员');
		}
    }
	//拒绝审核
	 public function refuse($user_id = 0){
        $obj = D('Usersaux');
         if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            if ($this->isPost()) {
                $reason = htmlspecialchars($this->_param('reason'));
                if(!$reason){
                    $this->tuError('拒绝理由不能为空');
                }
                $obj->save(array('user_id' => $user_id, 'audit' => -1,'reason'=>$reason));
                $this->tuSuccess('操作成功', U('usersaux/index'));
            }else{
                $this->assign('user_id',$user_id);
                $this->display();
            }
         }
    }
}