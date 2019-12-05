<?php
class SmsAction extends CommonAction{
	
    private $create_fields = array('sms_key', 'sms_explain', 'sms_tmpl');
    private $edit_fields = array('sms_key', 'sms_explain', 'sms_tmpl');
	
	
    public function index(){
        import('ORG.Util.Page');
        $map = array();
        $count = M('Sms')->where($map)->count();
        $Page = new Page($count,50);
        $show = $Page->show();
        $list = M('Sms')->where($map)->order(array('sms_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = M('Sms');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('sms/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
	
	
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['sms_key'] = htmlspecialchars($data['sms_key']);
        if (empty($data['sms_key'])) {
            $this->tuError('标签不能为空');
        }
        $data['sms_explain'] = htmlspecialchars($data['sms_explain']);
        if (empty($data['sms_explain'])) {
            $this->tuError('说明不能为空');
        }
        $data['sms_tmpl'] = htmlspecialchars($data['sms_tmpl']);
        if (empty($data['sms_tmpl'])) {
            $this->tuError('模版不能为空');
        }
        return $data;
    }
	
	
    public function edit($sms_id = 0){
        if ($sms_id = (int) $sms_id) {
            $obj = M('Sms');
            if (!($detail = $obj->find($sms_id))) {
                $this->tuError('请选择要编辑的短信模版');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['sms_id'] = $sms_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('sms/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的短信模版');
        }
    }
	
	
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['sms_key'] = htmlspecialchars($data['sms_key']);
        if (empty($data['sms_key'])) {
            $this->tuError('标签不能为空');
        }
        $data['sms_explain'] = htmlspecialchars($data['sms_explain']);
        if (empty($data['sms_explain'])) {
            $this->tuError('说明不能为空');
        }
        $data['sms_tmpl'] = htmlspecialchars($data['sms_tmpl']);
        if (empty($data['sms_tmpl'])) {
            $this->tuError('模版不能为空');
        }
        return $data;
    }
	
	
    public function delete($sms_id = 0){
        if (is_numeric($sms_id) && ($sms_id = (int) $sms_id)) {
            $obj = M('Sms');
            $obj->save(array('sms_id' => $sms_id, 'is_open' => 0));
            $this->tuSuccess('关闭成功', U('sms/index'));
        } else {
            $sms_id = $this->_post('sms_id', false);
            if (is_array($sms_id)) {
                $obj = M('Sms');
                foreach ($sms_id as $id) {
                    $obj->save(array('sms_id' => $id, 'is_open' => 0));
                }
                $this->tuSuccess('关闭成功', U('sms/index'));
            }
            $this->tuError('请选择要关闭的短信模版');
        }
    }
	
	
    public function audit($sms_id = 0){
        if(is_numeric($sms_id) && ($sms_id = (int) $sms_id)) {
            $obj = M('Sms');
            $obj->save(array('sms_id' => $sms_id, 'is_open' => 1));
            $this->tuSuccess('开启成功', U('sms/index'));
        }else{
            $sms_id = $this->_post('sms_id', false);
            if (is_array($sms_id)) {
                $obj = M('Sms');
                foreach ($sms_id as $id) {
                    $obj->save(array('sms_id' => $id, 'is_open' => 1));
                }
                $this->tuSuccess('开启成功', U('sms/index'));
            }
            $this->tuError('请选择要开启的短信模版');
        }
    }
}