<?php
class  AppointAction extends CommonAction{
	
	public function _initialize(){
        parent::_initialize();
		$this->assign('orderTypes', $orderTypes = D('Appoint')->getAppoinOrderType());
        $this->assign('stars', $stars = D('Appoint')->getAppoinStar());
		$this->assign('certs', $certs = D('Appoint')->getAppoinCert());
		$this->assign('dates', $dates = D('Appoint')->getAppoinDate());
		$this->assign('zodiacs', $zodiacs = D('Appoint')->getAppoinZodiac());
		$this->assign('constellatorys', $constellatorys = D('Appoint')->getAppoinConstellatory());
		$this->assign('mandarins', $mandarins = D('Appoint')->getAppoinMandarin());
		$this->assign('cates', D('Appointcate')->fetchAll());
    }
	
	 private $create_fields = array('shop_id','cate_id','city_id','area_id','business_id','lng','lat','price', 'title', 'intro', 'unit','gongju', 'photo','thumb', 'user_name','user_mobile', 'biz_time','end_date','contents');
     private $edit_fields = array('shop_id','cate_id','city_id','area_id','business_id','lng','lat','price', 'title', 'intro', 'unit','gongju', 'photo','thumb', 'user_name','user_mobile', 'biz_time','end_date','contents');
	 
	 private $worker_edit_fields = array('appoint_id','cate_id','city_id','area_id','business_id','star','date_id','price','photo','name','office','height','age','zodiac','constellatory','culture','working_age','household','mandarin','mobile','is_recommend','intro','skill','content','audit');
	 private $worker_cert_edit_fields = array('appoint_id','worker_id','cert','name','photo','intro','is_show');
    
    public function index(){
        $Appoint = D('Appoint');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
		
        if ($keyword) {
            $map['title|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }  
		
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Appointcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
		
        $count = $Appoint->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $Appoint->where($map)->order(array('appoint_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$shop_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
			$list[$key]['num'] = D('Appointworker')->where(array('appoint_id'=>$val['appoint_id'],'closed'=>0))->count();
			
        }		
		
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->display(); 
    }

	//添加家政
	public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Appoint');
            if ($obj->add($data)) {
                $this->tuSuccess('添加成功', U('Appoint/index'));
            }
            $this->tuError('操作失败');
        } else {
            $this->display();
        }
    }
	//添加验证
	 private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		
			$data['shop_id'] = (int) $data['shop_id'];//商家ID
			if (empty($data['shop_id'])) {
            $this->tuError('请您选择商家');
     	    } 
			$shop = D('Shop')->find($data['shop_id']);
			if (empty($shop)) {
				$this->tuError('请选择正确的商家');
			}
			$data['city_id'] = $shop['city_id'];
			$data['area_id'] = $shop['area_id'];
			$data['business_id'] = $shop['business_id'];
			$data['lng'] = $shop['lng'];
			$data['lat'] = $shop['lat'];
        	$data['cate_id'] = (int) $data['cate_id'];//ID
			if (empty($data['cate_id'])) {
				$this->tuError('类型ID不能为空');
			}
			$Appointcate = D('Appointcate')->where(array('cate_id' => $data['cate_id']))->find();
			$parent_id = $Appointcate['parent_id'];
			if ($parent_id == 0) {
			$this->tuError('请选择二级分类');
			}
			
            $data['title'] = htmlspecialchars($data['title']);
			if (empty($data['title'])) {
            $this->tuError('请您填写服务标题');
     	    }
			if ($words = D('Sensitive')->checkWords($data['title'])) {
            $this->tuError('标题内容含有敏感词：' . $words);
			}
			$data['intro'] = htmlspecialchars($data['intro']);//标题名字
			if (empty($data['intro'])) {
            $this->tuError('请您填写服务建议');
     	    }
			if ($words = D('Sensitive')->checkWords($data['intro'])) {
				$this->tuError('服务建议含有敏感词：' . $words);
			}
            $data['price'] = (int)($data['price'] * 100);
			if (empty($data['price'])) {
            $this->tuError('价格不能为空');
            }
            $data['unit']  = htmlspecialchars($data['unit']);
            $data['gongju']  = htmlspecialchars($data['gongju']);
			$data['user_name'] = htmlspecialchars($data['user_name']);
			if (empty($data['user_name'])) {
            $this->tuError('请您填写姓名');
     	    }
			$data['user_mobile'] = htmlspecialchars($data['user_mobile']);
			if (empty($data['user_mobile'])) {
            $this->tuError('请您手机号码');
     	    }
			if (!isPhone($data['user_mobile']) && !isMobile($data['user_mobile'])) {
            $this->tuError('联系电话格式不正确');
            }
			
            $data['photo']  = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
            $this->tuError('请您上传图片');
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
            $data['biz_time']  = htmlspecialchars($data['biz_time']);
			$data['end_date'] = htmlspecialchars($data['end_date']);
			if (empty($data['end_date'])) {
				$this->tuError('结束时间不能为空');
			}
			if (!isDate($data['end_date'])) {
				$this->tuError('结束时间格式不正确');
			}
			$data['contents'] = SecurityEditorHtml($data['contents']);
			if (empty($data['contents'])) {
            $this->tuError('家政内容不能为空');
			}
			if ($words = D('Sensitive')->checkWords($data['contents'])) {
				$this->tuError('家政简介含有敏感词：' . $words);
			}
			$data['audit'] = 1;
        return $data;
    }
	
