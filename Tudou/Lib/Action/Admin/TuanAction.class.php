<?php

class TuanAction extends CommonAction {

    private $create_fields = array('shop_id', 'orderby', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'thumb', 'price', 'tuan_price', 'settlement_price','mobile_fan', 'num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook','xiadan','xiangou', 'activity_id','instructions');
    private $edit_fields = array('shop_id', 'orderby', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'thumb', 'price', 'tuan_price', 'settlement_price','mobile_fan', 'num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook','xiadan','xiangou', 'activity_id','instructions');

    public function _initialize() {
        parent::_initialize();
        $this->Tuancates = D('Tuancate')->fetchAll();
        $this->assign('cates', $this->Tuancates);
        $this->assign('ranks',D('Userrank')->fetchAll());
    }

    public function index() {
        $Tuan = D('Tuan');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Tuancate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = ($audit === 1 ? 1 : 0);
            $this->assign('audit', $audit);
        }
        $count = $Tuan->where($map)->count();  
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Tuan->where($map)->order(array('tuan_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val = $Tuan->_format($val);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('cates', D('Tuancate')->fetchAll());
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }

  

        public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Tuan');
			$details = $this->_post('details', 'SecurityEditorHtml');
            if (empty($details)) {
                $this->tuError('抢购详情不能为空');
            }
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->tuError('详细内容含有敏感词：' . $words);
            }
            $instructions = $this->_post('instructions', 'SecurityEditorHtml');
            if (empty($instructions)) {
                $this->tuError('购买须知不能为空');
            }
            if ($words = D('Sensitive')->checkWords($instructions)) {
                $this->tuError('购买须知含有敏感词：' . $words);
            }
          
