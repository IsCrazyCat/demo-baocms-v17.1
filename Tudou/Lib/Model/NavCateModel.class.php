<?php
class NavCateModel extends CommonModel{
    protected $pk   = 'cate_id';
    protected $tableName =  'nav_cate';
    protected $token = 'nav_cate';
    protected $orderby = array('orderby'=>'asc');
	
	
	public function getType(){
       return  array(
			1 => '电脑版',
			2 => '手机版',
	   );
    }
	
	
	
    
    public function  getParentsId($id){
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        $parent_id2 = $data[$parent_id]['parent_id'];
        if($parent_id2 == 0) return $parent_id;
        return  $parent_id2;
    }
      public function getChildren($id){
        $local = array();
        //循环两层即可了 最高3级分类
        $data = $this->fetchAll();
        foreach($data  as $val){
            if($val['parent_id'] == $id){
                $child = true;
                foreach($data as  $val1){
                    if($val1['parent_id'] == $val['cate_id']){
                        $child = FALSE;
                        $local[]=$val1['cate_id'];
                    }
                }
                if($child){
                    $local[]=$val['cate_id'];
                }
            }         
        }
        return $local;
    }
  
  
  public function check_parent_id($cate_id){
		$detail = $this->where(array('cate_id'=>$cate_id))->find();
		if($detail){ 
		
		 
			$res = $this->where(array('parent_id'=>$cate_id))->find();
			if($res){
		 		$this->error = '当前分类下面有子菜单【'.$res['cate_name'].'】ID【'.$res['cate_id'].'】，请先删除子菜单后再来重试，谢谢';
				return false;
			}else{
				return true;
			}
			
			
			
		}else{
			$this->error = '您准备删除的分类没找到对应的信息';
			return false;
		}  
    }
   
}