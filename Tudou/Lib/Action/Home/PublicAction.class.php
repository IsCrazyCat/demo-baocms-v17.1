<?php
class PublicAction extends CommonAction{
    public function email(){
        $email = $this->_get('email');
        if (!isEmail($email)) {
            $this->error('EMAIL地址不正确', U('index/index'));
        }
        $uid = (int) $this->_get('uid');
        $time = (int) $this->_get('time');
        $sig = $this->_get('sig');
        if (empty($uid) || empty($time) || empty($sig)) {
            $this->error('参数不能为空', U('index/index'));
        }
        if (NOW_TIME - $time > 3600) {
            $this->error('验证链接已经超时了', U('index/index'));
        }
        $sign = md5($uid . $email . $time . C('AUTH_KEY'));
        if ($sig != $sign) {
            $this->error('签名失败', U('index/index'));
        }
        $user = D('Users')->find($uid);
        if (empty($user)) {
            $this->error('用户不存在', U('index/index'));
        }
        if (!empty($user['email'])) {
            $this->error('用户已经通过邮件认证的', U('index/index'));
        }
        $data = array('user_id' => $uid, 'email' => $email);
        D('Users')->save($data);
        D('Users')->integral($this->uid, 'email');
        D('Users')->prestige($this->uid, 'email');
        $this->success('恭喜您邮件认证成功', U('index/index'));
    }
	//积分返还
	public function restore() {
           $obj = D('Userintegrallibrary');
           $list = $obj->where(array('integral_library_surplus' => array('gt', 0),'closed' => 0))->order(array('library_id' => 'asc'))->select();
           if ($list) {
                $i = 0;
                foreach($list as $k => $v) {
					if ($v['integral_library_total_success'] >= $v['integral_library_total'] || $v['integral_library_day'] >= $v['integral_library_surplus']) {
						unset($lists[$k]);
					}
					$restore_time = NOW_TIME;//返还时间
                    $day_time = strtotime(TODAY) - 60 * 60 * 24;
                    $restore_date = date('Y-m-d', $day_time);
                    $intro = '每日返积分，返利日期：' . $restore_date.'，当前已返还天数:'.($v['integral_library_total_success']+1);
                    $count = D('Userintegralrestore')->where(array('user_id' => $v['user_id'], 'restore_date' => $restore_date))->count();
                    if (!$count) {
                        if(D('Users')->add_Integral_restore($v['library_id'],$v['user_id'], $v['integral_library_day'], $intro, 0,$restore_date)) $i++;
                    }
                }
                echo "已给{$i}个用户返利";
            }
    }

    public function shopcate($parent_id = 0){
        $datas = D('Shopcate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                    }
                }
            }
        }
        echo $str;
        die;
    }
    public function child($parent_id = 0){
        $datas = D('Activitytype')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['type_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['type_id']) {
                        $str .= '<option value="' . $var2['type_id'] . '">' . $var2['type_name'] . '</option>' . "\n\r";
                    }
                }
            }
        }
        echo $str;
        die;
    }
    public function business($area_id = 0){
        $str = '<option value="0">请选择</option>';
        foreach ($this->bizs as $val) {
            if ($val['area_id'] == $area_id) {
                $str .= '<option value="' . $val['business_id'] . '">' . $val['business_name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
	
	public function apply_link(){
		$obj = D('Links');
        if ($this->isPost()) {
            $yzm = $this->_post('yzm');
            if (strtolower($yzm) != strtolower(session('verify'))) {
                session('verify', null);
                $this->tuError('验证码不正确!', 2000, true);
            }
            $data = $this->createCheck();
            if ($link_id = $obj->add($data)) {
                $this->tuSuccess('恭喜您申请成功，审核后将会邮件通知您', U('index/index'));
            }
            $this->tuError('申请失败');
        } else {
            $this->display();
        }
    }
	
	 private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), array('city_id','link_name', 'link_url','link_email','link_intro','orderby'));
        $data['city_id'] = $this->city_id;
        if (empty($data['city_id'])) {
            $this->tuError('未获取到城市ID，请稍后再试试', 2000, true);
        }
        $data['link_name'] = htmlspecialchars($data['link_name']);
        if (empty($data['link_name'])) {
            $this->tuError('友情链接名称不能为空', 2000, true);
        }
        $data['link_url'] = htmlspecialchars($data['link_url']);
        if (empty($data['link_url'])) {
            $this->tuError('贵站链接不能为空', 2000, true);
        }
        $data['link_email'] = htmlspecialchars($data['link_email']);
        if (empty($data['link_email'])) {
            $this->tuError('请填写您的邮箱', 2000, true);
        }
        $data['link_intro'] = htmlspecialchars($data['link_intro']);
        if (empty($data['link_intro'])) {
            $this->tuError('填写网站简介', 2000, true);
        }
		$data['orderby'] = 100;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
}