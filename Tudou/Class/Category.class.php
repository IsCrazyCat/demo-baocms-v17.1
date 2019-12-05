<?php
Class Category {
	//组合一位数组
	//通过接点人获得子级
	Static public function getChilds13($us,$uname){
		$arr=array();
		foreach($us as $v){
			if($v['tname']==$uname){
				
				$arr=array_merge($arr,self::getChilds13($us,$v['uname']));
				//$v['children']=self::getChilds13($us,$v['uname']);
				$arr[]=$v;
				
			}
		}
		return $arr;
	}
	Static public function getdai($us,$uname,$slevel=0){
		$arr=array();
		foreach($us as $v){
			if($v['tname']==$uname){
				$v['slevel']=$slevel+1;
				$arr[]=$v;
				$arr=array_merge($arr,self::getdai($us,$v['uname'],$slevel+1));
				
			}
			
		}
		return $arr;
	}
	Static public function unlimitedForlevel($us,$uname,$level=0){
		$arr=array();
		foreach($us as $v){
			if($v['uname']==$uname && $level<2){
				$v['level']=$level+1;
				$arr[]=$v;
				$arr=array_merge($arr,self::unlimitedForlevel($us,$v['jname'],$level+1));
				
			}
			
		}
		return $arr;
	}
	//传递id查询子级
	Static public function getchild($us,$id){
		$arr=array();
		foreach($us as $v){
			if($v['pid']==$id){
				$arr[]=$v;
			
				$arr=array_merge($arr,self::getchild($us,$v['id']));
			}
		}
		return $arr;
	}
	//传递id查询父级
	Static public function getParent($us,$jname){
		$arr=array();
		foreach($us as $v){
			if($v['uname']==$jname){
				$d['uname']=$v['uname'];
				$d['jiandian']=$v['jiandian'];
				$d['jibie']=$v['jibie'];
				$d['treeplace']=$v['treeplace'];
				$arr[]=$d;
				$arr=array_merge($arr,self::getParent($us,$v['jname']));
			}
		}
		return $arr;
	}
	//通过接点人获得子级,查询数组，会员的UID
	Static public function getChilds1($us,$user_id){
		$arr = array();
		foreach($us as $v){
				if($v['fuid1']== $user_id){
					$v['children']=self::getChilds1($us,$v['user_id']);
					$arr[]=$v;
				}
			
		}
		return $arr;
	}
	
	//通过接点人获得子级
	Static public function getChilds11($us,$uname){
		$arr=array();
		foreach($us as $v){
			if($v['uname']==$uname){
				
				//$arr=array_merge($arr,self::getChilds($us,$v['uname']));
				$v['children']=self::getChilds11($us,$v['tname']);
				$arr[]=$v;
				
			}
		}
		return $arr;
	}
	
	
	//通过接点人获得子级
	Static public function getChilds12($us,$uname,$level){
		$arr=array();
		foreach($us as $v){
			if($v['uname']==$uname){
				
				$arr=array_merge($arr,self::getChilds12($us,$v['tname']));
				//$v['children']=self::getChilds12($us,$v['tname']);
				$arr[]=$v;
				
			}
		}
		return $arr;
	}
	
	
	
}

?>