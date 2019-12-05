<?php
class BackersAction extends CommonAction{
    public function index(){
        $this->display();
    }
	public function apply(){
        $this->display();
    }
	public function add(){
		if(D('Users')->where(array('user_id'=>$this->uid))->save(array('is_backers'=>1))){
			$this->tuMsg('申请成功!', U('backers/apply'));
		}else{
			$this->tuMsg('操作失败!');
		}
    }
	
	public function reward(){
        $this->display();
    }
	
	public function reward_data(){
        $obj = D('UsersBackersRewardLog');
        import('ORG.Util.Page');
        $map = array('fuid' => $this->uid);
        $count = $obj->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	public function discount(){
        $this->display();
    }
	
	public function discount_data(){
        $obj = D('UsersBackersDiscountLog');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $obj->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}