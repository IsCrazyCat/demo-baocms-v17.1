<?php
class GuideAction extends CommonAction{
	
	
	 public function _initialize(){
        parent::_initialize();
    }
	
	
    public function index(){
        if(empty($this->uid)){
            header("Location: " . url('Wap/passport/login'));
            die;
        }
		$this->assign('money', $money = D('Userguidelogs')->where(array('user_id' => $this->uid))->sum('money'));
        $this->display();
	}
	
    public function shop(){
		$status = (int) I('status');
		$this->assign('status', $status);
		$this->assign('nextpage', LinkTo('guide/load',array('status'=>$status,'t' => time(), 'p' => '0000')));
		$this->display();
	}
	
	
	public function load(){
		import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = D('Shopguide')->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();      
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = D('Shopguide')->where($map)->order(array('guide_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $val){
            $list[$k]['shop'] = D('Shop')->find($val['shop_id']);
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('status',$status);
		$this->display();
	}
	
    public function logs(){
		$this->assign('nextpage', LinkTo('guide/loaddata',array('t' => time(),'p' => '0000')));
		$this->display();;
	}
	
	public function loaddata(){
		import('ORG.Util.Page');
        $map = array('user_id'=> $this->uid);
        $count = D('Userguidelogs')->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();      
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = D('Userguidelogs')->where($map)->order(array('user_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$shop_ids = $user_ids  = array();
        foreach ($list as $k => $val) {
			$type = D('Shopmoney')->get_money_type($val['type']);
            $list[$k]['type'] = $type;
            $user_ids[$val['user_id']] = $val['user_id'];
			$shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
	}
	
	
   
}