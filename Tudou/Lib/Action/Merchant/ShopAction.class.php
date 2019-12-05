<?php
class ShopAction extends CommonAction{
	
	 private $edit_fields = array('contact', 'tel','mobile','tags', 'qq', 'business_time', 'express_price', 'commission', 'delivery_time', 'panorama_url', 'addr', 'is_ele_print', 'is_tuan_print', 'is_goods_print', 'is_booking_print', 'is_appoint_print', 'lng', 'lat');
	 
	 //基本信息修改
	  public function about(){
        if($this->isPost()){
            $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
			$data['shop_id'] = $this->shop_id;
			$data['contact'] = htmlspecialchars($data['contact']);
			$data['tel'] = htmlspecialchars($data['tel']);
			$data['mobile'] = htmlspecialchars($data['mobile']);
            if(empty($data['mobile'])){
                $this->tuError('手机不能为空');
            }
            if(!isMobile($data['mobile'])){
                $this->tuError('手机格式不正确');
            }
			$data['tags'] = htmlspecialchars($data['tags']);
			$data['qq'] = htmlspecialchars($data['qq']);
			$data['business_time'] = htmlspecialchars($data['business_time']);
			$data['express_price'] = (int) ($data['express_price']*100);
			if(empty($data['express_price'])){
				$this->tuError('配送费必须设置');
			}
			if($data['express_price'] < 10){
				$this->tuError('配送费必须大于0.1元');
			}
			$data['commission'] = (int) $data['commission'];
			$data['delivery_time'] = (int) $data['delivery_time'];
			$data['panorama_url'] = htmlspecialchars($data['panorama_url']);
            $data['addr'] = htmlspecialchars($data['addr']);
            if(empty($data['addr'])){
                $this->tuError('店铺地址不能为空');
            }
			$data['is_ele_print'] = (int) $data['is_ele_print'];
			$data['is_tuan_print'] = (int) $data['is_tuan_print'];
			$data['is_goods_print'] = (int) $data['is_goods_print'];
			$data['is_booking_print'] = (int) $data['is_booking_print'];
			$data['is_appoint_print'] = (int) $data['is_appoint_print'];
			$data['lng'] = htmlspecialchars($data['lng']);
        	$data['lat'] = htmlspecialchars($data['lat']);
			$data['audit'] = 1;
			$details = $this->_post('details', 'SecurityEditorHtml');
            if($words = D('Sensitive')->checkWords($details)){
                $this->tuError('商家介绍含有敏感词：' . $words);
            }
            $ex = array('details' => $details, 'business_time' => $data['business_time'], 'delivery_time' => $data['delivery_time']);
            unset($data['business_time'],$data['delivery_time']);
            if(false !== D('Shop')->save($data)){
                D('Shopdetails')->upDetails($this->shop_id, $ex);
                $this->tuSuccess('操作成功', U('shop/about'));
            }
            $this->tuError('操作失败');
        }else{
            $this->assign('ex', D('Shopdetails')->find($this->shop_id));
            $this->display();
        }
    }
	
	
	 
    public function index(){
        $this->display();
    }
    public function logo(){
        if ($this->isPost()) {
            $logo = $this->_post('logo', 'htmlspecialchars');
            if (empty($logo)) {
                $this->tuError('请上传商铺LOGO');
            }
            if (!isImage($logo)) {
                $this->tuError('商铺LOGO格式不正确');
            }
            $data = array('shop_id' => $this->shop_id, 'logo' => $logo);
            if (D('Shop')->save($data)) {
                $this->tuSuccess('上传LOGO成功', U('shop/logo'));
            }
            $this->tuError('更新LOGO失败');
        } else {
            $this->display();
        }
    }
    public function image(){
        if($this->isPost()){
            $photo = $this->_post('photo', 'htmlspecialchars');
            if(empty($photo)){
                $this->tuError('请上传商铺形象照');
            }
            if(!isImage($photo)){
                $this->tuError('商铺形象照格式不正确');
            }
			
			$logo = $this->_post('logo', 'htmlspecialchars');
            if(empty($logo)){
                $this->tuError('请上传商铺LOGO');
            }
            if(!isImage($logo)){
                $this->tuError('LOGO格式不正确');
            }
			$service_weixin_qrcode = $this->_post('service_weixin_qrcode', 'htmlspecialchars');
            $data = array('shop_id' => $this->shop_id, 'photo' => $photo, 'logo' => $logo, 'service_weixin_qrcode' => $service_weixin_qrcode);
            if(false !== D('Shop')->save($data)){
                $this->tuSuccess('上传成功', U('shop/image'));
            }
            $this->tuError('更新形象照失败');
        }else{
            $this->display();
        }
    }
	
	//其他设置
    public function service(){
        $obj = D('Shop');
        if (!($detail = $obj->find($this->shop_id))) {
            $this->tuError('请选择要编辑的商家');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->tuError('请不要非法操作');
        }
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('is_ele_print','is_tuan_print','is_goods_print','is_booking_print','is_appoint_print','panorama_url','apiKey', 'mKey', 'partner', 'machine_code', 'service'));
			$data['is_ele_print'] = (int) $_POST['is_ele_print'];
			$data['is_tuan_print'] = (int) $_POST['is_tuan_print'];
			$data['is_goods_print'] = (int) $_POST['is_goods_print'];
			$data['is_booking_print'] = (int) $_POST['is_booking_print'];
			$data['is_appoint_print'] = (int) $_POST['is_appoint_print'];
			$data['panorama_url'] = htmlspecialchars($data['panorama_url']);
            $data['apiKey'] = htmlspecialchars($data['apiKey']);
            $data['mKey'] = htmlspecialchars($data['mKey']);
            $data['partner'] = htmlspecialchars($data['partner']);
            $data['machine_code'] = htmlspecialchars($data['machine_code']);
            $data['service'] = $data['service'];
            $data['shop_id'] = $this->shop_id;

