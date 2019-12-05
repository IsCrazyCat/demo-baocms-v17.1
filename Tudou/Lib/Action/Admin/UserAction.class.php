<?php
class UserAction extends CommonAction{
    private $create_fields = array('account', 'password', 'pay_password','rank_id', 'face', 'mobile', 'email', 'nickname', 'face', 'ext0');
    private $edit_fields = array('account', 'password','pay_password', 'rank_id', 'face', 'mobile', 'email', 'nickname', 'face', 'ext0');
	
	private $binding_edit_fields = array('user_id','uid','open_id','nickname');//绑定
   
    public function index(){
        $User = D('Users');
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
        if($keyword = $this->_param('keyword','htmlspecialchars')){
            $map['user_id|account|nickname|mobile|email|ext0'] = array('LIKE','%'.$keyword.'%');
            $this->assign('keyword',$keyword);
        }
        if ($rank_id = (int) $this->_param('rank_id')) {
            $map['rank_id'] = $rank_id;
            $this->assign('rank_id', $rank_id);
        }
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['reg_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['reg_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['reg_time'] = array('ELT', $end_time);
            }
        }
	
		if(isset($_GET['is_prestige_frozen']) || isset($_POST['is_prestige_frozen'])){
            $is_prestige_frozen = (int) $this->_param('is_prestige_frozen');
            if($is_prestige_frozen != 999) {
                $map['is_prestige_frozen'] = $is_prestige_frozen;
            }
            $this->assign('is_prestige_frozen', $is_prestige_frozen);
        }else{
            $this->assign('is_prestige_frozen', 999);
        }
		
		if(isset($_GET['is_aux']) || isset($_POST['is_aux'])){
            $is_aux = (int) $this->_param('is_aux');
            if($is_aux != 999) {
                $map['is_aux'] = $is_aux;
            }
            $this->assign('is_aux', $is_aux);
        }else{
            $this->assign('is_aux', 999);
        }
		
		if(isset($_GET['is_lock']) || isset($_POST['is_lock'])){
            $is_lock = (int) $this->_param('is_lock');
            if($is_lock != 999) {
                $map['is_lock'] = $is_lock;
            }
            $this->assign('is_lock', $is_lock);
        }else{
            $this->assign('is_lock', 999);
        }
		
		if(isset($_GET['is_backers']) || isset($_POST['is_backers'])){
            $is_backers = (int) $this->_param('is_backers');
            if($is_backers != 999) {
                $map['is_backers'] = $is_backers;
            }
            $this->assign('is_backers', $is_backers);
        }else{
            $this->assign('is_backers', 999);
        }
		
