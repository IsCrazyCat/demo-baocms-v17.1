<?php
class WorkAction extends CommonAction
{
    public function index(){
        $Work = D('Work');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Work->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Work->where($map)->order(array('work_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
    public function audit($work_id = 0)
    {
        if (is_numeric($work_id) && ($work_id = (int) $work_id)) {
            $obj = D('Work');
            $obj->save(array('work_id' => $work_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('work/index'));
        } else {
            $work_id = $this->_post('work_id', false);
            if (is_array($work_id)) {
                $obj = D('Work');
                foreach ($work_id as $id) {
                    $obj->save(array('work_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('work/index'));
            }
            $this->tuError('请选择要审核的招聘信息');
        }
    }
    public function delete($work_id = 0)
    {
        if (is_numeric($work_id) && ($work_id = (int) $work_id)) {
            $obj = D('Work');
            $obj->delete($work_id);
            $this->tuSuccess('删除成功', U('work/index'));
        } else {
            $work_id = $this->_post('work_id', false);
            if (is_array($work_id)) {
                $obj = D('Work');
                foreach ($work_id as $id) {
                    $obj->delete($id);
                }
                $this->tuSuccess('删除成功', U('work/index'));
            }
            $this->tuError('请选择要删除的招聘信息');
        }
    }
}