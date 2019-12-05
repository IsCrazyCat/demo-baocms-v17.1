<?php
class IndexAction extends CommonAction{
    public function index(){
        $this->display();
    }
    public function main(){
		
		 
        $bg_time = strtotime(TODAY);
        $str_1 = '-1 day';
		$str_2 = '-2 day';
		$str_3 = '-3 day';
		$str_4 = '-4 day';
		$str_5 = '-5 day';
        $bg_time_1 = strtotime(date('Y-m-d', strtotime($str_1)));
		$bg_time_2 = strtotime(date('Y-m-d', strtotime($str_2)));
		$bg_time_3 = strtotime(date('Y-m-d', strtotime($str_3)));
		$bg_time_4 = strtotime(date('Y-m-d', strtotime($str_4)));
		$bg_time_5 = strtotime(date('Y-m-d', strtotime($str_5)));
        
        $this->assign('m',$m = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'shop_id' => $this->shop_id))->sum('money'));
       
       
        $this->assign('m_1',$m_1 = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time), array('EGT', $bg_time_1)), 'shop_id' => $this->shop_id))->sum('money'));
		
		$this->assign('m_2',$m_2 = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time_1), array('EGT', $bg_time_2)), 'shop_id' => $this->shop_id))->sum('money'));
		 
		$this->assign('m_3',$m_3 = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time_2), array('EGT', $bg_time_3)), 'shop_id' => $this->shop_id))->sum('money'));
		  
		$this->assign('m_4',$m_4 = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time_3), array('EGT', $bg_time_4)), 'shop_id' => $this->shop_id))->sum('money'));
		   
	  $this->assign('m_5', $m_5 = (int) D('Shopmoney')->where(array('create_time' => array(array('ELT', $bg_time_4), array('EGT', $bg_time_5)), 'shop_id' => $this->shop_id))->sum('money'));
		
		$this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->display();
    }
	
	//商家导入会员
	public function import() {
		$file = $_FILES['fileName'];
		$max_size = "2000000";
		$fname = $file['name'];
		$ftype = strtolower(substr(strrchr($fname, '.'), 1));
		//文件格式
		$uploadfile = $file['tmp_name'];
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (is_uploaded_file($uploadfile)) {
				if ($file['size'] > $max_size) {
					$this -> tuError('导入的文件太大了');
					exit ;
				}
				if ($ftype == 'xls') {
					Vendor("PHPExcel.PHPExcel");
					$objReader = PHPExcel_IOFactory::createReader('Excel5');
					$objPHPExcel = $objReader -> load($uploadfile);
					$sheet = $objPHPExcel -> getSheet(0);
					$highestRow = $sheet -> getHighestRow();
					$succ_result = 0;
					$error_result = 0;
					for ($j = 2; $j <= $highestRow; $j++) {
						$shop_id = trim($objPHPExcel -> getActiveSheet() -> getCell("A$j") -> getValue());
						$mobile = trim($objPHPExcel -> getActiveSheet() -> getCell("B$j") -> getValue());
						if (!empty($shop_id)) {
							//业务逻辑
						} else {
							if (!empty($orderNo)) {
								$error_result += 1;
							}
						}
					}
					$this -> tuSuccess('导入会员操作成功！成功' . $succ_result . '条，失败' . $error_result . '条', U('porder/plfahuo'));
				} elseif ($ftype == 'csv') {
					if (empty($uploadfile)) {
						echo '请选择要导入的CSV文件！';
						$this -> tuError('请选择要导入的CSV文件');
						exit ;
					}
					$handle = fopen($uploadfile, 'r');
					$n = 0;
					while ($data = fgetcsv($handle, 10000)) {
						$num = count($data);
						for ($i = 0; $i < $num; $i++) {
							$out[$n][$i] = $data[$i];
						}
						$n++;
					}
					$result = $out;
					//解析csv
					$len_result = count($result);
					if ($len_result == 0) {
						$this -> tuError('没有任何数据');
						exit ;
					}
					$succ_result = 0;
					$error_result = 0;
					for ($i = 1; $i < $len_result; $i++) {//循环获取各字段值
						$shop_id = trim(iconv('gb2312', 'utf-8', $result[$i][0]));
						if($shop_id == ''){
							continue;
						}
						$mobile = trim(iconv('gb2312', 'utf-8', $result[$i][1]));
						$school_year = trim(iconv('gb2312', 'utf-8', $result[$i][2]));
						$addr = trim(iconv('gb2312', 'utf-8', $result[$i][3]));
						$identity = trim(iconv('gb2312', 'utf-8', $result[$i][4]));
						if (!empty($shop_id) && !empty($mobile)){
							//商家id，csv商家id，手机号，入校年份，地址，身份去操作，如果成功返回真，失败返回假
							$res = D('Users')->ImportMember($this->shop_id,$shop_id,$mobile,$school_year,$addr,$identity); 
							if ($res) {
								$succ_result += 1;
							} else {
								$error_result += 1;
							}
							
						} else {
							$error_result += 1;
						}
					}
					fclose($handle);
					$this -> tuSuccess('导入会员操作成功！成功' . $succ_result . '条，失败' . $error_result . '条', U('index/index'));
					//关闭指针
				} else {
					$this -> tuError('文件后缀格式必须为xls或csv');
					exit ;
				}
			} else {
				$this -> tuError('导入会员失败');
				exit ;
			}
		}
	}
	
	
}