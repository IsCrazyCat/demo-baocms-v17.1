<?php
class RunningAction extends CommonAction{
   public function index(){
        $this->assign('nextpage', LinkTo('running/loaddata', array('t' => NOW_TIME,'p' => '0000')));
        $this->display();
    }
	
    public function loaddata(){
        $obj = D('Running');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        $count = $obj->where($map)->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $obj->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

}