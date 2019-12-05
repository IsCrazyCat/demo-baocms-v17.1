<?php

class GoodscateModel extends CommonModel {
    protected $pk = 'cate_id';
    protected $tableName = 'goods_cate';
    protected $token = 'goods_cate';
    protected $orderby = array('orderby' => 'asc');

	
    public function getCommIds(){
        return array(
			'27' => array('name' => '粮油类', 'buyNumber'=>4,'sellNumber'=>27),
			'28' => array('name' => '生鲜食品类区', 'buyNumber'=>5,'sellNumber'=>28), 
			'29' => array('name' => '干货类', 'buyNumber'=>6,'sellNumber'=>29),
			'30' => array('name' => '休闲食品类', 'buyNumber'=>8,'sellNumber'=>30),
			'32' => array('name' => '烟酒类', 'buyNumber'=>9,'sellNumber'=>32),
			'33' => array('name' => '服装、鞋帽、针纺织品类','buyNumber'=>10,'sellNumber'=>33),
			'34' => array('name' => '化妆品类', 'buyNumber'=>11,'sellNumber'=>34),
			'35' => array('name' => '金银珠宝类', 'buyNumber'=>12,'sellNumber'=>35),
			'36' => array('name' => '日用品类', 'buyNumber'=>13,'sellNumber'=>36),
			'37' => array('name' => '家用电器和音像器材类', 'buyNumber'=>14,'sellNumber'=>37),
			'38' => array('name' => '中西药品类', 'buyNumber'=>15,'sellNumber'=>38),
			'39' => array('name' => '文化办公用品类', 'buyNumber'=>16,'sellNumber'=>39),
			'40' => array('name' => '家具类', 'buyNumber'=>17,'sellNumber'=>40),
			'41' => array('name' => '通讯器材类', 'buyNumber'=>18,'sellNumber'=>41),
			'42' => array('name' => '建筑及装潢材料类', 'buyNumber'=>19,'sellNumber'=>42),
			'43' => array('name' => '农用生产资料类', 'buyNumber'=>20,'sellNumber'=>43),
			'44' => array('name' => '化工产品类', 'buyNumber'=>21,'sellNumber'=>44),
			'45' => array('name' => '机电类', 'buyNumber'=>22,'sellNumber'=>45),
			'46' => array('name' => '木材类', 'buyNumber'=>23,'sellNumber'=>46),
		);
    }
	
	//获取名称
	public function getCommIdName($commId){
		$list = $this->getCommIds();
	    foreach($list as $k =>$val){
           if($val['sellNumber'] == $commId){
              return $val['name'];
           }
        }
        return  '';
    }

    public function getParentsId($id){
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
	//统计数量
	public function getCateGoodsNum($cate_id){
            if($cate_id){
                $catids = $this->getChildren($cate_id);
                if(!empty($catids)){
                    return D('Goods')->where(array('cate_id' => array('IN', $catids),'audit'=>1,'closed'=>'0'))->count();
                }else{
                    return D('Goods')->where(array('cate_id' =>$cate_id,'audit' =>1,'closed'=>'0'))->count();
                }
				return false;
            }
			return false;
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
	
	public function check_cate_id_goods($cate_id){
        $res = D('Goods')->where(array('cate_id'=>$cate_id,'closed'=>'0'))->find();
		if($res){
		 	$this->error = '商品【'.$res['title'].'】正在使用当前分类，暂时无法删除';
			return false;
		}
        return true;
    }
}