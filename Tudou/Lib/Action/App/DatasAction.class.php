<?php

class  DatasAction extends  CommonAction{
    

    public function cityareas(){
        $data = array();
        $data['city']       = D('City')->fetchAll();
        $data['area']       = D('Area')->fetchAll();
        $data['status'] = self::TU_REQUEST_SUCCESS;
        echo json_encode($data);
        die;
    }
	
	public  function cityarea(){
        $data = array();
        $data['city'] = D('City')->where(array('is_open'=>'1'))->fetchAll();
        $data['area'] = D('Area')->fetchAll();
        header("Content-Type:application/javascript");
        echo   'var  cityareas = '.  json_encode($data);die;
    }
    
    public function cab() { //城市地区商圈
        $name = htmlspecialchars($_GET['name']);
        $data = array();
        $data['city']       = D('City')->fetchAll();
        $data['area']       = D('Area')->fetchAll();
        $data['business']   = D('Business')->fetchAll();
        header("Content-Type:application/javascript");
        echo  'var '.$name.'='.  json_encode($data).';';
        die;
    }
	
	public function stock() { 
        $name = htmlspecialchars($_GET['name']);
        $data = array();
        $data['team']      = D('Stockteam') -> select();
        $data['jury']      = D('Stockjury') -> select();
        $data['group']     = D('Stockgroup') -> select();
        header("Content-Type:application/javascript");
        echo  'var '.$name.'='.  json_encode($data).';';
        die;
    }
	
	public  function teamjury(){
        $data = array();
        $data['team']      = D('Stockteam') -> select();
        $data['jury']      = D('Stockjury') -> select();
        header("Content-Type:application/javascript");
        echo   'var  teamjurys = '.  json_encode($data);die;
    }
	
    public function tuancata()
    {
        $city_id    = $this->_param('city_id');
        $tuan_cata  = D('TuanCate')->fetchAll();
        $_cata       = array();
        foreach($tuan_cata as $cata){
            $_cata[$cata['cate_id']] = $cata['cate_name'];
        }
        $this->stringify(array('status'=>self::TU_REQUEST_SUCCESS,'tuan'=>$_cata));
    }

	/*三级联动start*/
	public function onecity() { //城市
        $name = htmlspecialchars($_GET['name']);
        $data = array();
        $data['city'] = D('City')->where(array('is_open'=>'1'))->select();
        header("Content-Type:application/javascript");
        echo  'var '.$name.'='.  json_encode($data).';';
        die;
    }
	public function twoarea() { //地区
        $cid =  $_POST['cid'];
        $data = array();
		$data  = D('Area')->where(array('city_id'=>$cid))->select();
        echo json_encode($data);
        die;
    }

   public function tbusiness() { //商圈
        $bid =  $_POST['bid'];
        $data = array();
		$data  = D('Business')->where(array('area_id'=>$bid))->select();
        echo json_encode($data);
        die;
    }
    /*三级联动end*/
	public function cab_app() { //城市地区商圈
        $name = htmlspecialchars($this->_param('name'));
        $data = array();
        $data['city']       = D('City')->fetchAll();
        $data['area']       = D('Area')->fetchAll();
        $data['business']   = D('Business')->fetchAll();
        //header("Content-Type:application/javascript");
		$data = array('status'=>self::TU_REQUEST_SUCCESS,'cityareas'=>$data);
        $this->stringify($data);
    }
    
	public function cates(){ //店铺团购商品
		$data = array();
		$data['shopcates'] = D('Shopcate')->fetchAll();
		$data['tuancates'] = D('Tuancate')->fetchAll();
		$data['goodscates'] = D('Goodscate')->fetchAll();
        $data['status'] = self::TU_REQUEST_SUCCESS;
        echo json_encode($data);
        die;
	}
	//获取全站的地址列表
	public function city() {
		$upid = isset($_GET['upid']) ? intval($_GET['upid']) : 0;
		$callback = $_GET['callback'];
		$outArr = array();
		$cityList = D('Paddlist') -> where(array('upid' => $upid)) -> select();
		if (is_array($cityList) && !empty($cityList)) {
			foreach ($cityList as $key => $value) {
				$outArr[$key]['id'] = $value['id'];
				$outArr[$key]['name'] = $value['name'];
			}
		}
		$outStr = '';
		$outStr = json_encode($outArr);
		if ($callback) {
			$outStr = $callback . "(" . $outStr . ")";
		}
		echo $outStr;
		die();
	}
	//获取商家分类
	public function shopcate($parent_id = 0){
        $datas = D('Shopcate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                    }
                }
            }
        }
        echo $str;
        die;
    }
    
}