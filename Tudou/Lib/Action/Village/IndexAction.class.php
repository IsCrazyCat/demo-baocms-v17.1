<?php
class IndexAction extends CommonAction{
    public function index(){
        $bg_time = strtotime(TODAY);
        $counts['bbs'] = (int) D('Communityposts')->where(array('village_id' => $this->village_id))->count();
        $counts['bbs_audit'] = (int) D('Communityposts')->where(array('village_id' => $this->village_id, 'audit' => 0, 'closed' => 0))->count();
        $counts['bbs_day'] = (int) D('Communityposts')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'village_id' => $this->village_id, 'closed' => 0))->count();
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('counts', $counts);
        $this->display();
    }
}