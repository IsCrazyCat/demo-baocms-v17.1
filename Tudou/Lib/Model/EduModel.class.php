<?php

class EduModel extends CommonModel{
    protected $pk   = 'edu_id';
    protected $tableName =  'edu';
	//年龄
    public function getEduAge() {
        return array(
            '1' => '学前',
            '2' => '幼儿',
            '3' => '小学',
            '4' => '初中',
            '5' => '高中',
            '6' => '大学',
            '7' => '成人',
        );
    }
    //时间
    public function getEduTime() {
        return array(
            '1' => '全日制',
            '2' => '白天班',
            '3' => '晚班',
            '4' => '周末班',
            '5' => '寒假班',
            '6' => '随到随学',
        );
    }
    //类型
    public function getEduClass() {
        return array(
            '1' => '小班（2-5人）',
            '2' => '大班（5人）',
            '3' => '一对一',
        );
    }
    
    public function getDays() {
        return array(
            '1' => '2天',
            '2' => '3天',
            '3' => '5天',
            '4' => '7天',
        );
    }
    
    public function getid($shop_id,$type) {
        if($type == 1 || !$type){
            $rs = D('Edugroupattr');  //适合人群
        }else{
            $rs = D('Eduplayattr');   //能玩什么
        }
        if(!$shop_id){
            return false;
        }else if($res = $rs->where(array('shop_id'=>$shop_id))->select()){
            $Arrays = array();
            foreach($res as $k => $v){
                $Arrays[] = $v['attr_id'];
            }
            return $Arrays;
        }else{
            return false;
        }
    }
	//获取教育频道的ID
	 public function get_edu_id($shop_id){
		$map = array('shop_id'=>$shop_id,'audit'=>1,'closed'=>0);
        $detail = $this->where($map)->find();
		$count = $this->where($map)->count();
		if($count == 1){
			return $detail['edu_id'];
		}else{
			return false;
		}
    } 
	
    public function getPics($comment_id){
        $comment_id = (int)$comment_id;
        return $this->where(array('comment_id'=>$comment_id))->select();
    } 
}