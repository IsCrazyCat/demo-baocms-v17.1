<?php
class DistributionAction extends CommonAction{
	
	public function _initialize(){
        parent::_initialize();
        if($this->_CONFIG['profit']['profit'] == 0){
            $this->error('暂无此功能');
			die;
        }
		if(false == D('Userprofitlogs')->determinePower($this->uid)){
			$this->error('您的等级暂时不支持分成');
			die;
		}
    }

    public function index(){
        if (empty($this->uid)) {
            header("Location: " . U('Wap/passport/login'));
            die;
        }
		$this->assign('profit_ok', $profit_ok = D('Userprofitlogs')->where(array('user_id' => $this->uid,'is_separate' =>1))->sum('money'));
		$this->assign('profit_cancel',$profit_cancel = D('Userprofitlogs')->where(array('user_id' => $this->uid,'is_separate' =>2))->sum('money'));
        $this->display();
    }
    public function profit(){
		$status = (int) $this->_param('status');
		$this->assign('status', $status);
		$this->assign('nextpage', LinkTo('distribution/profitloaddata',array('status'=>$status,'t' => NOW_TIME, 'p' => '0000')));
        $this->mobile_title = '优惠买单';
		$this->display(); // 输出模板		
    }
	public function profitloaddata(){
        $status = (int) $this->_param('status');
        if (!in_array($status, array(0, 1, 2, 3))) {
            $status = 1;
        }
        $model = D('Userprofitlogs');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'is_separate' => $status);
        $count = $model->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
        $orderby = array('log_id' => 'DESC');
        $list = $model->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$type_name= $model->get_money_type($val['type']);
            $list[$k]['type_name'] = $type_name;
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('status', $status);
		$this->display();
		
	}
	
    public function subordinate(){
		$level = (int) $this->_param('level');
		$this->assign('level', $level);
		$this->assign('nextpage', LinkTo('distribution/subordinateloaddata',array('level'=>$level,'t' => NOW_TIME, 'p' => '0000')));
        $this->mobile_title = '优惠买单';
		$this->display(); // 输出模板		
    }
	
	public function subordinateloaddata(){
		$level = (int) $this->_param('level');
        if (!in_array($level, array(1, 2, 3))) {
            $level = 1;
        }
        $user = D('Users');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'fuid' . $level => $this->uid);
        $count = $user->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
        $orderby = array('user_id' => 'DESC');
        $list = $user->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('level', $level);
        $this->display();
		 
	}
    
    public function poster()
    {
        $token = 'fuid_' . $this->uid;
        $url = U('Wap/passport/register', array('fuid' => $this->uid));
        $file = tuQrCode($token, $url);
        $this->assign('file', $file);
        $this->display();
    }
    public function superior()
    {
        $user = D('Users');
        if ($this->member['fuid1']) {
            $fuser = $user->find($this->member['fuid1']);
        }
        $this->assign('fuser', $fuser);
        $this->display();
    }
}