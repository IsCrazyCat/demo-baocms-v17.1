<?php
class UsersauxAction extends CommonAction{
    private $create_fields = array('user_id','city_id', 'area_id', 'business_id','team_id', 'jury_id', 'group_id', 'card_photo', 'name', 'mobile','card_id','addr_str', 'addr_info', 'guarantor_name', 'guarantor_mobile');
	private $edit_fields = array('user_id','city_id', 'area_id', 'business_id', 'team_id', 'jury_id', 'group_id','card_photo', 'name', 'mobile','card_id','addr_str', 'addr_info', 'guarantor_name', 'guarantor_mobile');
    public function index(){
        if (empty($this->uid)) {
            header("Location:" . U('passport/login'));
            die;
        }
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Usersaux');
            if ($user_id = $obj->add($data)) {
                $this->tuMsg('申请实名认证成功', U('usersaux/index'));
            }else{
				$this->tuMsg('申请失败');
			}
        } else {
			$this->assign('detail',$detail = D('Usersaux')->find($this->uid));
			$this->assign('citys', D('City')->fetchAll());
            $this->assign('business', D('Business')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['user_id'] = $this->uid;
		$data['guide_id'] = (int) $data['guide_id'];
        $data['card_photo'] = htmlspecialchars($data['card_photo']);
        if (empty($data['card_photo'])) {
            $this->tuMsg('请上传身份证');
        }
        if (!isImage($data['card_photo'])) {
            $this->tuMsg('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuMsg('真实名字不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuMsg('手机号不能为空');
        }
		$data['card_id'] = (int) $data['card_id'];
        if (empty($data['card_id'])) {
            $this->tuMsg('身份证号码不能为空');
        }
	
		if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->tuMsg('手机号码格式不正确');
        }
		
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->tuMsg('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->tuMsg('地区不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->tuMsg('商圈不能为空');
        }
		
		
		$data['team_id'] = (int) $data['team_id'];
        if (empty($data['team_id'])) {
            $this->tuMsg('队伍不能为空');
        }
        $data['jury_id'] = (int) $data['jury_id'];
        if (empty($data['jury_id'])) {
            $this->tuMsg('团队不能为空');
        }
        $data['group_id'] = (int) $data['group_id'];
        if (empty($data['group_id'])) {
            $this->tuMsg('群不能为空');
        }
		
		
		$city = D('City')->find($data['city_id']);
		$area = D('Area')->find($data['area_id']);
		$Busines = D('Business')->find($data['business_id']);
		$data['addr_str'] = $city['name'] . " " . $area['area_name'] . " " . $Busines['business_name'];
        $data['addr_info'] = htmlspecialchars($data['addr_info']);
        if (empty($data['addr_info'])) {
            $this->tuMsg('详细地址不能为空');
        }
		$data['guarantor_name'] = htmlspecialchars($data['guarantor_name']);
        if (empty($data['guarantor_name'])) {
            $this->tuMsg('担保人姓名不能为空');
        }
		$data['guarantor_mobile'] = htmlspecialchars($data['guarantor_mobile']);
        if (empty($data['guarantor_mobile'])) {
            $this->tuMsg('担保人电话不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	//实名编辑
	public function edit($user_id = 0){
        if ($user_id = (int) $user_id) {
            $obj = D('Usersaux');
				if (!($detail = $obj->find($user_id))) {
					$this->error('该认证不存在');
				}
				if ($detail['closed'] != 0) {
					$this->error('该认证已被删除');
				}
				if ($this->isPost()) {
					$data = $this->editCheck();
					if (false !== $obj->save($data)) {
						$this->tuMsg('编辑操作成功', U('usersaux/index'));
					}else{
						$this->tuMsg('操作失败');
					}
					
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('参数错误');
        }
    }
	//编辑
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = $this->uid;
		$data['guide_id'] = (int) $data['guide_id'];
        $data['card_photo'] = htmlspecialchars($data['card_photo']);
        if (empty($data['card_photo'])) {
            $this->tuMsg('请上传身份证');
        }
        if (!isImage($data['card_photo'])) {
            $this->tuMsg('身份证格式不正确');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->tuMsg('真实名字不能为空');
        }
		$data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->tuMsg('手机号不能为空');
        }
		$data['card_id'] = (int) $data['card_id'];
        if (empty($data['card_id'])) {
            $this->tuMsg('身份证号码不能为空');
        }
		
		if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->tuMsg('手机号码格式不正确');
        }
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->tuMsg('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->tuMsg('地区不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->tuMsg('商圈不能为空');
        }
		
		$data['team_id'] = (int) $data['team_id'];
        if (empty($data['team_id'])) {
            $this->tuMsg('队伍不能为空');
        }
        $data['jury_id'] = (int) $data['jury_id'];
        if (empty($data['jury_id'])) {
            $this->tuMsg('团队不能为空');
        }
        $data['group_id'] = (int) $data['group_id'];
        if (empty($data['group_id'])) {
            $this->tuMsg('群不能为空');
        }
		
		$city = D('City')->find($data['city_id']);
		$area = D('Area')->find($data['area_id']);
		$Busines = D('Business')->find($data['business_id']);
		$data['addr_str'] = $city['name'] . " " . $area['area_name'] . " " . $Busines['business_name'];
        $data['addr_info'] = htmlspecialchars($data['addr_info']);
        if (empty($data['addr_info'])) {
            $this->tuMsg('详细地址不能为空');
        }
		$data['guarantor_name'] = htmlspecialchars($data['guarantor_name']);
        if (empty($data['guarantor_name'])) {
            $this->tuMsg('担保人姓名不能为空');
        }
		$data['guarantor_mobile'] = htmlspecialchars($data['guarantor_mobile']);
        if (empty($data['guarantor_mobile'])) {
            $this->tuMsg('担保人电话不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	function is_idcard( $id ) { 
	  $id = strtoupper($id); 
	  $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/"; 
	  $arr_split = array(); 
	  if(!preg_match($regx, $id)) { 
		return FALSE; 
	  } 
	  if(15==strlen($id)) { 
		$regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/"; 
	  
		@preg_match($regx, $id, $arr_split); 
		//检查生日日期是否正确 
		$dtm_birth = "19".$arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4]; 
		if(!strtotime($dtm_birth))  { 
		  return FALSE; 
		} else { 
		  return TRUE; 
		} 
	  } 
	  else { 
		$regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/"; 
		@preg_match($regx, $id, $arr_split); 
		$dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4]; 
		if(!strtotime($dtm_birth)) { 
		  return FALSE; 
		} 
		else{ 
		  //检验18位身份证的校验码是否正确。 
		  //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。 
		  $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); 
		  $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); 
		  $sign = 0; 
		  for ( $i = 0; $i < 17; $i++ ) 
		  { 
			$b = (int) $id{$i}; 
			$w = $arr_int[$i]; 
			$sign += $b * $w; 
		  } 
		  $n = $sign % 11; 
		  $val_num = $arr_ch[$n]; 
		  if ($val_num != substr($id,17, 1)) 
		  { 
			return FALSE; 
		  } 
		  else
		  { 
			return TRUE; 
		  } 
		} 
	  } 
	} 		
}