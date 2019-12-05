<?php

class ShopkuaidiAction extends CommonAction{

	private $create_fields = array('type','shop_id','name', 'tel');
	private $edit_fields = array('type','shop_id','name', 'tel');
	private $listcreate_fields = array('type','shop_id','name', 'tel','shouzhong','xuzhong','province_id');
	private $listedit_fields = array('type','shop_id','name', 'tel','shouzhong','xuzhong','province_id');
	
	public function _initialize(){
        parent::_initialize();
		$this->assign('provinceList', $provinceList = D('Paddlist') -> where(array('level' => array('IN',array(1,2)))) -> select());
    }			

	public function index(){
		$Pkuaidi = D('Pkuaidi');
		import('ORG.Util.Page');
		$map = array('type'=> goods,'closed'=> 0,'shop_id' => $this->shop_id);
		if ($keyword = $this -> _param('keyword', 'htmlspecialchars')) {
			$map['name|tel'] = array('LIKE', '%' . $keyword . '%');
			$this -> assign('keyword', $keyword);
		}
		$count = $Pkuaidi-> where($map)->count(); 
		$Page = new Page($count, 20); 
		$show = $Page->show(); 
		$list = $Pkuaidi -> order(array('id' => 'desc')) -> where($map)->limit($Page->firstRow . ',' . $Page->listRows) -> select();
		$shop_ids = array();
			foreach($list as $key => $val){
				$shop_ids[$val['shop_id']] = $val['shop_id'];
			}
			$this->assign('shops', D('Shop')->itemsByIds($shop_ids));
		$this -> assign('list', $list);
		$this -> assign('page', $show);
		$this -> display();
	}

	public function create(){
		if($this -> isPost()){
			$data = $this -> createCheck();
			$obj = D('Pkuaidi');
			$data['id'] = $id;
			if($obj->add($data)){
				$this->tuSuccess('添加成功', U('shopkuaidi/index'));
			}
			$this->tuError('操作失败');
		}else{
			$this->assign('id', $id);
			$this->display();
		}
	}

	private function createCheck(){
		$data = $this -> checkFields($this -> _post('data', false), $this -> create_fields);
		$data['type'] = goods;
		$data['shop_id'] = $this->shop_id;
		$data['name'] = htmlspecialchars($data['name']);
		if(empty($data['name'])){
			$this->tuError('快递不能为空');
		}
		$data['tel'] = (int)$data['tel'];
		$data['audit'] = 1;
		return $data;
	}


	public function edit($kuaidi_id = 0){
		if($kuaidi_id = (int)$kuaidi_id){
			$obj = D('Pkuaidi');
			if(!$detail = $obj -> find($kuaidi_id)){
				$this->tuError('请选择要编辑的快递');
			}
			if($detail['shop_id'] != $this->shop_id){
				$this->tuError('请不要非法操作');
			}
			if($this->isPost()){
				$data = $this -> editCheck();
				$data['id'] = $kuaidi_id;
				if(false !== $obj->save($data)){
					$this->tuSuccess('操作成功', U('shopkuaidi/index'));
				}
				$this->tuError('操作失败');
			}else{
				$this -> assign('detail', $detail);
				$this -> display();
			}
		}else{
			$this->tuError('请选择要编辑的快递');
		}
	}

	private function editCheck(){
		$data = $this -> checkFields($this -> _post('data', false), $this -> edit_fields);
		$data['type'] = goods;
		$data['shop_id'] = $this->shop_id;
		$data['name'] = htmlspecialchars($data['name']);
		if (empty($data['name'])){
			$this->tuError('快递名不能为空');
		}
		$data['tel'] = (int)$data['tel'];
		$data['audit'] = 1;
		return $data;
	}

	public function delete($kuaidi_id = 0) {
		    $kuaidi_id = (int)$kuaidi_id;
			$obj = D('Pkuaidi');
			if (!$detail = $obj -> find($kuaidi_id)) {
				$this->tuError('请选择要删除的快递');
			}
			if ($detail['shop_id'] != $this->shop_id) {
				$this->tuError('请不要非法操作');
			}
			$obj -> save(array('id'=>$kuaidi_id,'closed'=>1));
			$this-> uSuccess('删除成功', U('shopkuaidi/index'));
	
	}

	public function lists($kuaidi_id = 0) {
		if ($kuaidi_id = (int)$kuaidi_id) {
			$lists = D('Pyunfei');
			import('ORG.Util.Page');
			$map = array('type'=> goods,'shop_id' => $this->shop_id);
			if ($keyword = $this -> _param('keyword', 'htmlspecialchars')) {
				$map['name'] = array('LIKE', '%' . $keyword . '%');
				$this -> assign('keyword', $keyword);
			}
			$map['kuaidi_id'] = $kuaidi_id;
			$count = $lists ->where($map)->count(); 
			$Page = new Page($count, 20); 
			$show = $Page->show(); 
			$list = $lists -> order(array('yunfei_id' => 'desc')) -> where($map)->limit($Page->firstRow . ',' . $Page->listRows) -> select();
			$shop_ids = array();
			foreach ($list as $key => $val) {
				$shop_ids[$val['shop_id']] = $val['shop_id'];
			}
			$this->assign('shops', D('Shop')->itemsByIds($shop_ids));
			$this -> assign('list', $list);
			$this -> assign('page', $show);
			$this -> assign('count', $count);
			$this -> assign('kuaidi_id', $kuaidi_id);
			$this -> display();
		} else {
			$this->tuError('请选择快递');
		}
	}
	