	 public function edit($appoint_id = 0){
        if ($appoint_id = (int) $appoint_id) {
            $obj = D('Appoint');
            if (!($detail = $obj->find($appoint_id))) {
                $this->tuError('请选择要编辑的活动');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['appoint_id'] = $appoint_id;
                if (false !== $obj->save($data)) {
                    $this->tuSuccess('操作成功', U('appoint/index'));
                }
                $this->tuError('操作失败');
            } else {
                $thumb = unserialize($detail['thumb']);
				$this->assign('thumb', $thumb);
				$this->assign('shops', D('Shop')->find($detail['shop_id']));
				$this->assign('detail', $detail);
				$this->display();
            }
        } else {
            $this->tuError('请选择要编辑的家政');
        }
    }
    
	
	 private function editCheck(){
			$data = $this->checkFields($this->_post('data', false), $this->edit_fields);
			$data['shop_id'] = (int) $data['shop_id'];//商家ID
			if (empty($data['shop_id'])) {
            $this->tuError('请您选择商家');
     	    } 
			$shop = D('Shop')->find($data['shop_id']);
			if (empty($shop)) {
				$this->tuError('请选择正确的商家');
			}
			$data['city_id'] = $shop['city_id'];
			$data['area_id'] = $shop['area_id'];
			$data['business_id'] = $shop['business_id'];
			$data['lng'] = $shop['lng'];
			$data['lat'] = $shop['lat'];
        	$data['cate_id'] = (int) $data['cate_id'];//ID
			if (empty($data['cate_id'])) {
				$this->tuError('类型ID不能为空');
			}
			$Appointcate = D('Appointcate')->where(array('cate_id' => $data['cate_id']))->find();
			$parent_id = $Appointcate['parent_id'];
			if ($parent_id == 0) {
			$this->tuError('请选择二级分类');
			}
			
            $data['title'] = htmlspecialchars($data['title']);
			if (empty($data['title'])) {
            $this->tuError('请您填写服务标题');
     	    }
			if ($words = D('Sensitive')->checkWords($data['title'])) {
            $this->tuError('标题内容含有敏感词：' . $words);
			}
			$data['intro'] = htmlspecialchars($data['intro']);//标题名字
			if (empty($data['intro'])) {
            $this->tuError('请您填写服务建议');
     	    }
			if ($words = D('Sensitive')->checkWords($data['intro'])) {
				$this->tuError('服务建议含有敏感词：' . $words);
			}
            $data['price'] = (int)($data['price'] * 100);
			if (empty($data['price'])) {
            $this->tuError('价格不能为空');
            }
            $data['unit']  = htmlspecialchars($data['unit']);
            $data['gongju']  = htmlspecialchars($data['gongju']);
			$data['user_name'] = htmlspecialchars($data['user_name']);
			if (empty($data['user_name'])) {
            $this->tuError('请您填写姓名');
     	    }
			$data['user_mobile'] = htmlspecialchars($data['user_mobile']);
			if (empty($data['user_mobile'])) {
            $this->tuError('请您手机号码');
     	    }
			if (!isPhone($data['user_mobile']) && !isMobile($data['user_mobile'])) {
            $this->tuError('联系电话格式不正确');
            }
			
            $data['photo']  = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
            $this->tuError('请您上传图片');
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
            $data['biz_time']  = htmlspecialchars($data['biz_time']);
			$data['end_date'] = htmlspecialchars($data['end_date']);
			if (empty($data['end_date'])) {
				$this->tuError('结束时间不能为空');
			}
			if (!isDate($data['end_date'])) {
				$this->tuError('结束时间格式不正确');
			}
            $data['contents'] = SecurityEditorHtml($data['contents']);
			if (empty($data['contents'])) {
            $this->tuError('家政内容不能为空');
			}
			if ($words = D('Sensitive')->checkWords($data['contents'])) {
				$this->tuError('家政简介含有敏感词：' . $words);
			}
			$data['audit'] = 1;
        	return $data;
    }
	
   
	public function delete($appoint_id = 0) {
        if (is_numeric($appoint_id) && ($appoint_id = (int) $appoint_id)) {
            $obj = D('Appoint');
            $obj->save(array('appoint_id' => $appoint_id, 'closed' => 1));
            $this->tuSuccess('删除成功', U('appoint/index'));
        } else {
            $appoint_id = $this->_post('appoint_id', false);
            if (is_array($appoint_id)) {
                $obj = D('Appoint');
                foreach ($appoint_id as $id) {
                    $obj->save(array('appoint_id' => $id, 'closed' => 1));
                }
                $this->tuSuccess('批量删除成功', U('appoint/index'));
            }
            $this->tuError('请选择要删除的预约项目');
        }
    }
	