            if (false !== $obj->save($data)) {
                $this->tuSuccess('更新成功', U('shop/service'));
            }
            $this->tuError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
	 
   
    //购买短信
    public function sms() {
        $sms_shop_money = $this->_CONFIG['sms_shop']['sms_shop_money']; //单价
        $sms_shop_small = $this->_CONFIG['sms_shop']['sms_shop_small'];//最少购买多少条
        $sms_shop_big = $this->_CONFIG['sms_shop']['sms_shop_big'];//最大购买多少条
        $nums = D('Smsshop')->where(array('type' => shop, 'shop_id' => $this->shop_id))->find();
        if(IS_POST){
            $num = (int) $_POST['num'];
            if($num <= 0) {
                $this->tuError('购买数量不合法');
            }
			if(false == D('Smsshop')->buy($num,$this->uid,$this->shop_id)){
				$this->tuError(D('Smsshop')->getError());
			}else{
				$this->tuSuccess('购买短信成功', U('shop/sms'));
			}
        } else {
            $this->assign('sms_shop_money', $sms_shop_money);
            $this->assign('sms_shop_small', $sms_shop_small);
            $this->assign('sms_shop_big', $sms_shop_big);
            $this->assign('nums', $nums);
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
            $this->tuError('请选择要查看的商家等级');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
	
	//购买等级权限
	public function pay_permission(){
        $grade_id = (int) $this->_param('grade_id');
		$shop_id = (int) $this->_param('shop_id');
        if (!$obj = D('Shopgradeorder')->shop_pay_grade($grade_id,$shop_id)) {
			$this->tuError(D('Shopgradeorder')->getError());	
        }else{
			 $this->tuSuccess('恭喜您购买等级成功', U('shop/grade'));
		}
        $this->display();
    }
	
	
	public function village($type,$id){
        $type = (int) $type;
		$id = (int) $id;
        if($this->isPost()) {
           $data = $this->_post('data', false);
		   $data['type'] = (int) $data['type'];
		   $data['id'] = (int) $data['id'];
		   $data['village_id'] = (int) $data['village_id'];
		   if(!$data['village_id']){
			   $this->tuError('没选择乡村');
		   }
		   if(D('VillageEnter')->where(array('type'=>$data['type'],'id'=>$data['id']))->find()){
			   $this->tuError('请不要重复入驻');
		   }
		   $data['shop_id'] = $this->shop_id;
		   $data['intro'] = htmlspecialchars($data['intro']);
		   if(empty($data['intro'])){
			   $this->tuError('填写一点理由');
		   }
		   $data['create_time'] = NOW_TIME;
           $data['create_ip'] = get_client_ip();
           if(D('VillageEnter')->add($data)){
			   if($data['type'] == 1){
				  $this->tuSuccess('商品申请入驻乡村成功', U('goods/index')); 
			   }else{
				  $this->tuSuccess('抢购申请入驻乡村成功', U('tuan/index')); 
			   }
           }
           $this->tuError('操作失败');
        }else{
			$this->assign('type', $type);
			$this->assign('id', $id);
			$this->assign('villages', $villages = D('Village')->select());
            $this->display();
        }
    }
	
	
	//商家模板
	public function template(){
        $obj = D('Template');
		//手机模板
        $list = $obj->where(array('closed'=>0,'type'=>2,'is_mobile'=>1,))->order(array('template_id' =>'asc'))->select();//手机模板
		foreach($list as $key => $val){
			$list[$key]['state'] = (int)D('TemplateOrder')->getTemplateOrderState($val['template_id'],$this->shop_id);
        }	
		$this->assign('list', $list);
		//电脑模板
        $list2 = $obj->where(array('closed'=>0,'type'=>2,'is_mobile'=>0,))->order(array('template_id' =>'asc'))->select();//电脑模板
		foreach($list2 as $k => $val2){
			$list2[$k]['state'] = (int)D('TemplateOrder')->getTemplateOrderState($val2['template_id'],$this->shop_id);
        }	
		$this->assign('list2', $list2);
        $this->display();
    }
	
	
	//购买商家模板
	public function pay_template(){
        $template_id = (int) $this->_param('template_id');
		$shop_id = (int) $this->_param('shop_id');
        if (!$obj = D('TemplateOrder')->shop_pay_template($template_id,$shop_id)) {
			$this->tuError(D('TemplateOrder')->getError());	
        }else{
			 $this->tuSuccess('恭喜您购买模板成功', U('shop/template'));
		}
        $this->display();
    }
	
	//设置模板默认
	public function template_default($template_id = 0){
        $template_id = (int) $this->_param('template_id');
        if(empty($template_id)){
            $this->tuError('请选择模版');
        }
        if(!($detail = D('Template')->find($template_id))){
            $this->tuError('该模版不存在');
        }
		if($detail['is_mobile'] == 1){
			D('Shop')->save(array('shop_id' =>$this->shop_id, 'wap_template_id' => $template_id));
		}else{
			D('Shop')->save(array('shop_id' =>$this->shop_id, 'pc_template_id' => $template_id));
		}
        cookie('think_shop_template',$detail['theme'], 864000);
        D('Template')->cleanCache();
        $this->tuSuccess('选择模板成功', U('shop/template'));
    }
	
	
}