        $count = $User->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $User->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$rank_ids = array();
        foreach ($list as $k => $val) {
			$rank_ids[$val['rank_id']] = $val['rank_id'];
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area'] = $this->ipToArea($val['last_ip']);
            $val['is_shop'] = $User->get_is_shop($val['user_id']);
			$val['is_delivery'] = $User->get_is_delivery($val['user_id']);
			$val['is_weixin'] = D('Connect')->check_connect_bing($val['user_id'],1);
			$val['is_qq'] = D('Connect')->check_connect_bing($val['user_id'],2);
			$val['is_weibo'] = D('Connect')->check_connect_bing($val['user_id'],3);
			$val['stock_num'] = D('Stockorder')->get_user_stock_order_num($val['user_id']);
			$list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('ranks', D('Userrank')->fetchAll());
		$this->assign('rank', D('Userrank')->itemsByIds($rank_ids));
		session('user_index_list', $map);//保存session
        $this->display();
    }
	//会员绑定列表首页
	public function binding(){
        $Connect = D('Connect');
        import('ORG.Util.Page');
        $map = array();
       	if($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['nickname|open_id|uid'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($uid = (int) $this->_param('uid')) {
            $map['uid'] = $uid;
            $this->assign('uid', $uid);
        }
		if (isset($_GET['type']) || isset($_POST['type'])) {
            $type = (int) $this->_param('type');
            if ($type == 1) {
                $map['type'] = 'weixin';
            }elseif($type == 2){
				$map['type'] = 'qq';
			}elseif($type == 3){
				$map['type'] = 'weibo';
			}
            $this->assign('type', $type);
        } else {
            $this->assign('type', 999);
        }
        $count = $Connect->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Connect->where($map)->order(array('connect_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$uids = array();
        foreach ($list as $k => $val) {
            if ($val['uid']) {
                $uids[$val['uid']] = $val['uid'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($uids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	 //回收站的会员彻底删除
    public function binding_delete($connect_id = 0){
        $connect_id = (int) $connect_id;
		if(false !== D('Connect')->delete($connect_id)){
			$this->tuSuccess('删除会员绑定成功', U('user/binding'));
		}else{
			$this->tuError('操作失败');
		}
    }
	//会员回收站
	public function recycle(){
        $User = D('Users');
        import('ORG.Util.Page');
        $map = array('closed' =>1);
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        if ($nickname = $this->_param('nickname', 'htmlspecialchars')) {
            $map['nickname'] = array('LIKE', '%' . $nickname . '%');
            $this->assign('nickname', $nickname);
        }
        if ($mobile = $this->_param('mobile', 'htmlspecialchars')) {
            $map['mobile'] = array('LIKE', '%' . $mobile . '%');
            $this->assign('mobile', $mobile);
        }
        $count = $User->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $User->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$rank_ids = array();
        foreach ($list as $k => $val) {
			$rank_ids[$val['rank_id']] = $val['rank_id'];
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area'] = $this->ipToArea($val['last_ip']);
			$list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('rank', D('Userrank')->itemsByIds($rank_ids));
        $this->display();
    }
	
	
	 //删除会员重写
    public function delete($user_id = 0){
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            $obj = D('Users');
            $obj->save(array('user_id' => $user_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('user/index'));
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                $obj = D('Users');
                foreach ($user_id as $id) {
				$obj->save(array('user_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('user/index'));
            }
            $this->tuError('请选择要删除的会员');
        }
    }
	
	
    public function audit($user_id = 0){
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            $obj = D('Users');
            $obj->save(array('user_id' => $user_id, 'closed' => 0));
            $this->tuSuccess('审核成功', U('user/index'));
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                $obj = D('Users');
                foreach ($user_id as $id) {
                    $obj->save(array('user_id' => $id, 'closed' => 0));
                }
                $this->tuSuccess('审核成功', U('user/index'));
            }
            $this->tuError('请选择要审核的会员');
        }
    }
	
	//删除会员重写
    public function renew($user_id = 0){
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            $obj = D('Users');
            $obj->save(array('user_id' => $user_id, 'closed' => 0));
            $this->tuSuccess('恢复成功', U('user/recycle'));
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                $obj = D('Users');
                foreach ($user_id as $id) {
				$obj->save(array('user_id' => $id, 'closed' => 0));
                }
                $this->tuSuccess('批量恢复成功', U('user/recycle'));
            }
            $this->tuError('请选择要删除的会员');
        }
    }
	
	
	//回收站的会员彻底删除
    public function recycle_delete($user_id = 0){
        $user_id = (int) $user_id;
		
		if(!($detail = D('Users')->find($user_id))){
            $this->tuError('删除的会员不存在');
        }
		
		$this->tuError('为了数据安全，此操作暂未开放，如要彻底删除会员去数据库删除');
		
		/*
		$connect = D('Connect')->where(array('uid'=>$user_id))->select();
		foreach ($connect as $k => $v){
			D('Connect')->delete($v['connect_id']);
        }
		if(false !== D('Users')->delete($user_id)){
			$this->tuSuccess('彻底删除成功', U('user/recycle'));
		}else{
			$this->tuError('操作失败');
		}
		*/
    }
    public function select(){
        $User = D('Users');
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['account|nickname|mobile|user_id|email|ext0'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $User->where($map)->count();
        $Page = new Page($count, 8);
        $pager = $Page->show();
        $list = $User->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        $this->display();
    }
    public function selectapp(){
        $User = D('Users');
        import('ORG.Util.Page');
        $map = array('closed' => array('IN', '0,-1'));
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        if ($nickname = $this->_param('nickname', 'htmlspecialchars')) {
            $map['nickname'] = array('LIKE', '%' . $nickname . '%');
            $this->assign('nickname', $nickname);
        }
        if ($ext0 = $this->_param('ext0', 'htmlspecialchars')) {
            $map['ext0'] = array('LIKE', '%' . $ext0 . '%');
            $this->assign('ext0', $ext0);
        }
        $join = ' inner join ' . C('DB_PREFIX') . 'app_user a on a.user_id = ' . C('DB_PREFIX') . 'users.user_id';
        $count = $User->where($map)->join($join)->count();
        $Page = new Page($count, 8);
        $pager = $Page->show();
        $list = $User->where($map)->join($join)->order(array(C('DB_PREFIX') . 'users.user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        $this->display();
    }
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Users');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('user/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('ranks', D('Userrank')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['account'] = htmlspecialchars($data['account']);
        if (empty($data['account'])) {
            $this->tuError('账户不能为空');
        }
        if (D('Users')->getUserByAccount($data['account'])) {
            $this->tuError('该账户已经存在');
        }
        $data['password'] = htmlspecialchars($data['password']);
        if (empty($data['password'])) {
            $this->tuError('密码不能为空');
        }
        $data['password'] = md5($data['password']);
		$data['pay_password'] = htmlspecialchars($data['pay_password']);
        if (empty($data['pay_password'])) {
            $this->tuError('支付密码不能为空');
        }
        $data['pay_password'] = md5(md5($data['pay_password']));
        $data['nickname'] = htmlspecialchars($data['nickname']);
        if (empty($data['nickname'])) {
            $this->tuError('昵称不能为空');
        }
        $data['rank_id'] = (int) $data['rank_id'];
        $data['email'] = htmlspecialchars($data['email']);
        $data['face'] = htmlspecialchars($data['face']);
        $data['ext0'] = htmlspecialchars($data['ext0']);
        $data['reg_ip'] = get_client_ip();
        $data['reg_time'] = NOW_TIME;
        return $data;
    }
    public function edit($user_id = 0){
        if ($user_id = (int) $user_id) {
            $obj = D('Users');
            if (!($detail = $obj->find($user_id))) {
                $this->tuError('请选择要编辑的会员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['user_id'] = $user_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('user/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('ranks', D('Userrank')->fetchAll());
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的会员');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['account'] = htmlspecialchars($data['account']);
        if (empty($data['account'])) {
            $this->tuError('账户不能为空');
        }
        if ($data['password'] == '******') {
            unset($data['password']);
        } else {
            $data['password'] = htmlspecialchars($data['password']);
            if (empty($data['password'])) {
                $this->tuError('密码不能为空');
            }
            $data['password'] = md5($data['password']);
        }
		
		if ($data['pay_password'] == '******') {
            unset($data['pay_password']);
        } else {
            $data['pay_password'] = htmlspecialchars($data['pay_password']);
            if (empty($data['pay_password'])) {
                $this->tuError('支付密码不能为空');
            }
            $data['pay_password'] = md5(md5($data['pay_password']));
        }
		
        $data['nickname'] = htmlspecialchars($data['nickname']);
		if (empty($data['nickname'])) {
            $this->tuError('昵称不能为空');
        }
        $data['face'] = htmlspecialchars($data['face']);
        $data['email'] = htmlspecialchars($data['email']);
        $data['ext0'] = htmlspecialchars($data['ext0']);
        $data['rank_id'] = (int) $data['rank_id'];
        
        return $data;
    }
   
	
    public function integral(){
        $user_id = (int) $this->_get('user_id');
        if (empty($user_id)) {
            $this->tuError('请选择用户');
        }
        if (!($detail = D('Users')->find($user_id))) {
            $this->tuError('没有该用户');
        }
        if ($this->isPost()) {
            $integral = (int) $this->_post('integral');
            if ($integral == 0) {
                $this->tuError('请输入正确的积分数');
            }
            $intro = $this->_post('intro', 'htmlspecialchars');
			if (empty($intro)) {
                $this->tuError('积分说明不能为空');
            }
            if ($detail['integral'] + $integral < 0) {
                $this->tuError('积分余额不足');
            }
            D('Users')->save(array('user_id' => $user_id, 'integral' => $detail['integral'] + $integral));
            D('Userintegrallogs')->add(array(
				'user_id' => $user_id, 
				'integral' => $integral, 
				'intro' => $intro, 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip()
			));
			D('Weixinmsg')->weixinTmplCapital($type = 2,$user_id,$integral,$intro);//积分模板通知
            $this->tuSuccess('操作成功', U('userintegrallogs/index'));
        } else {
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }
	
	public function gold(){
        $user_id = (int) $this->_get('user_id');
        if (empty($user_id)) {
            $this->tuError('请选择用户');
        }
        if (!($detail = D('Users')->find($user_id))) {
            $this->tuError('没有该用户');
        }
        if ($this->isPost()) {
            $gold = (int) ($this->_post('gold') * 100);
            if ($gold == 0) {
                $this->tuError('请输入正确的商户资金数');
            }
            $intro = $this->_post('intro', 'htmlspecialchars');
			if (empty($intro)) {
                $this->tuError('变动商户资金说明不能为空');
            }
            if ($detail['gold'] + $gold < 0) {
                $this->tuError('商户资金余额不足');
            }
			D('Users')->save(array('user_id' => $user_id, 'gold' => $detail['gold'] + $gold));
            M('UserGoldLogs')->add(array(
				'user_id' => $user_id, 
				'gold' => $gold, 
				'intro' => '管理员后台操作说明：'.$intro, 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip()
			));

            $this->tuSuccess('操作成功', U('user/index'));
        }else{
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }
	
	
    //设置商户冻结金 
 	public function frozen_gold(){
       $user_id = (int)$this->_get('user_id'); 
       if(!$detail = D('Users')->find($user_id)){
           $this->tuError('没有该用户');
       }
       if($this->isPost()){
		   $gold = (int) ($this->_post('gold') * 100);
           if($gold == 0){
               $this->tuError('请输入正确商户冻结金');
           }
           $intro =  $this->_post('intro',  'htmlspecialchars');
		   if(empty($intro)){
               $this->tuError('商户冻结金说明不能为空');
           }
		   if (!D('Users')->set_frozen_gold($user_id,$gold,$intro)) {//入账
			  $this->tuError(D('Users')->getError(), 3000, true);	  
		   }
           $this->tuSuccess('操作成功',U('user/index'));
       }else{
           $this->assign('user_id',$user_id);
           $this->display();
       }       
   }
   
   //设置会员冻结金 
 	public function frozen_money(){
       $user_id = (int)$this->_get('user_id'); 
       if(!$detail = D('Users')->find($user_id)){
           $this->tuError('没有该用户');
       }
       if($this->isPost()){
		   $money = (int)  ($this->_post('money') * 100);
           if($money == 0){
               $this->tuError('请输入正确的会员冻结金');
           }
           $intro =  $this->_post('intro', 'htmlspecialchars');
		   if(empty($intro)){
               $this->tuError('会员冻结金说明不能为空');
           }
		   if (!D('Users')->set_frozen_money($user_id,$money,$intro)) {//入账
			  $this->tuError(D('Users')->getError(), 3000, true);	  
		   }
           $this->tuSuccess('操作成功',U('user/index'));
       }else{
           $this->assign('user_id',$user_id);
           $this->display();
       }       
   }
   
   
    public function manage(){
        $user_id = (int) $this->_get('user_id');
        if (empty($user_id)) {
            $this->error('请选择用户');
        }
        if (!($detail = D('Users')->find($user_id))) {
            $this->error('没有该用户');
        }
        setUid($user_id);
        header("Location:" . U('members/index/index'));
        die;
    }
    public function money(){
        $user_id = (int) $this->_get('user_id');
        if (empty($user_id)) {
            $this->tuError('请选择用户');
        }
        if (!($detail = D('Users')->find($user_id))) {
            $this->tuError('没有该用户');
        }
        if ($this->isPost()) {
            $money = (int) ($this->_post('money') * 100);
            if ($money == 0) {
                $this->tuError('请输入正确的余额数');
            }
            $intro = $this->_post('intro', 'htmlspecialchars');
			if (empty($intro)) {
                $this->tuError('添加余额必须输入说明');
            }
            if ($detail['money'] + $money < 0) {
                $this->tuError('余额不足');
            }
            D('Users')->save(array('user_id' => $user_id, 'money' => $detail['money'] + $money));
            D('Usermoneylogs')->add(array(
				'user_id' => $user_id, 
				'money' => $money, 
				'intro' => $intro, 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip()
			));
			D('Weixinmsg')->weixinTmplCapital($type = 1,$user_id,$money,$intro);//余额模板通知
            $this->tuSuccess('操作成功', U('usermoneylogs/index'));
        } else {
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }
	//会员绑定编辑
	public function binding_edit($connect_id = 0){
        if ($connect_id = (int) $connect_id) {
            $obj = D('Connect');
            if (!($detail = $obj->find($connect_id))) {
                $this->tuError('请选择要编辑的绑定会员');
            }
            if ($this->isPost()) {
                $data = $this->binding_editCheck();
                $data['connect_id'] = $connect_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('user/binding'));
                }
                $this->tuError('操作失败');
            } else {
				$this->assign('user', D('Users')->find($detail['uid']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的会员');
        }
    }
    private function binding_editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->binding_edit_fields);
        $data['uid'] = (int) $data['user_id'];
        if (empty($data['uid'])) {
            $this->tuError('请选择会员');
        }
		$data['open_id'] = htmlspecialchars($data['open_id']);
		if (empty($data['open_id'])) {
            $this->tuError('open_id不能为空');
        }
        $data['nickname'] = htmlspecialchars($data['nickname']);
		if (empty($data['nickname'])) {
            $this->tuError('昵称不能为空');
        }
        return $data;
    }
	
	public function tree(){
		$this->display();
	}
	
	//json输出系谱图
	public function family(){
		import('Class.Category',APP_PATH);
		$data = array();
		$obj = D('Users');
		$us1= $obj->where(array('user_id'=>36))->field('user_id,nickname,mobile,prestige,integral,rank_id,fuid1')->find();
		$us = $obj->where(array('fuid1'=>array('gt','0')))->field('user_id,nickname,mobile,prestige,integral,rank_id,fuid1')->select();
		$arr=Category::getChilds1($us,36);
		$us1['children']=$arr;
		$data[]=$us1;
		$this->ajaxReturn($data,'JSON');
	}
	
	//更新会员分销信息
	public function profit($user_id = 0){
        if ($user_id = (int) $user_id) {
            $obj = D('Users');
            if (!($detail = $obj->find($user_id))) {
                $this->tuError('请选择要编辑的会员');
            }
			$this->assign('profit', $profit = $obj->find($detail['fuid1']));
			
            if($this->isPost()){
				$CONFIG = D('Setting')->fetchAll();
				$data['user_id'] = $user_id;
				$data['fuid1'] = (int) $_POST['fuid1'];//我成为他的下级
				$safety = $_POST['safety'];
				if(!$safety){
					$this->tuError('安全码必须填写');
				}
				$safety2 = md5($CONFIG['site']['host'].'-'.$CONFIG['site']['qq']);
				if($safety != $safety2){
					$this->tuError('安全码错误');
				}
				
				//我的下1级成为我领导的下二级
				$fuid1 = $obj->where(array('fuid1'=>$user_id))->select();
				foreach($fuid1 as $k =>$val){
					$obj->where(array('user_id'=>$val['user_id']))->save(array('fuid2'=>$data['fuid1']));
				}
				//我的下2级成为领导的下三级
				$fuid2 = $obj->where(array('fuid2'=>$user_id))->select();
				foreach($fuid2 as $k2 =>$val2){
					$obj->where(array('user_id'=>$val2['user_id']))->save(array('fuid3'=>$data['fuid1']));
				}
				//我的下3级
				/*
				$fuid3 = $obj->where(array('fuid3'=>$user_id))->select();
				foreach($fuid3 as $k3 =>$val3){
					$obj->where(array('user_id'=>$val3['user_id']))->save(array('fuid3'=>$data['fuid1']));
				}
				*/
				
				
                if(false !== $obj->save($data)){
                    $this->tuSuccess('更新操作成功', U('user/index'));
                }
                $this->tuError('操作失败');
            }else{
                $this->assign('detail', $detail);
                $this->assign('ranks', D('Userrank')->fetchAll());
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的会员');
        }
    }
	
	//会员订单列表导出
    public function export_code(){
		$admin_id = (int) $_POST['admin_id'];
        if(empty($admin_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '非法错误'));
        }
		$value = $this->_param('value', 'htmlspecialchars');
        if(empty($value)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '请填写导出密码'));
        }
		if($value != 123456) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '导出密码错误'));
        }else{
			session('export_code', md5($admin_id.'--'.$value));
			$this->ajaxReturn(array('status' => 'success', 'msg' => '输入密码成功，正在为你跳转', 'url' => U('user/export',array('admin_id'=>$admin_id,'value'=>$value))));
		}
		
    }
	
		//会员订单列表导出
    public function export($admin_id = 0,$value = 0){
		$admin_id = (int) $admin_id;
		$value = $this->_param('value', 'htmlspecialchars');
		$export_code = session('export_code');
		if(!$export_code || $export_code != md5($admin_id.'--'.$value)){
			exit;
		}
        $list = D('Users')->where($_SESSION['user_index_list'])->order(array('user_id' => 'asc'))->select();
        $date = date("Y_m_d", time());
        $filetitle = "会员列表";
        $fileName = $filetitle . "_" . $date;
        $html = "﻿";
        $filter = array(
			'aa' => '会员ID', 
			'bb' => '账户', 
			'cc' => '昵称', 
			'dd' => '积分', 
			'ee' => '声望', 
			'ff' => '余额', 
			'gg' => '商户资金', 
			'hh' => '会员昵称', 
			'ii' => '会员等级', 
			'jj' => '会员等级', 
			'kk' => '冻结金-商户资金', 
			'll' => '邮箱', 
			'mm' => '手机号', 
			'nn' => '一级推荐人', 
			'oo' => '二级推荐人', 
			'pp' => '三级推荐人',  
			'ss' => '实名状态', 
			'tt' => '推手状态', 
			'uu' => '锁定状态', 
			'vv' => '注册时间', 
			'ww' => '注册IP', 
			'xx' => '最后登录时间' 
		);
        foreach ($filter as $key => $title) {
            $html .= $title . "\t,";
        }
        $html .= "\n";
        foreach ($list as $k => $v) {
			$fuid1 = D('Users')->find($v['fuid1']);
			$fuid2 = D('Users')->find($v['fuid2']);
			$fuid3 = D('Users')->find($v['fuid3']);
            if($v['is_aux'] == 1) {
                $aux = '已实名';
            }else {
                $aux = '未实名';
            }
			if($v['is_lock'] == 1) {
                $lock = '已锁定';
            }else {
                $lock = '未锁定';
            }
			if($v['is_backers'] == 1) {
                $backers = '申请中';
            }elseif($v['is_backers'] == 2) {
                $backers = '已审核';
            }else {
                $backers = '已拒绝';
            }
            
            $filter = array(
				'aa' => '会员ID', 
				'bb' => '账户', 
				'cc' => '昵称', 
				'dd' => '积分', 
				'ee' => '声望', 
				'ff' => '余额', 
				'gg' => '商户资金', 
				'hh' => '会员昵称', 
				'ii' => '会员等级', 
				'jj' => '冻结金-会员余额', 
				'kk' => '冻结金-商户资金', 
				'll' => '邮箱', 
				'mm' => '手机号', 
				'nn' => '一级推荐人', 
				'oo' => '二级推荐人', 
				'pp' => '三级推荐人', 
				'ss' => '实名状态', 
				'tt' => '推手状态', 
				'uu' => '锁定状态', 
				'vv' => '注册时间', 
				'ww' => '注册IP', 
				'xx' => '最后登录时间' 
			);
            $list[$k]['aa'] = $v['user_id'];
            $list[$k]['bb'] = $v['account'];
            $list[$k]['cc'] = $v['nickname'];
            $list[$k]['dd'] = $v['integral'];
            $list[$k]['ee'] = $v['prestige'];
            $list[$k]['ff'] = $v['money']/ 100;
            $list[$k]['gg'] = $v['gold']/ 100;
            $list[$k]['hh'] = $v['nickname'];
            $list[$k]['ii'] = $this->ranks[$v['rank_id']]['rank_name'];
            $list[$k]['jj'] = $v['frozen_money']/ 100;
            $list[$k]['kk'] = $v['frozen_gold']/ 100;
            $list[$k]['ll'] = $v['email'];
            $list[$k]['mm'] = $v['mobile'];
            $list[$k]['nn'] = $fuid1['nickname'].'【'.$v['fuid1'].'】';
            $list[$k]['oo'] = $fuid2['nickname'].'【'.$v['fuid2'].'】';
            $list[$k]['pp'] = $fuid3['nickname'].'【'.$v['fuid3'].'】';
            $list[$k]['ss'] = $aux;
            $list[$k]['tt'] = $backers;
            $list[$k]['uu'] = $lock;
            $list[$k]['vv'] = date('H:i:s', $v['reg_time']);
            $list[$k]['ww'] = $v['reg_ip'];
            $list[$k]['xx'] = date('H:i:s', $v['last_time']);
            foreach ($filter as $key => $title) {
                $html .= $list[$k][$key] . "\t,";
            }
            $html .= "\n";
        }
        /* 输出CSV文件 */
        ob_end_clean();
        header("Content-type:text/csv");
        header("Content-Disposition:attachment; filename={$fileName}.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
		session('export_code', null);// session
        echo $html;
        exit;
    }
    
}