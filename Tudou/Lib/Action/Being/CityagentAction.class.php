<?php
class CityagentAction extends CommonAction {

    private $create_fields = array('agent_name','shop_id','price', 'intro','orderby'); 
    private $edit_fields = array('agent_name', 'shop_id','price','intro','orderby');

    public function index() {
        $obj = D('Cityagent');
		import('ORG.Util.Page');
		$map = array('closed' =>0);
		$count = $obj->where($map)->count();
        $Page = new Page($count,500);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('orderby' => 'asc','agent_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
        foreach ($list as $k => $val) {
			$user_ids[$val['user_id']] = $val['user_id'];
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
			$val['city_num'] = $obj->GetCityAgentCity($val['agent_id']);
            $list[$k] = $val;
        }
		$this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    public function create($parent_id=0) {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Cityagent');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('cityagent/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('parent_id',$parent_id);
            $this->display();
        }
    }
    
    
    public function child($parent_id=0){
        $datas = D('Cityagent')->fetchAll();
        $str = '';
        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['agent_id'] == $parent_id){
                foreach($datas as $var2){
                    if($var2['parent_id'] == $var['agent_id']){
                        $str.='<option value="'.$var2['agent_id'].'">'.$var2['agent_name'].'</option>'."\n\r";
           
                        foreach($datas as $var3){
                            if($var3['parent_id'] == $var2['agent_id']){
                               $str.='<option value="'.$var3['agent_id'].'">&nbsp;&nbsp;--'.$var3['agent_name'].'</option>'."\n\r"; 
                            }
                        }
                    }  
                }
                             
              
            }           
        }
        echo $str;die;
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['agent_name'] = htmlspecialchars($data['agent_name']);
        if (empty($data['agent_name'])) {
            $this->tuError('代理名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('简介不能为空');
        }
		$data['price'] = htmlspecialchars($data['price']);
        if (empty($data['price'])) {
            $this->tuError('购买价格不能为空');
        }
		if($data['price'] <= 0) {
            $this->tuError('非法的购买价格');
        }
        $data['orderby'] = (int) $data['orderby'];
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    public function edit($agent_id = 0) {
        if ($agent_id = (int) $agent_id) {
            $obj = D('Cityagent');
            if (!$detail = $obj->find($agent_id)) {
                $this->tuError('请选择要编辑的代理分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['agent_id'] = $agent_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('cityagent/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的代理分类');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['agent_name'] = htmlspecialchars($data['agent_name']);
        if (empty($data['agent_name'])) {
            $this->tuError('代理名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('简介不能为空');
        }
		$data['price'] = htmlspecialchars($data['price']);
        if (empty($data['price'])) {
            $this->tuError('购买价格不能为空');
        }
		if($data['price'] <= 0) {
            $this->tuError('非法的购买价格');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function delete($agent_id = 0) {
        if (is_numeric($agent_id) && ($agent_id = (int) $agent_id)) {
            $obj = D('Cityagent');
            $obj->delete($agent_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('cityagent/index'));
        } else {
            $agent_id = $this->_post('agent_id', false);
            if (is_array($agent_id)) {
                $obj = D('Cityagent');
                foreach ($agent_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('cityagent/index'));
            }
            $this->tuError('请选择要删除的代理分类');
        }
    }
    
    public function update() {
        $orderby = $this->_post('orderby', false);
        $obj = D('Cityagent');
        foreach ($orderby as $key => $val) {
            $data = array(
                'agent_id' => (int) $key,
                'orderby' => (int) $val
            );
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->tuSuccess('更新成功', U('cityagent/index'));
    }

}
