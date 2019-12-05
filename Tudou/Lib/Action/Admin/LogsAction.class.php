<?php
class LogsAction extends CommonAction
{
    public function index(){
        $obj = D('Communityorderlogs');
        import('ORG.Util.Page');
        $map = array();
        if ($community_id = (int) $this->_param('community_id')) {
            $map['community_id'] = $community_id;
            $community = D('Community')->find($community_id);
            $this->assign('name', $community['name']);
            $this->assign('community_id', $community_id);
        }
        if ($number = $this->_param('number', 'htmlspecialchars')) {
            if (!empty($number)) {
                $owner = D('Communityowner')->where(array('number' => $number, 'community_id' => $community_id))->find();
                $map['user_id'] = $owner['user_id'];
                $this->assign('number', $number);
            }
        }
        if ($type = (int) $this->_param('type')) {
            if ($type != 999) {
                $map['type'] = $type;
                $this->assign('type', $type);
            } else {
                $this->assign('type', 999);
            }
        }
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
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
        $count = $obj->where($map)->count();
        $sum = $obj->where($map)->sum('money');
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $community_ids = array();
        foreach ($list as $key => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $community_ids[$val['community_id']] = $val['community_id'];
        }
        $this->assign('sum', $sum);
        $this->assign('communitys', D('Community')->itemsByIds($community_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('types', D('Communityorder')->getType());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function select(){
        $obj = D('Community');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|addr|property'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
}