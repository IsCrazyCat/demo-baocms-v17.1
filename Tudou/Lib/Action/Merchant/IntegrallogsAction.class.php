<?php

class  IntegrallogsAction extends CommonAction{
    
    public function  index(){
        $obj = D('Userintegrallogs');
        import('ORG.Util.Page'); 
        $map = array('user_id' => $this->uid);
        $count = $obj->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show); 
        $this->display(); 
        
    }
    
    
    
}