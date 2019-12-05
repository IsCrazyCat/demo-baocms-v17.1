<?php
class NeighborAction extends CommonAction{
    public function index(){
        $this->assign('nextpage', LinkTo('neighbor/loadneighbor', array('t' => NOW_TIME, 'village_id' => $this->village_id, 'p' => '0000')));
        $this->display();
    }
    public function loadneighbor(){
        $obj = D('Villagejoin');
        import('ORG.Util.Page');
        $map = array('village_id' => $this->village_id);
        $count = $obj->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list =$obj->where($map)->order('join_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function delete($join_id = 0){
        if (is_numeric($join_id) && ($join_id = (int) $join_id)) {
            $obj = D('Villagejoin');
            if (!($detail = $obj->find($join_id))) {
                $this->error('该通知不存在');
            }
            if ($detail['village_id'] != $this->village_id) {
                $this->error('请不要删除他人好友');
            }
            $obj->where(array('join_id' => $join_id))->delete();
            $this->success('删除成功', U('neighbor/index'));
        }
    }
}