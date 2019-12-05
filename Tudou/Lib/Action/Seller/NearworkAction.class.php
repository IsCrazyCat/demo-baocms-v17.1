<?php
class NearworkAction extends CommonAction
{
    private $edit_fields = array('title', 'money1', 'money2', 'num', 'intro', 'work_time', 'expir_date');
    public function index()
    {
        $Work = D('Work');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            if (empty($map['title'])) {
                $this->error("未能搜索到!");
            }
            $this->assign('keyword', $keyword);
        }
        $count = $Work->where($map)->count();
        $Page = new Page($count, 30);
        $show = $Page->show();
        $list = $Work->where($map)->order(array('work_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->editCheck();
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Work');
            if ($obj->add($data)) {
                $this->tuMsg('招聘发布成功，请等待审核通过后即可显示!', U('nearwork/index'));
            }
            $this->tuMsg('招聘发布失败', U('nearwork/index'));
        } else {
            $this->display();
        }
    }
    public function edit()
    {
        $work_id = (int) $_GET['work_id'];
        if (empty($work_id)) {
            $this->error('请选择需要编辑的内容操作');
        }
        $obj = D('Work');
        $detail = $obj->find($work_id);
        if (empty($detail) || $detail['shop_id'] != $this->shop_id) {
            $this->error('请不要非法操作');
        }
        if ($this->isPost()) {
            $data = $this->editCheck();
            $data['work_id'] = $work_id;
            if (false !== $obj->save($data)) {
                $this->tuMsg('编辑成功', U('nearwork/index'));
            }
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id'] = $this->shop_id;
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['lng'] = $this->shop['lng'];
        $data['lat'] = $this->shop['lat'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuMsg('标题不能为空');
        }
        $data['num'] = (int) $data['num'];
        $data['money1'] = (int) $data['money1'];
        $data['money2'] = (int) $data['money2'];
        $data['work_time'] = htmlspecialchars($data['work_time']);
        if (empty($data['work_time'])) {
            $this->tuMsg('工作时间不能为空');
        }
        $data['expir_date'] = htmlspecialchars($data['expir_date']);
        if (empty($data['expir_date'])) {
            $this->tuMsg('过期时间不能为空');
        }
        $data['intro'] = SecurityEditorHtml($data['intro']);
        if (empty($data['intro'])) {
            $this->tuMsg('职位描述不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuMsg('职位描述含有敏感词：' . $words);
        }
        return $data;
    }
}