<?php
class  WorkAction extends CommonAction{
    private $edit_fields = array('title', 'money1', 'money2','num','intro','work_time','expir_date');
    public function index() {
        $Work = D('Work');
        import('ORG.Util.Page'); 
        $map = array('shop_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Work->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show();
        $list = $Work->where($map)->order(array('work_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->editCheck(); 
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $obj = D('Work');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('work/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
    
    public function edit($work_id = 0) {
        if (empty($work_id)) {
            $this->error('请选择需要编辑的内容操作');
        }
        $work_id = (int) $work_id;
        $obj = D('Work');
        $detail = $obj->find($work_id);
        if (empty($detail) || $detail['shop_id'] != $this->shop_id) {
            $this->error('请选择需要编辑的内容操作');
        }
        if ($this->isPost()) {

            $data = $this->editCheck();
            $data['work_id'] = $work_id;
            if (false !== $obj->save($data)) {
                $this->tuSuccess('操作成功', U('work/edit', array('work_id' => $work_id)));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id'] = $this->shop_id;
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id']  = $this->shop['business_id'];
        $data['city_id']  = $this->shop['city_id'];
        $data['lng']  = $this->shop['lng'];
        $data['lat']  = $this->shop['lat'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('标题不能为空');
        }
        
        $data['num'] = (int)$data['num'];
        $data['money1'] = (int)$data['money1'];
        $data['money2'] = (int)$data['money2'];
        $data['work_time'] = htmlspecialchars($data['work_time']);
        if(empty($data['work_time'])){
            $this->tuError('工作时间不能为空');            
        }        
        $data['expir_date'] = htmlspecialchars($data['expir_date']);
        if(!isDate($data['expir_date'])){
            $this->tuError('过期时间不能为空');
        }
        $data['intro'] = SecurityEditorHtml($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('职位描述不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->tuError('职位描述含有敏感词：' . $words);
        }
        return $data;
    }
    
    
   
    
}