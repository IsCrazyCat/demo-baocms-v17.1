<?php
class CityAction extends CommonAction{

	protected function _initialize(){
        parent::_initialize();
    }
	
	//保存定位城市
	public function SaveHotCity(){
		//保存定位城市，这里不想去保存啊
		$data=array();
	    $data['user_id']=I('user_id','','trim');
		$data['cityname']=I('cityname','','trim,htmlspecialchars');
		$data['time']=time();
		if(!$res = M('CitySelectLogs')->where(array('cityname'=>$data['cityname'],'user_id'=>$data['user_id']))->find()){
			if(M('city_select_logs')->add($data)){
			 	echo  '1';	
			}else{
				echo  '2';
			}
		}
	}
	
	//获取城市
	public function GetCity(){
	  $res = M('CitySelectLogs')->where(array('user_id'=>$data['user_id']))->find();
	  if(!$res){
		 echo json_encode(1); 
	  }else{
		  echo json_encode($res);
	  }
	}
	
	//获取城市
	public function GetCitys(){
		$arr = M('City')->field('first_letter')->group('first_letter')->select();
		
		$citylists = array();
		foreach($arr as $val){ 
			$a = strtoupper($val['first_letter']);
			$list = M('City')->where(array('first_letter'=>$val['first_letter']))->select();
			$citylists[$a][]['city'] = $list;
			$citylists[$a][]['name'] = $a;
		}	
		//p($citylists);die;
	    echo json_encode($citylists);
	}
	
}