	public function audit($appoint_id = 0) {
        if(is_numeric($appoint_id) && ($appoint_id = (int) $appoint_id)) {
            $obj = D('Appoint');
            $obj->save(array('appoint_id' => $appoint_id, 'audit' => 1));
            $this->tuSuccess('审核成功', U('appoint/index'));
        }else{
            $appoint_id= $this->_post('appoint_id', false);
            if(is_array($appoint_id)){
                $obj = D('Appoint');
                foreach ($appoint_id as $id){
                    $obj->save(array('appoint_id' => $id, 'audit' => 1));
                }
                $this->tuSuccess('审核成功', U('appoint/index'));
            }
            $this->tuError('请选择要审核的优惠券');
        }
    }
	//技师列表，由于数量不多，暂时不做分页
	public function worker($appoint_id = 0) {
        if ($appoint_id = (int) $appoint_id) {
			$Appoint = D('Appoint');
            $Appointworker = D('Appointworker');
			
			$map = array('closed' => 0);
			$map = array('appoint_id' => $appoint_id);
			
			$keyword = $this->_param('keyword', 'htmlspecialchars');
			if($keyword){
				$map['name|office|intro|skill|content|mobile'] = array('LIKE', '%' . $keyword . '%');
				$this->assign('keyword', $keyword);
			}
		
			
			if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
				$bg_time = strtotime($bg_date);
				$end_time = strtotime($end_date);
				$map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
				$this->assign('bg_date', $bg_date);
				$this->assign('end_date', $end_date);
			}else{
				if($bg_date = $this->_param('bg_date', 'htmlspecialchars')){
					$bg_time = strtotime($bg_date);
					$this->assign('bg_date', $bg_date);
					$map['create_time'] = array('EGT', $bg_time);
				}
				if($end_date = $this->_param('end_date', 'htmlspecialchars')){
					$end_time = strtotime($end_date);
					$this->assign('end_date', $end_date);
					$map['create_time'] = array('ELT', $end_time);
				}
			}
		
		
			if(isset($_GET['star']) || isset($_POST['star'])){
				$star = (int) $this->_param('star');
				if($star != 999){
					$map['star'] = $star;
				}
				$this->assign('star', $star);
			}else{
				$this->assign('star', 999);
			}
		
			$detail = $Appoint->find($appoint_id);
			$this->assign('detail', $detail);
			$this->assign('list', $list = $Appointworker->where($map)->select());
			$this->display();
        }else{
            $this->error('请选择要设置的技师');
        }
    }
	

	
	
    //添加技师
	public function worker_create($appoint_id){
		if ($appoint_id = (int) $appoint_id){
			$Appoint = D('Appoint');
            $Appointworker = D('Appointworker');
            if(!$detail = $Appoint->find($appoint_id)){
                $this->error('请选择要设置的家政');
            }
            if($detail['closed'] != 0) {
                $this->error('该家政已被删除');
            }
			if($data = $this->_post()){
				$data = $this->worker_editCheck();
				
				$data['appoint_id'] = $appoint_id;
				
				$data['city_id'] = $detail['city_id'];
				$data['area_id'] = $detail['area_id'];
				$data['business_id'] = $detail['business_id'];
				
				$date_ids = $this->_post('date_id', false);
				$date_id = implode(',', $date_ids);
				$data['date_id'] = $date_id;
				
				if($Appointworker->add($data)) {
					$this->tuSuccess('操作成功', U('appoint/worker',array('appoint_id'=>$detail['appoint_id'])));
				}
				$this->tuError('操作失败');
			}else{
				$this->assign('detail', $detail);
				$this->display();
			}

		} else {
            $this->error('请选择要设置的家政');
        }
	}
	

	
	
    //编辑技师
	public function worker_edit($worker_id){
		if ($worker_id = (int) $worker_id) {
			$Appoint = D('Appoint');
            $Appointworker = D('Appointworker');
			if(!$worker = $Appointworker->find($worker_id)){
                $this->tuError('修改的内容不存在');
            }
			$appoint_id = $worker['appoint_id'];
            if(!$detail = $Appoint->find($appoint_id)){
                $this->tuError('请选择要设置的家政');
            }
            if($detail['closed'] != 0) {
                $this->tuError('该家政已被删除');
            }	
			
			if ($this->isPost()) {
                $data = $this->worker_editCheck();
				$data['worker_id'] = $worker_id;
				$data['appoint_id'] = $appoint_id;
				$data['city_id'] = $detail['city_id'];
				$data['area_id'] = $detail['area_id'];
				$data['business_id'] = $detail['business_id'];
				
				$date_ids = $this->_post('date_id', false);
				$date_id = implode(',', $date_ids);
				$data['date_id'] = $date_id;
				
				
				if(false !==  $Appointworker->save($data)) {
					$this->tuSuccess('操作成功', U('appoint/worker',array('appoint_id'=>$detail['appoint_id'])));
				}else{
					$this->tuError('操作失败');
				} 
            }else{
				$this->assign('date_ids', $date_ids = explode(',', $worker['date_id']));
                $this->assign('worker', $worker);//输出
				$this->assign('detail', $detail);
				$this->display();
            }
		}else{
            $this->tuError('修改的内容不存在');
        }
	}
	
	
	 private function worker_editCheck(){
			$data = $this->checkFields($this->_post('data', false), $this->worker_edit_fields);
			
			$data['cate_id'] = (int) $data['cate_id'];
			if(empty($data['cate_id'])){
				$this->tuError('类型ID不能为空');
			}
			$Appointcate = D('Appointcate')->where(array('cate_id' => $data['cate_id']))->find();
			if($Appointcate['parent_id'] == 0){
				$this->tuError('请选择二级分类');
			}
			
			$data['star'] = (int)$data['star'];
			if(empty($data['star'])){
				$this->tuError('技师星级不能为空');
			}
		
            $data['price'] = htmlspecialchars($data['price'])*100;
			if (empty($data['price'])) {
            	$this->tuError('请填写价格');
     	    }
			$data['photo']  = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
            	$this->tuError('请您上传头像');
            }
			$data['name'] = htmlspecialchars($data['name']);
			if (empty($data['name'])) {
            	$this->tuError('请填写姓名');
     	    }
			$data['office'] = htmlspecialchars($data['office']);
			if(empty($data['office'])) {
            	$this->tuError('请填写职位');
     	    }
			//新加入开始
			$data['height'] = (int)($data['height']);
			if(empty($data['height'])){
            	$this->tuError('请填写身高');
     	    }
			$data['age'] = (int)($data['age']);
			if(empty($data['age'])){
            	$this->tuError('请填写身高');
     	    }
			$data['zodiac'] = (int)($data['zodiac']);
			if(empty($data['zodiac'])){
            	$this->tuError('请选择生肖');
     	    }
			$data['constellatory'] = (int)($data['constellatory']);
			if(empty($data['constellatory'])){
            	$this->tuError('请选择星座');
     	    }
			$data['culture'] = htmlspecialchars($data['culture']);
			if(empty($data['culture'])) {
            	$this->tuError('请您填写文化水平');
     	    }
			$data['working_age'] = htmlspecialchars($data['working_age']);
			if(empty($data['working_age'])) {
            	$this->tuError('请您填写工作年限');
     	    }
			$data['household'] = htmlspecialchars($data['household']);
			$data['mandarin'] = (int)($data['mandarin']);
			//新加入结束
			$data['mobile'] = htmlspecialchars($data['mobile']);
			if (empty($data['mobile'])) {
            	$this->tuError('请您手机号码');
     	    }
			if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->tuError('联系电话格式不正确');
            }
			$data['is_recommend'] = (int)$data['is_recommend'];
			
			$data['intro'] = htmlspecialchars($data['intro']);//标题名字
			if(empty($data['intro'])) {
            	$this->tuError('请您填技师风采');
     	    }
			if($words = D('Sensitive')->checkWords($data['intro'])) {
				$this->tuError('技师风采含有敏感词：' . $words);
			}
			
			
			$data['skill'] = htmlspecialchars($data['skill']);//标题名字
			if(empty($data['skill'])) {
            	$this->tuError('请您填写技能简介');
     	    }
			if($words = D('Sensitive')->checkWords($data['skill'])) {
				$this->tuError('技能简介含有敏感词：' . $words);
			}
			$data['content'] = htmlspecialchars($data['content']);//标题名字
			if(empty($data['content'])){
            	$this->tuError('请您填写简介');
     	    }
			if($words = D('Sensitive')->checkWords($data['content'])) {
				$this->tuError('简介含有敏感词：' . $words);
			}
			$data['audit'] = 1;
			$data['create_time'] = NOW_TIME;
        	$data['create_ip'] = get_client_ip();
        	return $data;
    }
	

	
   
	//删除技师
	 public function worker_delete($appoint_id = 0,$worker_id = 0){
		  	$appoint_id = (int) $appoint_id;
            $worker_id = (int) $worker_id;
			$obj = D('Appointworker');
			if(!empty($worker_id)){
				if(!$detail = $obj->find($worker_id)){
					$this->tuError('内容不存在');
				}
				if($detail['closed'] != 0) {
					$this->tuError('技师已被删除');
				}
				$obj->save(array('worker_id' => $worker_id, 'closed' => 1));
				$this->tuSuccess('操作成功', U('appoint/worker',array('appoint_id'=>$appoint_id)));
			 }else{
				$this->tuError('操作错误');	
			}
	 }
	 
	//审核技师
	public function worker_audit($appoint_id = 0,$cert_id = 0){
		$appoint_id = (int) $appoint_id;
		$obj = D('Appointworker');
        if(is_numeric($worker_id) && ($worker_id = (int) $worker_id)){
			if($obj->save(array('worker_id' =>$worker_id,'audit' =>1))){
				$this->tuSuccess('操作成功', U('appoint/worker',array('appoint_id'=>$appoint_id)));
			}else{
				$this->tuError('操作错误');	
			}
        }else{
            $worker_id = $this->_post('worker_id', false);
            if(is_array($worker_id)){
                foreach ($worker_id as $id){
                    $obj->save(array('worker_id' =>$id,'audit' => 1));
                }
                $this->tuSuccess('操作成功', U('appoint/worker',array('appoint_id'=>$appoint_id)));
            }
            $this->tuError('请选择要批量审核的项目');
        }
    }
	 
	 
	 //证书列表，不做分页
	public function worker_cert($appoint_id = 0,$worker_id = 0) {
		
		$appoint_id = (int) $appoint_id;
		
        if($worker_id = (int) $worker_id){
			if(!$worker = D('Appointworker')->find($worker_id)){
				$this->error('技师不存在');
			}
            $obj = D('AppointCert');
			$map = array('closed' => 0);
			$map = array('worker_id' => $worker_id);
			$keyword = $this->_param('keyword', 'htmlspecialchars');
			if($keyword){
				$map['name'] = array('LIKE', '%' . $keyword . '%');
				$this->assign('keyword', $keyword);
			}
			if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))){
				$bg_time = strtotime($bg_date);
				$end_time = strtotime($end_date);
				$map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
				$this->assign('bg_date', $bg_date);
				$this->assign('end_date', $end_date);
			}else{
				if($bg_date = $this->_param('bg_date', 'htmlspecialchars')){
					$bg_time = strtotime($bg_date);
					$this->assign('bg_date', $bg_date);
					$map['create_time'] = array('EGT', $bg_time);
				}
				if($end_date = $this->_param('end_date', 'htmlspecialchars')){
					$end_time = strtotime($end_date);
					$this->assign('end_date', $end_date);
					$map['create_time'] = array('ELT', $end_time);
				}
			}
			if(isset($_GET['cert']) || isset($_POST['cert'])){
				$cert = (int) $this->_param('cert');
				if($cert != 999){
					$map['cert'] = $cert;
				}
				$this->assign('cert', $cert);
			}else{
				$this->assign('cert', 999);
			}
			$this->assign('list', $list = $obj->where($map)->select());
			$this->assign('appoint', $appoint = D('Appoint')->find($appoint_id));
			$this->assign('worker', $worker);
			$this->display();
        }else{
            $this->error('请选择要设置的技师');
        }
    }
	
    //添加证书
	public function worker_cert_create($appoint_id = 0,$worker_id = 0){
		$appoint_id = (int) $appoint_id;
		$worker_id = (int) $worker_id;
		
        if($worker_id){
			if(!$appoint = D('Appoint')->find($appoint_id)){
				$this->tuError('家政项目不存在');
			}
			if($appoint['closed'] != 0) {
                $this->error('该家政已被删除');
            }
			if(!$worker = D('Appointworker')->find($worker_id)){
				$this->tuError('技师不存在');
			}
			if($this->_post()){
				$data = $this->worker_cert_editCheck();
				$data['appoint_id'] = $appoint_id;
				$data['worker_id'] = $worker_id;
				if(D('AppointCert')->add($data)) {
					$this->tuSuccess('操作成功', U('appoint/worker_cert',array('worker_id'=>$data['worker_id'])));
				}
				$this->tuError('操作失败');
			}else{
				$this->assign('appoint', $appoint);
				$this->assign('worker', $worker);
				$this->display();
			}
		}else{
            $this->error('请选择要设置的技师');
        }
	}
	

	
	
    //编辑证书
	public function worker_cert_edit($appoint_id = 0,$worker_id = 0,$cert_id = 0){
		
		$appoint_id = (int) $appoint_id;
		$worker_id = (int) $worker_id;
		$obj = D('AppointCert');
		
        if($cert_id = (int) $cert_id){
			if(!$appoint = D('Appoint')->find($appoint_id)){
				$this->tuError('家政项目不存在');
			}
			if($appoint['closed'] != 0) {
                $this->error('该家政已被删除');
            }
			if(!$worker = D('Appointworker')->find($worker_id)){
				$this->tuError('技师不存在');
			}
			if(!$detail = $obj->find($cert_id)){
				$this->tuError('证书不存在');
			}
			if($detail['closed'] != 0){
				$this->tuError('证书已删除');
			}
			if ($this->isPost()){
                $data = $this->worker_cert_editCheck();
				$data['worker_id'] = $worker_id;
				$data['appoint_id'] = $appoint_id;
				$data['cert_id'] = $cert_id;
				if(false !== $obj->save($data)){
					$this->tuSuccess('操作成功', U('appoint/worker_cert',array('worker_id'=>$data['worker_id'])));
				}else{
					$this->tuError('操作失败');
				} 
            }else{
                $this->assign('appoint', $appoint);
				$this->assign('worker', $worker);
				$this->assign('detail', $detail);
				$this->display();
            }
		}else{
            $this->tuError('修改的内容不存在');
        }
	}
	
	
	 private function worker_cert_editCheck(){
			$data = $this->checkFields($this->_post('data', false), $this->worker_cert_edit_fields);
			$data['name'] = htmlspecialchars($data['name']);
			if(empty($data['name'])){
            	$this->tuError('证书名称不能为空');
     	    }
			$data['cert'] = (int)$data['cert'];
			if(empty($data['cert'])){
				$this->tuError('证书类型不能为空');
			}
			$data['photo']  = htmlspecialchars($data['photo']);
			if(empty($data['photo'])){
            	$this->tuError('请您证书图片');
            }
			$data['intro'] = SecurityEditorHtml($data['intro']);
			if(empty($data['intro'])) {
            $this->tuError('证书内容不能为空');
			}
			if($words = D('Sensitive')->checkWords($data['intro'])){
				$this->tuError('证书内容含有敏感词：' . $words);
			}
			$data['is_show'] = (int)$data['is_show'];
			$data['create_time'] = NOW_TIME;
       		$data['create_ip'] = get_client_ip();
        	return $data;
    }
	
   
   
   
	 //删除证书
	 public function worker_cert_delete($worker_id = 0,$cert_id = 0){
			$worker_id = (int) $worker_id;
            $cert_id = (int) $cert_id;
			if(!empty($cert_id)){
				$obj = D('AppointCert');
				if(!$detail = $obj->find($cert_id)){
					$this->tuError('内容不存在');
				}
				if($obj->save(array('cert_id' =>$cert_id,'closed' =>1))){
					$this->tuSuccess('操作成功', U('appoint/worker_cert',array('worker_id'=>$worker_id)));
				}else{
					$this->tuError('操作错误');	
				}				
			 }else{
				$this->tuError('操作错误');	
			}
	 }
	 
	//审核证书
	public function worker_cert_audit($worker_id = 0,$cert_id = 0){
		$worker_id = (int) $worker_id;
		$obj = D('AppointCert');
        if(is_numeric($cert_id) && ($cert_id = (int) $cert_id)){
			if($obj->save(array('cert_id' =>$cert_id,'audit' =>1))){
				$this->tuSuccess('审核成功', U('appoint/worker_cert',array('worker_id'=>$worker_id)));
			}else{
				$this->tuError('操作错误');	
			}
        }else{
            $cert_id = $this->_post('cert_id', false);
            if(is_array($cert_id)){
                foreach ($cert_id as $id){
                    $obj->save(array('cert_id' =>$id,'audit' => 1));
                }
                $this->tuSuccess('批量审核成功', U('appoint/worker_cert',array('worker_id'=>$worker_id)));
            }
            $this->tuError('请选择要批量审核的项目');
        }
    }
	 
	 
	 
}