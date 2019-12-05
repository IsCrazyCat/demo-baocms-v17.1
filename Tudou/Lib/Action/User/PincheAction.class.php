<?php 
class PincheAction extends CommonAction{

    private $edit_fields = array('cate_id','user_id','photo', 'start_time', 'start_time_more', 'goplace','toplace','middleplace','num_1','num_2','num_3','num_4','mobile','lng','lat','details');
	
	
	
   protected function _initialize() {
        parent::_initialize();
		 if ($this->_CONFIG['operation']['pinche'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $getPincheCate = D('Pinche')->getPincheCate();
        $this->assign('getPincheCate', $getPincheCate);
    }

    public function index(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', LinkTo('pinche/loaddata', array('t' => NOW_TIME,   'keyword' => $keyword, 'p' => '0000')));
	        $this->display(); 
    }
	 public function end(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', LinkTo('pinche/load', array('t' => NOW_TIME,   'keyword' => $keyword, 'p' => '0000')));
	        $this->display(); 
    }
	
	 public function del(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', LinkTo('pinche/del_load', array('t' => NOW_TIME,   'keyword' => $keyword, 'p' => '0000')));
	        $this->display(); 
    }
	
    public function loaddata(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'user_id'=>$this->uid, 'closed' => 0, 'start_time' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['toplace'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $pinche->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $pinche->where($map)->order(array('create_time desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('citys', D('City')->fetchAll());
		$this->assign('areas', D('Area')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	 public function load(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'user_id'=>$this->uid, 'closed' => 0, 'start_time' => array('ELT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['toplace'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $pinche->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $pinche->where($map)->order(array('create_time desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('citys', D('City')->fetchAll());
		$this->assign('areas', D('Area')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	 public function del_load(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('user_id'=>$this->uid, 'closed' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['toplace'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $pinche->where($map)->count(); 
        $Page = new Page($count, 10);
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $pinche->where($map)->order(array('create_time desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('citys', D('City')->fetchAll());
		$this->assign('areas', D('Area')->fetchAll());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

 	public function edit($pinche_id){
        if ($pinche_id = (int) $pinche_id) {
            $obj = D('Pinche');
            if (!($detail = $obj->find($pinche_id))) {
                $this->error('请选择要编辑的拼车');
            }
            if ($detail['status'] != 0) {
                $this->error('该拼车状态不允许被编辑');
            }
            if ($detail['closed'] == 1) {
                $this->error('该拼车已被删除');
            }
            if($this->isPost()){
                $data = $this->editCheck();
                $data['pinche_id'] = $pinche_id;
                if(FALSE !== $obj->save($data)) {
                    $this->tuMsg('操作成功', U('pinche/index'));
                }
                $this->tuMsg('操作失败');
            }else{
                $this->display();
            }
        }else{
            $this->error('请选择要编辑的拼车信息');
        }
    }
	
    public function editCheck(){
       $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
       $data['cate_id'] = (int) $data['cate_id'];
        if(empty($data['cate_id'])){
            $this->tuMsg('类型不能为空');
        }
		$data['city_id'] = (int) $data['city_id'];
		$data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])){
            $this->tuMsg('缩略图格式不正确');
        }
		$data['start_time'] = htmlspecialchars($data['start_time']);
        if(empty($data['start_time'])) {
            $this->tuMsg('出发时间不能为空');
        }
		$data['start_time_more'] = htmlspecialchars($data['start_time_more']);
		
		$data['goplace'] = htmlspecialchars($data['goplace']);
        if(empty($data['goplace'])) {
            $this->tuMsg('出发地不能为空');
        }
        $data['toplace'] = htmlspecialchars($data['toplace']);
        if(empty($data['toplace'])) {
            $this->tuMsg('目的地不能为空');
        }
		
		
		$data['middleplace'] = htmlspecialchars($data['middleplace']);
		$data['num_1'] = htmlspecialchars($data['num_1']);
		$data['num_2'] = htmlspecialchars($data['num_2']);
		$data['num_3'] = htmlspecialchars($data['num_3']);
		$data['num_4'] = htmlspecialchars($data['num_4']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
		$data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        if(empty($data['mobile'])){
            $this->tuMsg('手机不能为空');
        }
        if(!ismobile($data['mobile'])){
            $this->tuMsg('手机格式不正确');
        }
        $data['audit'] = 1;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
    public function delete($pinche_id){
        if (is_numeric($pinche_id) && ($pinche_id = (int) $pinche_id)) {
            $obj = D('Pinche');
            if(!($detail = $obj->find($pinche_id))) {
                $this->tuMsg('拼车不存在');
            }
            if ($detail['closed'] == 1) {
                $this->tuMsg('该拼车状态不允许被删除');
            }
            $obj->save(array('pinche_id' => $pinche_id, 'closed' => 1));
            $this->tuMsg('删除成功', U('pinche/index'));
        }
    }
	
	public function pinche_top($pinche_id = 0){
        if(IS_AJAX){
			$obj = D('Pinche'); 
            $pinche_id = I('pinche_id', 0, 'trim,intval');
            if(!($detail = $obj->find($pinche_id))) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该拼车ID【'.$pinche_id.'】不存在'));
            }
			$type = I('type', 0, 'trim,intval');
			if(!$type){
				$this->ajaxReturn(array('status' => 'error', 'msg' => '必须选择置顶时间'));
			}
			$money = $type * $this->_CONFIG['pinche']['top'] * 100;
			if($this->member['money'] < $money) {
				$this->ajaxReturn(array('status' => 'error', 'msg' => '你余额不足，暂不支持置顶'));
			}
			if(D('Users')->addMoney($this->uid, -$money, '置顶拼车ID【'.$pinche_id.'】' . $type . '小时')) {
				if($obj->save(array('pinche_id'=>$pinche_id,'top_time'=>NOW_TIME + 3600))) {
					$this->ajaxReturn(array('status' => 'success', 'msg' => '置顶成功', U('pinche/index')));
				}else{
					$this->ajaxReturn(array('status' => 'error', 'msg' => '操作失败'));
				}
			}else{
				$this->ajaxReturn(array('status' => 'error', 'msg' => '扣费失败'));
			}
        }
    }
	
}