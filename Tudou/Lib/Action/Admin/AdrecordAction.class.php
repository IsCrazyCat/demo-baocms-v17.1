<?php
class AdrecordAction extends CommonAction {

    private $create_fields = array('user_id', 'title','prestore_integral','city_id', 'active_time', 'prestore_integral','link_url','photo','site_id', 'ad_id', 'status');
    private $edit_fields = array('user_id', 'title','prestore_integral','city_id', 'active_time', 'prestore_integral','link_url','photo', 'site_id', 'ad_id', 'status');

     public function _initialize() {
        parent::_initialize();
        $this->citys = D('City')->fetchAll();
        $this->assign('citys', $this->citys);
    }
    
    public function index() {
        $Adrecord = M('adRecord');
        import('ORG.Util.Page'); 
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword',$keyword);
        }
        if ($id = (int) $this->_param('id')) {
            $map['id'] = $id;
            $this->assign('id', $id);
        }
        $user = D('Users');  
        $count = $Adrecord->where($map)->count(); 
 
        $Page = new Page($count,15); 
        $show = $Page->show(); 
        $list = $Adrecord->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
          
         foreach($list as $k=>$v){
         	    $u['user_id'] = $v['user_id'];
                $userinfo = $user->where($u)->find();
 
                $list[$k]['nickname'] = $userinfo['nickname'];
          }                                  
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->assign('sites', D('Adsite')->fetchAll());
        $this->assign('types', D('Adsite')->getType());
        $this->display(); 
    }

   
    //审核申请
    public function audit($id = 0){  
	
        if(is_numeric($id) && ($id = (int) $id)){
			
			 $adrecord = D('Adrecord')->find($id); 
			
			 if(D('Ad')->where(array('site_id'=>$adrecord['site_id'],'city_id'=>$adrecord['city_id'],'user_id'=>$adrecord['user_id']))->find()){
				$this->tuError('该位置广告已存在');
			 }
			 
			 
			 if(D('Ad')->where(array('site_id'=>$adrecord['site_id'],'city_id'=>$adrecord['city_id']))->count() >=10){
				$this->tuError('同一位置，轮播图数量超过10个'); 
			 }
            
     
            $Users = D('Users')->where(array(''=>$user_id['user_id']))->select();  
			
            if($Users[0]['integral'] < 0 || $Users[0]['integral'] < $adrecord['prestore_integral']){
              $this->tuError('该用户积分不足');
            }
			
            $intro = '广告位展示申请成功，扣除预付【'.$adrecord['prestore_integral'].'】积分';   
			            
            D('Users')->addIntegral($adrecord['user_id'],'-'.$adrecord['prestore_integral'], $intro);//扣除积分
			
            $arr['title']   = $adrecord['title'];
            $arr['site_id']   = $adrecord['site_id'];
            $arr['city_id'] = $adrecord['city_id'];
            $arr['site_id'] = $adrecord['site_id'];
            $arr['link_url'] = $adrecord['link_url'];
            $arr['photo'] = $adrecord['photo'];  
            $arr['user_id'] = $adrecord['user_id'];
            $arr['prestore_integral'] = $adrecord['prestore_integral'];
            $arr['bg_date'] =  date('Y-m-d',time());
            $arr['end_date'] =  date('Y-m-d',strtotime('+'.$adrecord['active_time'].'day',time()));//购买过后的到期时间 $size
			
			if($ad_id = D('ad')->add($arr)){
				D('Adrecord')->where(array('id'=>$id))->save(array('ad_id' =>$ad_id,'audit' => 1));//改变状态
				$this->tuSuccess($intro, U('adrecord/index'));
			}else{
				$this->tuError('审核失败');
			}
        }else{
            $this->tuError('ID不存在');
        }
		
		
	 }
	
	//拒绝审核
	public function fail($id = 0){
      if(is_numeric($id) && ($id = (int) $id)){
		 	if(D('Adrecord')->where(array('id'=>$id))->save(array('audit'=>'-1'))){
				$this->tuSuccess('操作成功', U('adrecord/index'));
			}else{
				 $this->tuError('操作失败');
			}
        }

	}
	
	
    public function edit($id = 0){
        if($id = (int) $id){
           $obj = D('Adrecord');
            if(!$detail = $obj->find($id)){
                $this->tuError('请选择要编辑的广告记录');
            }
            if($this->isPost()){
                $data = $this->editCheck();
                $data['id'] = $id;
                if(false !== $obj->save($data)){
                    $this->tuSuccess('操作成功', U('adrecord/index',array('id'=>$data['id'])));
                }
                $this->tuError('操作失败');
            }else{
             	$adrecordinfo = $obj->where(array('id'=>$id))->find();
                $userinfo  = D('Users')->where(array('user_id' => $adrecordinfo['user_id']))->find();
                $this->assign('nickname',$userinfo['nickname']);
                $this->assign('detail', $detail);
                $this->assign('sites', D('Adsite')->fetchAll());
                $this->assign('types', D('Adsite')->getType());
                $this->display();
            }
        }else{
            $this->tuError('请选择要编辑的广告');
        }
    }


    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['site_id'] = (int) $data['site_id'];
        if(empty($data['site_id'])){
            $this->tuError('所属广告位不能为空');
        } 
        if(!empty($data['photo']) && !isImage($data['photo'])){
            $this->tuError('广告图片格式不正确');
        } 
		$data['code'] = $data['code'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['active_time'])) {
            $this->tuError('有效时间不能为空');
        }
        $data['link_url'] = htmlspecialchars($data['link_url']);
        $data['photo'] = htmlspecialchars($data['photo']);       
        $data['user_id'] = (int) $data['user_id'];
        $data['city_id'] = (int) $data['city_id'];
        $data['active_time'] = (int) $data['active_time'];
        return $data;
    }

    public function delete($id = 0){
        if(is_numeric($id) && ($id = (int) $id)){
			if(!$detail = D('Adrecord')->find($id)){
				$this->tuError('不存在');
			}
			if(D('Adrecord')->where(array('id' => $id))->delete()){
				if($detail['ad_id']){
					D('Ad')->where(array('ad_id'=>$detail['ad_id']))->delete();
				}
				$this->tuSuccess('删除成功', U('adrecord/index'));
			}
            $this->tuError('删除失败');
        }else{
            $this->tuError('暂时不支持批量删除');
        }
    }
	
	
	
	public function logs() {
        $obj = D('AdRecordLogs');
        import('ORG.Util.Page'); 
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if($keyword){
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword',$keyword);
        }
		
		if($user_id = (int) $this->_param('user_id')){
            $map['user_id'] = $user_id;
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
        }
        if(($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
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
        if($id = (int) $this->_param('id')){
            $map['id'] = $id;
            $this->assign('id', $id);
        }
		if($city_id = (int) $this->_param('city_id')){
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
		if($site_id = (int) $this->_param('site_id')){
            $map['site_id'] = $site_id;
            $this->assign('site_id', $site_id);
        }
        $count = $obj->where($map)->count(); 
        $Page = new Page($count,15); 
        $show = $Page->show(); 
        $list = $obj->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->assign('sites', D('Adsite')->fetchAll());
        $this->assign('types', D('Adsite')->getType());
        $this->display(); 
    }
	
	
	public function logs_delete($log_id = 0){
        if(is_numeric($log_id) && ($log_id = (int) $log_id)){
            $obj = D('AdRecordLogs');
			$res = $obj->find($log_id);
            $obj->delete($log_id);
            $this->tuSuccess('删除成功', U('adrecord/logs',array('id'=>$res['id'])));
        }else{
            $log_id = $this->_post('log_id', false);
            if(is_array($log_id)){
                $obj = D('AdRecordLogs');
                foreach($log_id as $id){
                    $obj->delete($id);
                }
                $this->tuSuccess('批量批量删除成功', U('adrecord/logs'));
            }
            $this->tuError('非法操作');
        }
    }
	
	

}
