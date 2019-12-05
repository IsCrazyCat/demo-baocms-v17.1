<?php
class KtvModel extends CommonModel{
    protected $pk   = 'ktv_id';
    protected $tableName =  'ktv';
	
	public function getError() {
        return $this->error;
    }
    
	public function getKtvDate(){
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
	
	//返回当前日期
	public function get_date_id_unset($date_ids,$date_id){
        $explode_date_id = explode(',', $date_ids);
		$b = in_array($date_id,$explode_date_id);
        return $b;
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
}