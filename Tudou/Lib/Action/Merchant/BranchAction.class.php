<?php
class BranchAction extends CommonAction{
     private $create_fields = array('parent_id','user_id','business_id','user_id','city_id', 'area_id', 'logo', 'cate_id', 'user_guide_id','mobile', 'logo', 'photo', 'shop_name', 'contact', 'details', 'business_time', 'area_id', 'addr', 'lng', 'lat');
    public function _initialize(){
        parent::_initialize();
        $this->assign('city', D('City')->fetchAll());
        $this->assign('area', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
    }
    public function index(){
        $obj = D('Shop');
        import('ORG.Util.Page');
        $map = array('closed' => 0, 'parent_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|addr'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $obj->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $obj->where($map)->order(array('create_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
       if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Shop');
            $details = $this->_post('details', 'htmlspecialchars');
            if($words = D('Sensitive')->checkWords($details)) {
                $this->tuError('商家介绍含有敏感词：' . $words, 2000, true);
            }
            $ex = array('details' => $details, 'near' => $data['near'], 'price' => $data['price'], 'business_time' => $data['business_time']);
            unset($data['near'], $data['price'], $data['business_time']);
            if ($shop_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($shop_id, 1);
                $ex['wei_pic'] = $wei_pic;
                D('Shopdetails')->upDetails($shop_id, $ex);
				D('Shopguide')->upAdd($data['user_guide_id'],$shop_id);//新增到表
                $this->tuSuccess('恭喜您申请分店成功', U('branch/index'));
            }
            $this->tuError('申请失败');
        } else {
            $this->assign('cates', D('Shopcate')->fetchAll());
            $this->assign('areas', $areas = D('Area')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['parent_id'] = (int) $this->shop_id;
		$data['user_id'] = (int) $data['user_id'];
		if(empty($data['user_id'])) {
            $this->tuError('管理员ID不能为空', 2000, true);
        }
		if(!D('Users')->find($data['user_id'])) {
            $this->tuError('你输入的管理员不存在', 2000, true);
        }
        if(D('Shop')->where(array('user_id' => $data['user_id']))->find()) {
            $this->tuError('当前用户ID【'.$data['user_id'].'】已经管理其他商家', 2000, true);
        }
        $guide_ids = htmlspecialchars($data['user_guide_id']);
		$data['user_guide_id'] = explode(',',$guide_ids);
		if($guide_ids){
			if (false == D('Shopguide')->check_user_guide_id($data['user_guide_id'])){
				$this->tuError(D('Shopguide')->getError());
			}
		}
        $data['shop_name'] = htmlspecialchars($data['shop_name']);
        if(empty($data['shop_name'])) {
            $this->tuError('店铺名称不能为空', 2000, true);
        }
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        if(empty($data['lng']) || empty($data['lat'])) {
            $this->tuError('店铺坐标需要设置', 2000, true);
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('分类不能为空', 2000, true);
        }
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->tuError('城市不能为空', 2000, true);
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->tuError('地区不能为空', 2000, true);
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->tuError('商圈不能为空', 2000, true);
        }
        $data['contact'] = htmlspecialchars($data['contact']);
        if (empty($data['contact'])) {
            $this->tuError('联系人不能为空', 2000, true);
        }
        $data['business_time'] = htmlspecialchars($data['business_time']);
        if (empty($data['business_time'])) {
            $this->tuError('营业时间不能为空', 2000, true);
        }
        if (!isImage($data['logo'])) {
            $this->tuError('请上传正确的LOGO', 2000, true);
        }
        if (!isImage($data['photo'])) {
            $this->tuError('请上传正确的店铺图片', 2000, true);
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->tuError('地址不能为空', 2000, true);
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuError('联系方式不能为空', 2000, true);
        }
        $data['qq'] = htmlspecialchars($data['qq']);
        $data['recognition'] = 1;
        $data['user_id'] = $this->uid;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
  
    public function delete($shop_id = 0){
        if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
            $obj = D('Shop');
            if (!($detail = $obj->find($shop_id))) {
                $this->error('请选择要删除的分店');
            }
            if ($detail['closed'] == 1) {
                $this->error('该分店不存在');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('非法操作');
            }
            $obj->save(array('branch_id' => $shop_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('branch/index'));
        } else {
            $this->tuError('请选择要删除的分店');
        }
    }
	//登录分店
  public function login($shop_id){
        $obj = D('Shop');
        if(!($detail = $obj->find($shop_id))) {
            $this->error('请选择要编辑的商家');
        }
        if(empty($detail['user_id'])) {
            $this->error('该用户没有绑定管理者');
        }
        setUid($detail['user_id']);
        header('Location:' . U('Merchant/index/index'));
        die;
    }
   
   
}