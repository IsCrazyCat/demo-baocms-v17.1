<?php
class AdAction extends CommonAction {
	
	
	
	  //新版点击广告
      public function click(){
		  
		$user_id = $this->uid;
      	$ad_id = $this->_get('ad_id','intval');
		$config = D('Setting')->fetchAll();//全局
		$ad_consume = $config['ad']['ad_consume'] ? (int)$config['ad']['ad_consume'] : '1';
		$ad_user = $config['ad']['ad_user'] ? (int)$config['ad']['ad_user'] : '1';
		$ad_chief = $config['ad']['ad_chief'] ? (int)$config['ad']['ad_chief'] : '1';
		
		
		$Adrecord = D('Adrecord')->where(array('ad_id'=>$ad_id))->find();
		
  
        //广告位已被购买
        if($Adrecord['user_id'] && ($Adrecord['closed'] == 0)){
           if($Adrecord['prestore_integral'] == 0){
               M('Ad')->where(array('ad_id'=>$ad_id))->save(array('closed'=>'1')); //删除广告
          }else{
         	   //检查积分是否充足
               if($Adrecord['prestore_integral'] < $ad_consume){
                     D('Ad')->where(array('ad_id'=>$ad_id))->save(array('closed'=>'1','prestore_integral'=>'0')); //积分消耗完，自动下架广告位
                     D('Users')->addIntegral($Adrecord['user_id'],$Adrecord['prestore_integral'],'积分不足，退还用户剩余积分，下架广告位');//返回积分
                  }else{
					 //如果没有存cookie
                     if(!cookie('adinfo_'.$ad_id)){
                          if($user_id){
								D('Ad')->where(array('ad_id'=>$ad_id))->setDec('prestore_integral',$ad_consume); //扣除商家广告位积分，反用户和推荐人积分
								if($Adrecord['user_id'] != $user_id && $user_id != '' && $user_id != '0'){
									$Users = D('Users')->where(array('user_id'=>$user_id))->find();
									$intro2 = '用户【'.$Users['nickname'].'】广告点击返还用户积分';
									D('Users')->addIntegral($user_id,$ad_user,$intro2);
									if($Users['fuid1'] != $Adrecord['user_id']){
									   $fuid1 = D('Users')->where(array('user_id'=>$Users['fuid1']))->find();
									   $intro3 = '用户【'.$Users['nickname'].'】广告点击返还推荐人【'.$fuid1['nickname'].'】积分【'.$ad_chief.'】';
									   D('Users')->addIntegral($Users['fuid1'],$ad_chief,$intro3);//反推荐人积分
									}  
									$intro =  $intro.'---'.$intro2;
							    }else{
									$intro = '广告购买商【'.$this->member['nickname'].'】点击扣除积分【'.$ad_consume.'】';
								}
						  }else{
							  D('Ad')->where(array('ad_id'=>$ad_id))->setDec('prestore_integral',$ad_consume); //游客点击只消耗商家广告位积分
							  $intro = '游客点击【'.$Adrecord['title'].'】扣除积分【'.$ad_consume.'】';
						  } 
					  
					 $arr = array();
					 $arr['city_id'] = $this->city_id; 
					 $arr['site_id'] = $Adrecord['site_id']; 
					 $arr['id'] = $Adrecord['id'];
					 $arr['user_id'] = $user_id ? $user_id : '0'; 
					 $arr['nickname'] = $this->member['nickname'] ? $this->member['nickname'] : '游客点击'; 
					 $arr['title'] = $Adrecord['title']; 
					 $arr['integral'] = $ad_consume; 
					 $arr['link_url'] = $Adrecord['link_url']; 
					 $arr['intro'] = $intro;
					 $arr['create_time'] = NOW_TIME;
        			 $arr['create_ip'] = get_client_ip();
					 D('AdRecordLogs')->add($arr); //用户购买的广告点击日志
					 
			
                     D('Ad')->where(array('ad_id'=>$ad_id))->setInc('click',1); 
                     $Ad = D('Ad')->where(array('ad_id'=>$ad_id))->find();
                     if($Ad['prestore_integral'] == 0){
                       D('Ad')->where(array('ad_id'=>$ad_id))->save(array('closed'=>'1')); 
                     }
                     cookie('adinfo_'.$ad_id,$ad_id,24*3600);
                   }
                }

            }

       }
	   
      $result = D('Ad')->where(array('ad_id'=>$ad_id))->find();
	  if($result['link_url']){
		header("location:".$result['link_url']);  
	  }else{
		 header("location:".U('index/index'));   
	  }
  }

}

