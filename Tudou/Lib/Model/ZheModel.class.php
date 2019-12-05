<?php
class 	ZheModel extends CommonModel{
    protected $pk = 'zhe_id';
    protected $tableName = 'zhe';
    
    public function getZheWeek(){
        return array(
			'1' => '星期一', 
			'2' => '星期二', 
			'3' => '星期三', 
			'4' => '星期四', 
			'5' => '星期五', 
			'6' => '星期六',
			'7' => '星期日'
		);
    }
	
	public function getZheDate(){
        return array(
			'1' => '1日', 
			'2' => '2日', 
			'3' => '3日', 
			'4' => '4日', 
			'5' => '5日', 
			'6' => '6日',
			'7' => '7日',
			'8' => '8日', 
			'9' => '9日', 
			'10' => '10日', 
			'11' => '11日', 
			'12' => '12日', 
			'13' => '13日',
			'14' => '14日',
			'15' => '15日', 
			'16' => '16日', 
			'17' => '17日', 
			'18' => '18日', 
			'19' => '19日', 
			'20' => '20日',
			'21' => '21日',
			'22' => '22日', 
			'23' => '23日', 
			'24' => '24日', 
			'25' => '25日', 
			'26' => '26日', 
			'27' => '27日',
			'28' => '28日',
			'29' => '29日', 
			'30' => '30日', 
			'31' => '31日', 
		);
    }
	//返回星期几
	public function get_weed_id_unset($week_ids,$week_id){
        $explode_week_id = explode(',', $week_ids);
		$a = in_array($week_id,$explode_week_id);
        return $a;
    }
	
	//手机版返回多少号
	public function get_date_id_unset($date_ids,$date_id){
        $explode_date_id = explode(',', $date_ids);
		$b = in_array($date_id,$explode_date_id);
        return $b;
    }
	
	//返回列表最后一个星级几,返回星期几的数字
	public function get_day_week($zhe_id){
		$detail = $this->find($zhe_id);
        $reset_arrays = explode(',', $detail['week_id']);
        return reset($reset_arrays);
    }
	
	//返回列表最后一个号数,返回号数的数字
	public function get_day_date($zhe_id){
		$detail = $this->find($zhe_id);
        $reset_arrays = explode(',', $detail['date_id']);
        return reset($reset_arrays);
    }
	
	//销毁今天以前的排列
	public function get_day_week_unset($zhe_id){
		$detail = $this->find($zhe_id);
        $reset_arrays = explode(',', $detail['date_id']);
        return reset($reset_arrays);
    }
	
	//返回真假，检测用户是否有五折卡
	public function check_user_zhe($user_id){
        $Zheorder = D('Zheorder')->where(array('user_id'=>$user_id,'status'=>1,'closed'=>0,'end_time' => array('EGT', NOW_TIME)))->find();
		return $Zheorder;
        
    }
	
	
	
    public function CallDataForMat($items){
        if (empty($items)) {
            return array();
        }
        $obj = D('Shop');
        $shop_ids = array();
        foreach ($items as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $shops = $obj->itemsByIds($shop_ids);
        foreach ($items as $k => $val) {
            $val['shop'] = $shops[$val['shop_id']];
            $items[$k] = $val;
        }
        return $items;
    }
}