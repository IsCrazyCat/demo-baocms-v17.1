<?php
class AdsiteAction extends CommonAction
{
    private $create_fields = array('site_name', 'theme', 'site_type', 'site_place', 'site_price');
    private $edit_fields = array('site_name', 'theme', 'site_type', 'site_place', 'site_price');
    public function index()
    {
        $Adsite = D('Adsite');
        $this->assign('adsite', $Adsite->fetchAll());
        $this->assign('types', $Adsite->getType());
        $this->assign('place', $Adsite->getPlace());
        $this->display();
        // 输出模板
    }
	
	
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Adsite');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('adsite/index'));
            }
            $this->tuError('操作失败');
        } else {
            $Adsite = D('Adsite');
            $Template = D('Template');
            $this->assign('adsite', $Adsite->fetchAll());
            $this->assign('template', $Template->fetchAll());
            $this->assign('types', $Adsite->getType());
            $this->assign('place', $Adsite->getPlace());
            $this->display();
            // 输出模板
        }
    }
	
	
    public function edit($site_id = 0){
        if ($site_id = (int) $site_id) {
            $obj = D('Adsite');
            if (!($detail = $obj->find($site_id))) {
                $this->tuError('请选择需要编辑的广告位');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['site_id'] = $site_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('adsite/index'));
                }
                $this->tuError('操作失败');
            } else {
                $Adsite = D('Adsite');
                $Template = D('Template');
                $this->assign('adsite', $Adsite->fetchAll());
                $this->assign('template', $Template->fetchAll());
                $this->assign('types', $Adsite->getType());
                $this->assign('place', $Adsite->getPlace());
                $this->assign('detail', $detail);
                $this->display();
                // 输出模板
            }
        } else {
            $this->tuError('请选择要编辑的商家分类');
        }
    }
	
	
	
    public function delete($site_id = 0){
        if($site_id = (int) $site_id){
			
			$count = D('Ad')->where(array('site_id'=>$site_id))->count();
			if($count >= 1){
				$this->tuError('该广告位下面还有广告');	
			}
            D('Adsite')->delete($site_id);
            $this->tuSuccess('删除成功', U('adsite/index'));
        }else{
            $this->tuError('请选择要删除的广告位');
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['site_name'] = htmlspecialchars($data['site_name']);
        if (empty($data['site_name'])) {
            $this->tuError('广告位名称不能为空');
        }
		$data['site_price'] = (int)$data['site_price'];
        return $data;
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['site_name'] = htmlspecialchars($data['site_name']);
        if (empty($data['site_name'])) {
            $this->tuError('广告位名称不能为空');
        }
		$data['site_price'] = (int)$data['site_price'];
        return $data;
    }
}