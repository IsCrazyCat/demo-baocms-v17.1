<?php
class CommunityAction extends CommonAction{
    private $create_fields = array('name', 'addr', 'tel', 'pic', 'user_id', 'city_id', 'area_id', 'village_id', 'property', 'lng', 'lat', 'orderby');
    private $edit_fields = array('name', 'addr', 'tel', 'pic', 'user_id', 'city_id', 'area_id', 'village_id', 'property', 'lng', 'lat', 'orderby');
    private $tieba_create_fields = array('title', 'user_id', 'cate_id', 'details', 'orderby', 'is_fine', 'create_time', 'create_ip');
    private $tieba_edit_fields = array('title', 'user_id', 'cate_id', 'details', 'orderby', 'is_fine');
    public function index(){
        $obj = D('Community');
        import('ORG.Util.Page');
        $map = array();
        $users = $this->_param('data', false);
        if ($users['user_id']) {
            $map['user_id'] = $users['user_id'];
        }
		if ($community_id = (int) $this->_param('community_id')) {
            $map['community_id'] = $community_id;
            $community = D('Community')->find($community_id);
            $this->assign('name', $community['name']);
            $this->assign('community_id', $community_id);
        }
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|addr'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $obj->_format($val);
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        $Village = D('Village');
        $village_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $Village->_format($val);
            $village_ids[$val['village_id']] = $val['village_id'];
        }
        if (!empty($village_ids)) {
            $this->assign('village', D('Village')->itemsByIds($village_ids));
        }
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Community');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->tuSuccess('添加成功', U('community/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('areas', D('Area')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('小区名称不能为空');
        }
        $data['property'] = htmlspecialchars($data['property']);
        if (empty($data['property'])) {
            $this->tuError('物业公司不能为空');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('小区地址不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->tuError('所在区域不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('物业管理员不能为空');
        }
		if (!D('Community')->check_user_id_occupy($data['user_id'])) {
            $this->tuError('当前物业管理员已被占用');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($community_id = 0){
        if ($community_id = (int) $community_id) {
            $obj = D('Community');
            if (!($detail = $obj->find($community_id))) {
                $this->tuError('请选择要编辑的小区管理');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['community_id'] = $community_id;
				if (!D('Community')->check_user_id_neq_community($data['user_id'],$data['community_id'])) {
					$this->tuError('当前管理员已被其他小区占用');
				}
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->tuSuccess('操作成功', U('community/index'));
                }
                $this->tuError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('users', D('Users')->find($detail['user_id']));
                $this->assign('villages', D('village')->find($detail['village_id']));
                $this->assign('areas', D('Area')->fetchAll());
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的商圈管理');
        }
    }
    public function hots($business_id){
        if ($business_id = (int) $business_id) {
            $obj = D('Business');
            if (!($detail = $obj->find($business_id))) {
                $this->tuError('请选择商圈');
            }
            $detail['is_hot'] = $detail['is_hot'] == 0 ? 1 : 0;
            $obj->save(array('business_id' => $business_id, 'is_hot' => $detail['is_hot']));
            $obj->cleanCache();
            $this->tuSuccess('操作成功', U('business/index'));
        } else {
            $this->tuError('请选择商圈');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuError('小区名称不能为空');
        }
        $data['property'] = htmlspecialchars($data['property']);
        if (empty($data['property'])) {
            $this->tuError('物业公司不能为空');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('小区地址不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        $data['area_id'] = (int) $data['area_id'];
        $data['village_id'] = (int) $data['village_id'];
        if (empty($data['area_id'])) {
            $this->tuError('所在区域不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->tuError('物业管理员不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        return $data;
    }
    public function delete($community_id = 0){
        if (is_numeric($community_id) && ($community_id = (int) $community_id)) {
            $obj = D('Community');
            $obj->delete($community_id);
            $obj->cleanCache();
            $this->tuSuccess('删除成功', U('community/index'));
        } else {
            $community_id = $this->_post('community_id', false);
            if (is_array($community_id)) {
                $obj = D('Community');
                foreach ($community_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->tuSuccess('删除成功', U('community/index'));
            }
            $this->tuError('请选择要删除的小区管理');
        }
    }
    public function child($area_id = 0){
        $datas = D('Community')->select();
        $str = '<option value="0">请选择</option>';
        foreach ($datas as $val) {
            if ($val['area_id'] == $area_id) {
                $str .= '<option value="' . $val['community_id'] . '">' . $val['name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
    public function select(){
        $Community = D('Community');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($name = $this->_param('name', 'htmlspecialchars')) {
            $map['name|addr|property|tel'] = array('LIKE', '%' . $name . '%');
            $this->assign('name', $name);
        }
        $count = $Community->where($map)->count();
        $Page = new Page($count, 8);
        $pager = $Page->show();
        $list = $Community->where($map)->order(array('community_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $pager);
        $this->display();
    }
	
	 public function owner(){
        $obj = D('Communityowner');
        import('ORG.Util.Page');
        $map = array();
		
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|location'] = array('LIKE', '%' . $keyword . '%');
        }
		
     	if($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
		if($community_id = (int) $this->_param('community_id')) {
            $map['community_id'] = $community_id;
            $community = D('Community')->find($community_id);
            $this->assign('name', $community['name']);
            $this->assign('community_id', $community_id);
        }
		if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        }else{
            if($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
		if(isset($_GET['audit']) || isset($_POST['audit'])) {
            $audit = (int) $this->_param('audit');
            if ($audit != 999) {
                $map['audit'] = $audit;
            }
            $this->assign('audit', $audit);
        } else {
            $this->assign('audit', 999);
        }
       
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k => $v){
			if($community = D('Community')->where(array('community_id'=>$v['community_id']))->find()){
				$list[$k]['community'] = $community;
			}
			if($user = D('Users')->where(array('user_id'=>$v['user_id']))->find()){
				$list[$k]['user'] = $user;
			}
		}
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
   //删除业主
   public function owner_delete($owner_id = 0 ,$community_id = 0){
        if($owner_id = (int) $owner_id){
            $obj = D('Communityowner');
			if(!$detail = $obj->find($owner_id)){
				$this->tuError('业主不存在');
			}
			if($obj->delete($owner_id)){
				$obj->cleanCache();
            	$this->tuSuccess('删除成功', U('community/owner',array('community_id'=>$community_id)));
			}else{
				$this->tuError('删除失败');
			}
        }else{
            $this->tuError('请选择要删除的业主');
        }
    }
	//审核业主
   public function owner_audit($owner_id = 0 ,$community_id = 0){
        if($owner_id = (int) $owner_id){
            $obj = D('Communityowner');
			if(!$detail = $obj->find($owner_id)){
				$this->tuError('业主不存在');
			}
			if($obj->save(array('owner_id'=>$owner_id,'audit'=>1))){
				$obj->cleanCache();
            	$this->tuSuccess('审核成功', U('community/owner',array('community_id'=>$community_id)));
			}else{
				$this->tuError('审核失败');
			}
        }else{
            $this->tuError('请选择要审核的业主');
        }
    }
	//小区账单
	public function order() {
        $obj = D('Communityorder');
        import('ORG.Util.Page');
        $map = array();
		if($community_id = (int) $this->_param('community_id')) {
            $map['community_id'] = $community_id;
            $community = D('Community')->find($community_id);
            $this->assign('name', $community['name']);
            $this->assign('community_id', $community_id);
        }
		if($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $map['order_date'] = array(array('ELT', $end_date), array('EGT', $bg_date));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $this->assign('bg_date', $bg_date);
                $map['order_date'] = array('EGT', $bg_date);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $this->assign('end_date', $end_date);
                $map['order_date'] = array('ELT', $end_date);
            }
        }
		if(isset($_GET['order_date']) || isset($_POST['order_date'])) {
            $order_date = $this->_param('order_date');
            if ($order_date != 999) {
                $map['order_date'] = $order_date;
            }
            $this->assign('order_date', $order_date);
        } else {
            $this->assign('order_date', 999);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $obj->order(array('order_date' => 'desc'))->where($map)->select();
        $order_ids = array();
        foreach ($list as $k => $val) {
            if($owner = D('Communityowner')->where(array('user_id'=>$val['user_id']))->find()){
				$list[$k]['owner'] = $owner;
			}
			if($community = D('Community')->where(array('community_id'=>$val['community_id']))->find()){
				$list[$k]['community'] = $community;
			}
			if($user = D('Users')->where(array('user_id'=>$val['user_id']))->find()){
				$list[$k]['user'] = $user;
			}
            $order_ids[$val['order_id']] = $val['order_id'];
        }
        $products = D('Communityorderproducts')->where(array('order_id' => array('IN', $order_ids)))->select();
        foreach ($list as $k => $val) {
            foreach ($products as $kk => $v) {
                if ($v['order_id'] == $val['order_id']) {
                    $list[$k]['type' . $v['type']] = $v;
                }
            }
        }
        $this->assign('days', $days = $obj->getDays());
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	
	 public function manage(){
        $user_id = (int) $this->_get('user_id');
        if (empty($user_id)) {
            $this->error('请选择用户');
        }
        if (!($detail = D('Users')->find($user_id))) {
            $this->error('没有该用户');
        }
        setUid($user_id);
        header("Location:" . U('property/index/index'));
        die;
    }
}