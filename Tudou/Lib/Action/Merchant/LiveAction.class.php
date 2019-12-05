<?php 
class LiveAction extends CommonAction{
	protected function _initialize() {
        parent::_initialize();
    }
	public function index(){
		$obj = D('Live');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id);
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('live_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->display();
	}

	public function add(){
		$live_id = I('get.live_id','','intval');
		if(IS_POST){
			$data = I('post.');
			$data['create_time'] = time();
			if($data['live_id']){
				M('live')->where('live_id='.$data['live_id'])->save($data);
				$this->tuSuccess('更新成功！',U('live/index'));
			}else{
				M('live')->add($data);
				$this->tuSuccess('添加成功！',U('live/index'));
			}
		}
		$btn = '创建直播';
		if($live_id){
			$detail = M('live')->where('live_id='.$live_id)->find();
			$this->assign('detail',$detail);
			$btn = '更新直播';
		}
		$shop = M('shop')->where('shop_id='.$this->shop_id)->find();
		$this->assign('btn',$btn);
		$this->assign('shop_id',$this->shop_id);
		$this->assign('cate_id',$shop['cate_id']);
		$this->display();
	}

	public function del(){
		$live_id = I('get.live_id');
		if(M('live')->where('live_id='.$live_id)->delete()){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
}