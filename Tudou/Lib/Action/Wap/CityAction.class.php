<?php

class CityAction extends CommonAction{
    
    public function index(){
		if ($keyword2 = $this->_param('keyword2', 'htmlspecialchars')) {
             $map = array('name|pinyin' => array('LIKE', '%' . $keyword2 . '%'));
             $citlist = D('City')->where($map)->select();
			 $this->citys = $citlist;
             $this->assign('keyword2', $keyword2);
        }
		
		$type = $this->_param('type', 'htmlspecialchars');
		$this->assign('type', $type);
		
        $citylists = array(); 
        foreach($this->citys as $val){ 
			 if($val['is_open'] == 1){
				$a = strtoupper($val['first_letter']);
				$citylists[$a][] = $val;
			}
		}	
        ksort($citylists);
        $this->assign('citylists',$citylists);
        $this->display();
    }
	

    
	
	//成功后跳转
	public function goLocationUrl($url,$type){
		$url = cookie('url');
		if($url){
			$res = strpos($url,'login');
			if($res && ($type !='3')){
				cookie('url',null);
				header("Location:".U('index/index'));die;
			}else{
				cookie('url',null);
				header('Location:' .$url);die;
			}
			
		}else{
			header("Location:".U('index/index'));die;
		}
	}
	
	
	
    //城市连接跳转
    public function change($city_id = 0,$type = ''){
        if(empty($city_id)){
            //没有城市ID
			cookie('city_id',$this->_CONFIG['site']['city_id'],86400*30);
			cookie('url',null);
			header("Location:".U('index/index'));die;
        }
		$type = $this->_param('type', 'htmlspecialchars');
		$this->assign('type', $type);
		
        if(isset($this->citys[$city_id])){            
            cookie('city_id',$city_id,86400*30);
			cookie('cityop',1,86400);
			if($type && ($type != 'city')  &&  ($type !='3') && ($type != 'passport')){
				header("Location:".U(''.$type.'/index'));die;
			}else{
				$this->goLocationUrl($url = '',$type);
			}
        }else{
			//城市详情没ID，这样的情况很少
			cookie('city_id',$this->_CONFIG['site']['city_id'],86400*30);
			cookie('url',null);
			header("Location:".U('index/index'));die;
		}
        
    }
    
    
}