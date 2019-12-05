<?php

class DayuAction extends CommonAction
{
	public function index(){
		$dayu = D('Dayu');
		import('ORG.Util.Page'); 
		$map = array();
		$count = $dayu->where($map)->count(); 
        $Page = new Page($count,50); 
        $show = $Page->show(); 
		$all_tag = $dayu->order(array('dayu_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this -> assign('tag', $all_tag);
        $this->assign('page', $show); 
		$this -> display();
	}

	public function create(){
		if($this->isPost()){
			$data = $this -> _post('data', false);
			$data['dayu_name'] = htmlspecialchars($data['dayu_name']);
			$data['dayu_tag'] = htmlspecialchars($data['dayu_tag']);
			$data['dayu_note'] = htmlspecialchars($data['dayu_note']);

			$info = D('Dayu')->where(array("dayu_id='{$dayu_id}'"))->find();
			if($info['dayu_tag'] != $data['dayu_tag']){
				$check_info = D('Dayu')->where(array("dayu_tag='{$data['dayu_tag']}'"))->select();
				if(count($check_info) > 0){
					$this->tuError('模板ID已存在');
				}
			}
			if($data['dayu_name'] == NULL){
				$this->tuError('模板名不能为空');
			}
			if($data['dayu_tag'] == NULL){
				$this->tuError('模板ID不能为空');
			}
			if($data['dayu_note'] == NULL){
				$this->tuError('模板说明不能为空');
			}
			if(D('Dayu')->add($data)){
				$this->tuSuccess('操作成功', U('dayu/index'));
			}
			$this->tuError('操作失败');
		}else{
			$this -> display();
		}
	}

	public function edit($dayu_id){
		if($this->isPost()){
			$data = $this -> _post('data', false);
			$data['dayu_name'] = htmlspecialchars($data['dayu_name']);
			$data['dayu_tag'] = htmlspecialchars($data['dayu_tag']);
			$data['dayu_note'] = htmlspecialchars($data['dayu_note']);
			
			$info = D('Dayu')->where(array("dayu_id='{$dayu_id}'"))->find();
			if($info['dayu_tag'] != $data['dayu_tag']){
				$check_info = D('Dayu')->where(array("dayu_tag='{$data['dayu_tag']}'"))->select();
				if(count($check_info) > 0){
					$this->tuError('模板ID已存在');
				}
			}
			if($data['dayu_name'] == NULL){
				$this->tuError('模板名不能为空');
			}
			if($data['dayu_tag'] == NULL){
				$this->tuError('模板ID不能为空');
			}
			if($data['dayu_note'] == NULL){
				$this->tuError('模板说明不能为空');
			}
			if(D('Dayu')->where(array("dayu_id='{$dayu_id}'"))->save($data)){
				$this->tuSuccess('操作成功', U('dayu/index'));
			}
			$this->tuError('操作失败');
		}else{
			$info = D('Dayu')->where(array("dayu_id='{$dayu_id}'"))->find();
			$this -> assign('info', $info);
			$this -> display();
		}
	}

	public function delete($dayu_id = 0){
		if (is_numeric($dayu_id) && ($dayu_id = (int) $dayu_id)) {
			if(D('Dayu')->save(array('dayu_id' => $dayu_id, 'is_open' => 0))){
				$this->tuSuccess('操作成功', U('dayu/index'));
			}
			$this->tuError('操作失败');
		}else{
			$dayu_ids = $this->_post('dayu_id', false);
            if (is_array($dayu_ids)) {
                foreach ($dayu_ids as $id) {
					D('Dayu')->save(array('dayu_id' => $id, 'is_open' => 0));
                }
                $this->tuSuccess('操作成功', U('dayu/index'));
            }
            $this->tuError('操作失败');
		}
	}
	
	 public function audit($dayu_id = 0) {
        if (is_numeric($dayu_id) && ($dayu_id = (int) $dayu_id)) {
            $obj = D('Dayu');
            $obj->save(array('dayu_id' => $dayu_id, 'is_open' => 1));
            $obj->cleanCache();
            $this->tuSuccess('开启成功', U('Dayu/index'));
        } else {
            $dayu_id = $this->_post('dayu_id', false);
            if (is_array($dayu_id)) {
                $obj = D('Dayu');
                foreach ($dayu_id as $id) {
                    $obj->save(array('dayu_id' => $id, 'is_open' => 1));
                }
                $obj->cleanCache();
                $this->tuSuccess('开启成功', U('Dayu/index'));
            }
            $this->tuError('请选择要开启的短信模版');
        }
    }
}