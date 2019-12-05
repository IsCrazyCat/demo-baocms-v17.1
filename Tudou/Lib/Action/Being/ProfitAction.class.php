<?php
class ProfitAction extends CommonAction{
    public function order(){
		$obj = D('Userprofitlogs');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($log_id = (int) $this->_param('log_id')) {
            $map['log_id'] = $log_id;
            $this->assign('log_id', $log_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		
		 if (($bg_date = $this->_param('bg_date', 'htmlspecialchars') ) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);

        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }

            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }


        if (isset($_GET['type']) || isset($_POST['type'])) {
            $type = $this->_param('type');
            if ($type != 999) {
                $map['type'] = $type;
            }
            $this->assign('type', $type);
        } else {
            $this->assign('type', 999);
        }
		
		if (isset($_GET['is_separate']) || isset($_POST['is_separate'])) {
            $is_separate = (int) $this->_param('is_separate');
            if ($is_separate != 999) {
                $map['is_separate'] = $is_separate;
            }
            $this->assign('is_separate', $is_separate);
        } else {
            $this->assign('is_separate', 999);
        }
		
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$type_name= $obj->get_money_type($val['type']);
            $list[$k]['type_name'] = $type_name;
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', $obj->getType());
		$this->assign('separates', $obj->getSeparate());
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 

    }
	//分销商图标统计
 	public function sellertatistics() {
		$obj = D('Users');
		$this->assign('count_mobile',$count_mobile = $obj->where(array('mobile'=>array('neq','')))->count());
		$this->assign('count_mail',$count_mail = $obj->where(array('email'=>array('neq','')))->count());
		$this->assign('count_weixin',$count_weixin = D('Connect')->where(array('type'=>'weixin'))->count());
		$this->assign('count_weibo',$count_weibo = D('Connect')->where(array('type'=>'weibo'))->count());
		$this->assign('count_qq',$count_qq = D('Connect')->where(array('type'=>'qq'))->count());
		$this->display(); 
		
	}

    public function user() {
        $User = D('Users');
        import('ORG.Util.Page'); 
        $map = array('u.closed'=>array('IN','0,-1'));
        if($account = $this->_param('account','htmlspecialchars')){
            $map['u.account'] = array('LIKE','%'.$account.'%');
            $this->assign('account',$account);

        }
        if($nickname = $this->_param('nickname','htmlspecialchars')){
            $map['u.nickname'] = array('LIKE','%'.$nickname.'%');
            $this->assign('nickname',$nickname);
        }
        if($rank_id = (int)$this->_param('rank_id')){
            $map['u.rank_id'] = $rank_id;
            $this->assign('rank_id',$rank_id);
        }
        if($ext0 = $this->_param('ext0','htmlspecialchars')){
            $map['u.ext0'] = array('LIKE','%'.$ext0.'%');
            $this->assign('ext0',$ext0);
        }
        $profit_min_rank_id = (int)$this->_CONFIG['profit']['profit_min_rank_id'];
        if ($profit_min_rank_id) {
            $rank = D('Userrank')->find($profit_min_rank_id);
            if ($rank) {
                $map['u.prestige'] = array('EGT', $rank['prestige']);
            }
        }
        $join = ' LEFT JOIN ' . C('DB_PREFIX') . 'users f ON f.user_id = u.fuid1';
        $count = $User->alias('u')->join($join)->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $User->alias('u')->field('u.*, f.user_id AS fuserid, f.account AS fusername')->join($join)->where($map)->order(array('u.user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $uids = $level1 = $level2 = $level3 = array();
        foreach($list as $k=>$val){
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area']   = $this->ipToArea($val['last_ip']);
            $uids[$val['user_id']] = $val['user_id'];
            $list[$k] = $val;
        }
        $tmpLevel1 = $User->field(array('COUNT(*)' => 'cnt', 'fuid1'))->group('fuid1')->select();
        foreach($tmpLevel1 as $k => $v) {
            $level1[$v['fuid1']] = $v['cnt'];
        }
        $tmpLevel2 = $User->field(array('COUNT(*)' => 'cnt', 'fuid2'))->group('fuid2')->select();
        foreach($tmpLevel2 as $k => $v) {
            $level2[$v['fuid2']] = $v['cnt'];
        }
        $tmpLevel3 = $User->field(array('COUNT(*)' => 'cnt', 'fuid3'))->group('fuid3')->select();
        foreach($tmpLevel3 as $k => $v) {
            $level3[$v['fuid3']] = $v['cnt'];
        }
        $this->assign('list', $list); 
        $this->assign('level1', $level1);
        $this->assign('level2', $level2);
        $this->assign('level3', $level3);
        $this->assign('page', $show); 
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); 
    }


   public function cancel($log_id = 0){
        if ($log_id = (int) $log_id) {
            $obj = D('Userprofitlogs');
			if($detail = $obj->find($log_id)){
				if($detail['is_separate'] == 1){
					if($obj->save(array('log_id' => $log_id, 'is_separate' => 2))){
						D('Users')->addMoney($detail['user_id'],-$detail['money'],'取消分成');
						$this->tuSuccess('取消分成成功', U('profit/order'));
					}
				}else{
					$this->tuError('状态不正确');
				}
				
			}else{
				$this->tuError('没找到');
			}
            
        } else {
            $this->tuError('ID不正确');
        }
    }
	
	

}

