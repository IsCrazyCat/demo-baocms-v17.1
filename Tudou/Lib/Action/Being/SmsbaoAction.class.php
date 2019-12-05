<?php
class SmsbaoAction extends CommonAction{

    public function index(){
        $obj = D('Smsbao');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if($keyword) {
            $map['mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword ', $keyword );
        }
		if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
		 if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        if(isset($_GET['status']) || isset($_POST['status'])) {
            $status = (int) $this->_param('status');
            if($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        }else{
            $this->assign('status', 999);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 50);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('sms_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k=>$val){
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
			$val['paying_body'] = $obj->get_paying_body($val['sms_id']);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('count', $count);
		$this->assign('types', $obj->getType());
        $this->display();
    }

    public function delete($sms_id = 0){
        if (is_numeric($sms_id) && ($asms_id = (int) $sms_id)) {
            $obj = D('Smsbao');
            $obj->delete($sms_id);
            $this->tuSuccess('删除成功', U('smsbao/index'));
        } else {
            $sms_id = $this->_post('sms_id', false);
            if (is_array($sms_id)) {
                $obj = D('Smsbao');
                foreach ($sms_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('smsbao/index'));
            }
            $this->tuError('请选择要删除的短信宝短信记录');
        }
    }
	
	public function delete_drop() {
        D('Smsbao')->where('sms_id','gt',0)->delete();
        $this->tuSuccess('清空短信记录成功', U('smsbao/index'));
    }
   
}