            $branch_id = $this->_post('branch_id', false);
            foreach ($branch_id as $k => $val) {
                if (!$brdetail = D('Shopbranch')->find($val)) {
                    unset($branch_id[$k]);
                }
                if ($brdetail['shop_id'] != $this->shop_id) {
                    unset($branch_id[$k]);
                }
            }
            $branch = implode(',', $branch_id);
            $data['branch_id'] = $branch;
            $thumb = $this->_param('thumb', false);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
            $data['thumb'] = serialize($thumb);
            if ($tuan_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($tuan_id, 2); //抢购类型是2
                $obj->save(array('tuan_id' => $tuan_id, 'wei_pic' => $wei_pic));
                D('Tuandetails')->add(array('tuan_id' => $tuan_id, 'details' => $details, 'instructions' => $instructions));
                $this->tuSuccess('添加成功', U('tuan/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->tuError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->tuError('请选择正确的商家');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('抢购分类不能为空');
        }
		 $Tuancate = D('Tuancate')->where(array('cate_id' => $data['cate_id']))->find();
		 $parent_id = $Tuancate['parent_id'];
		 if ($parent_id == 0) {
			$this->tuError('请选择二级分类');
		 }
		 
        $data['lng'] = $shop['lng'];
        $data['lat'] = $shop['lat'];
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];

        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('商品名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('副标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传图片');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('图片格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->tuError('市场价格不能为空');
        }
        $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if (empty($data['tuan_price'])) {
            $this->tuError('抢购价格不能为空');
        }
        $data['settlement_price'] = (int) ($data['settlement_price'] * 100);
        if (empty($data['settlement_price'])) {
            $this->tuError('结算价格不能为空');
        }
        $data['mobile_fan'] = (int) ($data['mobile_fan'] * 100);
        if($data['mobile_fan'] < 0 || $data['mobile_fan'] >= $data['settlement_price']){
            $this->tuError('手机下单优惠金额不正确');
        }
        $data['use_integral'] = (int) $data['use_integral'];
		//抢购检测积分合法性开始
		if (D('Tuan')->check_add_use_integral($data['use_integral'],$data['settlement_price'])) {//传2参数
            //这里暂时保留，后期增加逻辑;
        }else{
			$this->tuError(D('Tuan')->getError(), 3000, true);	  
		}
		//抢购检测积分合法性结束

        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->tuError('库存不能为空');
        } $data['sold_num'] = (int) $data['sold_num'];

        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->tuError('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->tuError('开始时间格式不正确');
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuError('结束时间格式不正确');
        }
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_new'] = (int) $data['is_new'];
        $data['is_chose'] = (int) $data['is_chose'];
        $data['freebook'] = (int) $data['freebook'];
        $data['is_return_cash'] = (int) $data['is_return_cash'];
		$data['xiadan'] = (int) $data['xiadan'];
		$data['xiangou'] = (int) $data['xiangou'];
        $data['fail_date'] = htmlspecialchars($data['fail_date']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function edit($tuan_id = 0) {
        if ($tuan_id = (int) $tuan_id) {
            $obj = D('Tuan');
            if (!$detail = $obj->find($tuan_id)) {
                $this->tuError('请选择要编辑的抢购');
            }
            $tuan_details = D('Tuandetails')->getDetail($tuan_id);

            if ($this->isPost()) {
                $data = $this->editCheck();
                $details = $this->_post('details', 'SecurityEditorHtml');
                if (empty($details)) {
                    $this->tuError('抢购详情不能为空');
                }
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->tuError('详细内容含有敏感词：' . $words);
                }
                $instructions = $this->_post('instructions', 'SecurityEditorHtml');
                if (empty($instructions)) {
                    $this->tuError('购买须知不能为空');
                }
                if ($words = D('Sensitive')->checkWords($instructions)) {
                    $this->tuError('购买须知含有敏感词：' . $words);
                }
                $thumb = $this->_param('thumb', false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['thumb'] = serialize($thumb);
                $branch_id = $this->_post('branch_id', false);
                foreach ($branch_id as $val) {
                    if (!$brdetail = D('Shopbranch')->find($val)) {
                        unset($val);
                    }
                    if ($brdetail['shop_id'] != $this->shop_id) {
                        unset($val);
                    }
                }
                $branch = implode(',', $branch_id);
                $data['branch_id'] = $branch;
                $data['tuan_id'] = $tuan_id;
                if (!empty($detail['wei_pic'])) {
                    if (true !== strpos($detail['wei_pic'], "https://mp.weixin.qq.com/")) {
                        $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                        $data['wei_pic'] = $wei_pic;
                    }
                } else {
                    $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                    $data['wei_pic'] = $wei_pic;
                }
                $ex = array(
                    'tuan_id' => $tuan_id,
                    'details' => $details,
                    'instructions' => $instructions,
                );
                if (false !== $obj->save($data)) {
                    D('Tuandetails')->save($ex);
                    $this->tuSuccess('操作成功', U('tuan/index'));
                }
                $this->tuError('操作失败');
            } else {
                $branch = D('Shopbranch')->where(array('shop_id' => $detail['shop_id'], 'closed' => 0, 'audit' => 1))->select();
                $this->assign('branch', $branch);
                $hd = D('Activity')->order(array('orderby' => 'asc'))->where(array('shop_id'=>$detail['shop_id'],'bg_date' => array('ELT', TODAY), 'end_date' => array('EGT', TODAY), 'sign_end' => array('EGT', TODAY), 'closed' => 0, 'audit' => 1))->select();
                $this->assign('hd',$hd);
                $this->assign('detail', $obj->_format($detail));
                $thumb = unserialize($detail['thumb']);
                $this->assign('thumb', $thumb);
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('tuan_details', $tuan_details);
                $branch_id = explode(',', $detail['branch_id']);
                $this->assign('branch_id', $branch_id);
                $this->display();
            }
        } else {
            $this->tuError('请选择要编辑的抢购');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->tuError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->tuError('请选择正确的商家');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->tuError('抢购分类不能为空');
        }
		
		 $Tuancate = D('Tuancate')->where(array('cate_id' => $data['cate_id']))->find();
		 $parent_id = $Tuancate['parent_id'];
		 if ($parent_id == 0) {
			$this->tuError('请选择二级分类');
		 }
		 
		 
        $data['lng'] = $shop['lng'];
        $data['lat'] = $shop['lat'];
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['business_id'] = $shop['business_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->tuError('商品名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->tuError('副标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->tuError('请上传图片');
        }
        if (!isImage($data['photo'])) {
            $this->tuError('图片格式不正确');
        }$data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->tuError('市场价格不能为空');
        } $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if (empty($data['tuan_price'])) {
            $this->tuError('抢购价格不能为空');
        }
        $data['settlement_price'] = (int) ($data['settlement_price'] * 100);
        if (empty($data['settlement_price'])) {
            $this->tuError('结算价格不能为空');
        }
        $data['mobile_fan'] = (int) ($data['mobile_fan'] * 100);
        if($data['mobile_fan'] < 0 || $data['mobile_fan'] >= $data['settlement_price']){
            $this->tuError('手机下单优惠金额不正确');
        }
        $data['use_integral'] = (int) $data['use_integral'];
		//抢购检测积分合法性开始
		if (D('Tuan')->check_add_use_integral($data['use_integral'],$data['settlement_price'])) {//传2参数
            //这里暂时保留，后期增加逻辑;
        }else{
			$this->tuError(D('Tuan')->getError(), 3000, true);	  
		}
		//抢购检测积分合法性结束
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->tuError('库存不能为空');
        } $data['sold_num'] = (int) $data['sold_num'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->tuError('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->tuError('开始时间格式不正确');
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->tuError('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->tuError('结束时间格式不正确');
        }
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_new'] = (int) $data['is_new'];
        $data['is_chose'] = (int) $data['is_chose'];
        $data['freebook'] = (int) $data['freebook'];
        $data['is_return_cash'] = (int) $data['is_return_cash'];

		$data['xiadan'] = (int) $data['xiadan'];
		$data['xiangou'] = (int) $data['xiangou'];
        $data['fail_date'] = htmlspecialchars($data['fail_date']);
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function delete($tuan_id = 0) {
        if (is_numeric($tuan_id) && ($tuan_id = (int) $tuan_id)) {
            $obj = D('Tuan');
            $obj->save(array('tuan_id' => $tuan_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('tuan/index'));
        } else {
            $tuan_id = $this->_post('tuan_id', false);
            if (is_array($tuan_id)) {
                $obj = D('Tuan');
                foreach ($tuan_id as $id) {
                    $obj->save(array('tuan_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('删除成功', U('tuan/index'));
            }
            $this->tuError('请选择要删除的抢购');
        }
    }

    public function audit($tuan_id = 0) {
        if (is_numeric($tuan_id) && ($tuan_id = (int) $tuan_id)) {
            $obj = D('Tuan');
            $obj->save(array('tuan_id' => $tuan_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('tuan/index'));
        } else {
            $tuan_id = $this->_post('tuan_id', false);
            if (is_array($tuan_id)) {
                $obj = D('Tuan');
                foreach ($tuan_id as $id) {
                    $obj->save(array('tuan_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('tuan/index'));
            }
            $this->tuError('请选择要审核的抢购');
        }
    }

    public function cancel($tuan_id = 0) {
        if (is_numeric($tuan_id) && ($tuan_id = (int) $tuan_id)) {
            $obj = D('Tuan');
            $obj->save(array('tuan_id' => $tuan_id, 'is_seckill' => 0));
            $this->tuSuccess('秒杀活动取消成功', U('tuan/index'));
        } else {
            $tuan_id = $this->_post('tuan_id', false);
            if (is_array($tuan_id)) {
                $obj = D('Tuan');
                foreach ($tuan_id as $id) {
                    $obj->save(array('tuan_id' => $id, 'audit' => 0));
                }
                $this->tuSuccess('秒杀活动取消成功', U('tuan/index'));
            }
            $this->tuError('请选择要取消秒杀的抢购');
        }
    }

}
