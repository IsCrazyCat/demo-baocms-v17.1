<?php
class EnvelopeAction extends CommonAction{
	
	public function _initialize(){
        parent::_initialize();
		$this->assign('types', D('Envelope')->getType());
		$this->assign('orderTypes', D('Envelope')->getOrderType());
    }
	public function index(){
		$this->display();
	}
	
	public function loaddata(){
        $obj = D('EnvelopeLogs');
        import('ORG.Util.Page');
        $map = array('user_id'=>$this->uid);
        if($keyword = $this->_param('keyword', 'htmlspecialchars')){
            $map['title|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if($user_id = (int) $this->_param('user_id')){
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $key => $val){
            $list[$key]['shop'] = M('Shop')->find($val['shop_id']);
			$list[$key]['envelope'] = M('Envelope')->find($val['envelope_id']);
			$list[$key]['user'] = M('Users')->find($val['user_id']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
	
}
