<?php
class AddressAction extends CommonAction{
	
   public function index(){
        $obj = D('Paddress');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        if($keyword){
            $map['xm|tel|area_str|info'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        } 
		if($user_id = (int) $this->_param('user_id')){
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
   		foreach($list as $k => $v){
            $user = D('Users')->where(array('user_id'=>$v['user_id']))->find();
            $list[$k]['user'] = $user;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
  
	//删除
    public function delete($id){
        $id = (int) $id;
        if(empty($id)){
            $this->tuError('收货地址不存在');
        }
        if(!($detail = D('Paddress')->find($id))){
            $this->tuError('收货地址不存在');
        }
		if(D('Paddress')->save(array('id' => $id, 'closed' => 1))){
			$this->tuSuccess('删除成功', U('address/index'));
		}else{
			$this->tuError('删除失败');
		}
    }
	

	
	//地址库列表
	public function lists(){
		$p = (int) $this->_param('p');
		import('ORG.Util.Page');
		$map = array();
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        if($keyword){
            $map['name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        } 
		if($level = (int) $this->_param('level')){
            if($level!= 999){
                $map['level'] = $level;
            }
            $this->assign('level', $level);
        }else{
            $this->assign('level', 999);
        }
        $count = D('Paddlist')->where($map)->count();
        $Page = new Page($count,10);
        $show = $Page->show();
        $list = D('Paddlist')->where($map)->order(array('id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
   		foreach($list as $k => $v){
             $list[$k]['intro'] = D('Paddlist')->getIntro($v['id']);
			 $list[$k]['price'] = D('Paddlist')->getGoodsOrderPrice($v['id']);
			 $list[$k]['num'] = D('Paddlist')->getGoodsOrderNum($v['id']);
        }
		$this->assign('p', $p);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	//添加地址
	public function address_create($upid = 0,$level = 0){
		$p = (int) $this->_param('p');
		$upid = (int) $this->_param('upid');
		$level = (int) $this->_param('level');
        if($this->isPost()){
            $data = $this->checkFields($this->_param('data', false),array('name','displayorder','p1'));
			$data['name'] = htmlspecialchars($data['name']);
			if(empty($data['name'])){
				$this->tuError('地址名称不能为空');
			}
			if($res = D('Paddlist')->where(array('name'=>$data['name']))->find()){
                $this->tuError('貌似地址重复了');
            }
			$data['upid'] = $upid;
			$data['level'] = $level;
			$data['displayorder'] = (int) $data['displayorder'];
			$p1 = (int) $data['p1'];
            if(D('Paddlist')->add($data)){
                $this->tuSuccess('添加地址成功', U('address/lists',array('p'=>$p1)));
            }
            $this->tuError('操作失败');
        }else{
			$this->assign('p', $p);
			$this->assign('upid', $upid);
			$this->assign('level', $level);
            $this->display();
        }
    }
	
    //编辑地址
    public function address_edit($id = 0){
		$p = (int) $this->_param('p');
        if($id = (int) $id){
            if(!($detail = D('Paddlist')->find($id))){
                $this->error('地址不存在');
            }
            if($this->isPost()){
                $data = $this->checkFields($this->_param('data', false), array('name','displayorder','p1'));
                $data['id'] = $id;
				$data['name'] = htmlspecialchars($data['name']);
				if(empty($data['name'])){
					$this->tuError('地址名称不能为空');
				}
				$data['upid'] = $detail['upid'];
				$data['level'] = $detail['level'];
				$data['displayorder'] = (int) $data['displayorder'];
				$p1 = (int) $data['p1'];
                if(D('Paddlist')->save($data)){
                    $this->tuSuccess('编辑成功', U('address/lists',array('p'=>$p1)));
                }
                $this->tuError('编辑失败');
            }else{
				$this->assign('p', $p);
                $this->assign('detail', $detail);
                $this->display();
            }
        }else{
            $this->error('请选择要编辑的地址');
        }
    }
	
	//删除地址
    public function address_delete($id = 0){
		$p = (int) $this->_param('p');
        if($id = (int) $id){
			if(!($res = D('Paddlist')->find($id))){
                $this->tuError('地址不存在');
            }
			if($result = D('Paddlist')->where(array('upid'=>$res['id']))->find()){
                $this->tuError('您删除的地址下面还有子地址【'.$result['name'].'】');
            }
			if(D('Paddlist')->where(array('id'=>$id))->delete()){
				$this->tuSuccess('删除地址成功', U('address/lists',array('p'=>$p)));
			}else{
				$this->tuError('删除失败');
			}
        }else{
            $this->tuError('请选择要删除的地址');
        }
    }
	
	
	
  
}
