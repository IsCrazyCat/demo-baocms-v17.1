<?php
class UpsetAction extends CommonAction {
    public function index() {
        $set = D('Uploadset');
        $list = $set->order(array('id' => 'desc'))->select();
        $this->assign('list', $list); 
        $this->display(); 
    }
    public function edit($id = 0) {
        if ($id = (int)$id) {
            $obj = D('Uploadset');
            if (!$detail = $obj->find($id)) {
                $this->tuError('请选择要编辑的方式');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
					$filename = 'ueconfig.json';
					$datajson = $_POST['para'];
					$datajson['status'] = $_POST['status'];
					$d = json_encode($datajson);
					file_put_contents(APP_PATH.'../Public/qiniu_ueditor/php/'.$filename, $d);
                    $this->tuSuccess('操作成功', U('Upset/index'));
                }
                $this->tuError('操作失败');
            } else {
                $detail['para'] = json_decode($detail['para'],true);
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的方式');
        }
    }

    private function editCheck() {
        $data['status'] = (int)($_POST['status']);
        $data['para'] = json_encode($_POST['para']);
        return $data;
    }
}