	public function listcreate($kuaidi_id = 0){
		if ($this -> isPost()) {
			$data = $this -> listcreateCheck();
			$obj = D('Pyunfei');
			$kuaidi_id = (int)$kuaidi_id;
			$data['kuaidi_id'] = $kuaidi_id;
			
			
			$province_ids = $this->_post('id');
    
			if($yunfei_id = $obj->add($data)){
				foreach ($province_ids as $val) {
				  if(!empty($val)) {
					 $datas['yunfei_id'] = $yunfei_id;
					 $datas['kuaidi_id'] = $kuaidi_id;
					 $datas['province_id'] = $val;
					 D('Pyunfeiprovinces')->add($datas);
				   }
				}
				$this->tuSuccess('添加成功', U('shopkuaidi/lists',array('kuaidi_id' =>$kuaidi_id)));
			}
			$this->tuError('操作失败');
		} else {
			$this->assign('ids', D('Pyunfeiprovinces')->getIds($kuaidi_id));
			$this-> assign('kuaidi_id', $kuaidi_id);
			$this-> display();
		}
	}

	private function listcreateCheck() {
		$data = $this -> checkFields($this -> _post('data', false), $this -> listcreate_fields);
		$data['type'] = goods;
		$data['shop_id'] = $this->shop_id;
		$data['name'] = htmlspecialchars($data['name']);
		if (empty($data['name'])) {
			$this->tuError('名称不能为空');
		}
		$data['shouzhong'] = (int) ($data['shouzhong'] * 100);
        if (empty($data['shouzhong'])) {
            $this->tuError('首重价格不能为空');
        }  
        $data['xuzhong'] = (int) ($data['xuzhong'] * 100);
        if (empty($data['xuzhong'])) {
            $this->tuError('续重价格不能为空');
        }
		if ($data['xuzhong'] >= $data['shouzhong'] ) {
            $this->tuError('续重价格不能大于首重');
        }
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		return $data;
	}
	
	
	public function listedit($yunfei_id = 0) {
		if ($yunfei_id = (int)$yunfei_id) {
			$obj = D('Pyunfei');
			if (!$detail = $obj -> find($yunfei_id)) {
				$this->tuError('没找到运费地区详细内容');
			}
			if ($detail['shop_id'] != $this->shop_id) {
				$this->tuError('请不要非法操作');
			}
			if ($this -> isPost()) {
				$data = $this -> listeditCheck();
				$data['yunfei_id'] = $yunfei_id;
				$province_ids = $this->_post('id');
				if($obj->save($data)){
					D('Pyunfeiprovinces')->delete(array('where' => "yunfei_id = '{$yunfei_id}'"));//先删除再更新
					foreach ($province_ids as $val) {
					  if(!empty($val)) {
						 $datas['yunfei_id'] = $yunfei_id;
						 $datas['kuaidi_id'] = $detail['kuaidi_id'];
						 $datas['province_id'] = $val;
						 D('Pyunfeiprovinces')->add($datas);
					   }
					}
					$this->tuSuccess('修改成功', U('shopkuaidi/lists',array('kuaidi_id' =>$detail['kuaidi_id'])));
				}
				$this->tuError('操作失败');
			} else {
				$this->assign('ids', D('Pyunfeiprovinces')->getIds2($yunfei_id));
				$this->assign('ids3', D('Pyunfeiprovinces')->getIds3($detail['kuaidi_id'],$yunfei_id));
				$this -> assign('detail', $detail);
				$this -> display();
			}
		} else {
			$this->tuError('请选择要编辑的运费设置');
		}
	}
	//编辑区域
	private function listeditCheck() {
		$data = $this -> checkFields($this -> _post('data', false), $this -> listedit_fields);
		$data['type'] = goods;
		$data['shop_id'] = $this->shop_id;
		$data['name'] = htmlspecialchars($data['name']);
		if (empty($data['name'])) {
			$this->tuError('名称不能为空');
		}
		$data['shouzhong'] = (int) ($data['shouzhong'] * 100);
        if (empty($data['shouzhong'])) {
            $this->tuError('首重价格不能为空');
        }  
        $data['xuzhong'] = (int) ($data['xuzhong'] * 100);
        if (empty($data['xuzhong'])) {
            $this->tuError('续重价格不能为空');
        }
		if ($data['xuzhong'] >= $data['shouzhong'] ) {
            $this->tuError('续重价格不能大于首重');
        }
		$data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		return $data;
	}
	
	public function listdelete($yunfei_id = 0) {
			$yunfei_id = (int)$yunfei_id;
			$obj = D('Pyunfei');
			if(!$detail = $obj->find($yunfei_id)) {
				$this->tuError('没找到运费地区详细内容');
			}
			if($detail['shop_id'] != $this->shop_id){
				$this->tuError('请不要非法操作');
			}
			$obj->delete($yunfei_id);
			D('Pyunfeiprovinces')->delete(array('where' => "yunfei_id = '{$yunfei_id}'"));//删除这些
			$this-> uSuccess('删除【'.$detail['name'].'】成功', U('shopkuaidi/lists',array('kuaidi_id' =>$detail['kuaidi_id'])));
		
	}
	
	
}
