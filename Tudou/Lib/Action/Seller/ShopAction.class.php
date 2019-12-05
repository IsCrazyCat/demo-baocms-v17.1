<?php
class ShopAction extends CommonAction{
	
	
    private $photo_create_fields = array('title', 'photo', 'orderby');
	
    public function about(){
        if($this->isPost()){
            $data = $this->checkFields($this->_post('data', false), array('addr', 'contact', 'tel','mobile', 'business_time', 'delivery_time', 'is_ele_print','is_tuan_print','is_goods_print','is_ding_print','apiKey', 'mKey', 'partner', 'machine_code'));
            $data['addr'] = htmlspecialchars($data['addr']);
            if (empty($data['addr'])) {
                $this->tuMsg('店铺地址不能为空');
            }
            $data['contact'] = htmlspecialchars($data['contact']);
            $data['tel'] = htmlspecialchars($data['tel']);
			$data['mobile'] = htmlspecialchars($data['mobile']);
            if (empty($data['mobile'])) {
                $this->tuError('手机不能为空');
            }
            if (!isMobile($data['mobile'])) {
                $this->tuError('手机格式不正确');
            }
            $data['business_time'] = htmlspecialchars($data['business_time']);
            $data['shop_id'] = $this->shop_id;
            $data['delivery_time'] = (int) $data['delivery_time'];
			
			$data['is_ele_print'] = (int) $_POST['is_ele_print'];
			$data['is_tuan_print'] = (int) $_POST['is_tuan_print'];
			$data['is_goods_print'] = (int) $_POST['is_goods_print'];
			$data['is_ding_print'] = (int) $_POST['is_ding_print'];
			
			
            $data['apiKey'] = htmlspecialchars($data['apiKey']);
            $data['mKey'] = htmlspecialchars($data['mKey']);
            $data['partner'] = htmlspecialchars($data['partner']);
            $data['machine_code'] = htmlspecialchars($data['machine_code']);
            $data['service'] = $data['service'];
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->tuMsg('商家介绍含有敏感词：' . $words);
            }
            $ex = array('details' => $details, 'near' => $data['near'], 'business_time' => $data['business_time'], 'delivery_time' => $data['delivery_time']);
            unset($data['business_time'], $data['near'], $data['delivery_time']);
            if (false !== D('Shop')->save($data)) {
                D('Shopdetails')->upDetails($this->shop_id, $ex);
                $this->tuMsg('操作成功', U('shop/about'));
            }
            $this->tuMsg('操作失败');
        } else {
            $this->assign('ex', D('Shopdetails')->find($this->shop_id));
            $this->display();
        }
    }
    //图片列表
    public function photo(){
        $Shoppic = D('Shoppic');
        $map = array('shop_id' => $this->shop_id);
        $list = $Shoppic->where($map)->order(array('orderby' => 'desc'))->select();
        $this->assign('list', $list);
        $this->assign('sig', md5($this->shop_id . C('AUTH_KEY')));
        $this->display();
    }
    //传图
    public function photo_create()
    {
        if ($this->isPost()) {
            $data = $this->photo_createCheck();
            $obj = D('Shoppic');
            if ($obj->add($data)) {
                $this->tuMsg('添加成功，请等待网站管理员审核', U('shop/photo'));
            }
            $this->tuMsg('操作失败');
        } else {
            $this->display();
        }
    }
    private function photo_createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->photo_create_fields);
        $data['shop_id'] = $this->shop_id;
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuMsg('标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuMsg('请上传环境图图片');
        }
        if (!isImage($data['photo'])) {
            $this->tuMsg('环境图图片格式不正确');
        }
		$data['audit'] = 1;
        $data['orderby'] = (int) $data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function photo_delete($pic_id = 0){
        $pic_id = (int) $pic_id;
        $obj = D('Shoppic');
        $detail = $obj->find($pic_id);
        if ($detail['shop_id'] == $this->shop_id) {
            $obj->delete($pic_id);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功', U('shop/photo')));
        }
        $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
    }
	
	//Wap购买短信
    public function sms() {
        $detail = D('Smsshop')->where(array('type' => shop, 'shop_id' => $this->shop_id,'status'=>0))->find();
        if($this->isPost()){
            $num = (int) $_POST['num'];
            if($num <= 0) {
                $this->tuMsg('购买数量不合法');
            }
			if(false == D('Smsshop')->buy($num,$this->uid,$this->shop_id)){
				$this->tuMsg(D('Smsshop')->getError());
			}else{
				$this->tuMsg('购买短信成功', U('shop/sms'));
			}
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
	
	//商家等级权限 
	public function grade(){
        $Shopgrade = D('Shopgrade');
        import('ORG.Util.Page');
        $map = array('closed'=>0);
        $count = $Shopgrade->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Shopgrade->where($map)->order(array('orderby' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $list[$k]['shop_count'] = $Shopgrade->get_shop_count($val['grade_id']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	//商家等级权限 
	public function permission($grade_id = 0){
        $grade_id = (int) $grade_id;
        $obj = D('Shopgrade');
        if (!($detail = $obj->find($grade_id))) {
            $this->error('请选择要查看的商家等级');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
	
	//购买等级权限
	public function pay_permission(){
        $grade_id = (int) $this->_param('grade_id');
		$shop_id = (int) $this->_param('shop_id');
        if(!$obj = D('Shopgradeorder')->shop_pay_grade($grade_id,$shop_id)) {
			$this->ajaxReturn(array('code'=>'0','msg'=>D('Shopgradeorder')->getError()));
        }else{
			 $this->ajaxReturn(array('code'=>'1','msg'=>'恭喜您购买等级成功','url'=>U('shop/grade')));
		}
        $this->display();
    }
}