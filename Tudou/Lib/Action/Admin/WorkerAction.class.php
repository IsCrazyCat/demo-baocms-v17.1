<?php
class WorkerAction extends CommonAction{
    public function index(){
        $obj = D('Shopworker');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|tel|mobile|qq|weixin|work|addr'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
  
		if (isset($_GET['status']) || isset($_POST['status'])) {
            $status = (int) $this->_param('status');
            if ($status != 999) {
                $map['status'] = $status;
            }
            $this->assign('status', $status);
        } else {
            $this->assign('status', 999);
        }
		
		
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('worker_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function audit($worker_id = 0){
        if (is_numeric($worker_id) && ($worker_id = (int) $worker_id)) {
            $obj = D('Shopworker');
            $obj->save(array('worker_id' => $worker_id, 'status' => 1));
            $this->tuSuccess('审核成功', U('worker/index'));
        } else {
            $worker_id = $this->_post('worker_id', false);
            if (is_array($worker_id)) {
                $obj = D('Shopworker');
                foreach ($worker_id as $id) {
                    $obj->save(array('worker_id' => $id, 'status' => 1));
                }
                $this->tuSuccess('审核成功', U('worker/index'));
            }
            $this->tuError('请选择要审核的店员信息');
        }
    }
    public function delete($worker_id = 0){
        if (is_numeric($worker_id) && ($worker_id = (int) $worker_id)) {
            $obj = D('Shopworker');
            $obj->delete($worker_id);
            $this->tuSuccess('删除成功', U('worker/index'));
        } else {
            $worker_id = $this->_post('worker_id', false);
            if (is_array($worker_id)) {
                $obj = D('Shopworker');
                foreach ($worker_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('worker/index'));
            }
            $this->tuError('请选择要删除的店员信息');
        }
    }
}