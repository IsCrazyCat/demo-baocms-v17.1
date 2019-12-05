<?php
class SignAction extends CommonAction {
    public function signed(){
        if(!empty($this->uid)){
            $integral = $this->_CONFIG['integral']['sign'];
            $data = D('Usersign')->getSign($this->uid,$integral);
            $this->assign('sign',D('Usersign')->getSign($this->uid,$integral));
		}else{
			$this->error('请登录后进行签到！',U("passport/login"));
		}
		
		$first = strtotime(date("Y-m-01 00:00:00"));
        $first = date("w", $first);
        $weekArr = array("日", "一", "二", "三", "四", "五", "六");
        $maxDay = date('t', strtotime("" . date("Y") . "-" . date("m") . ""));
        for ($j = 0; $j < $first; $j++) {
            $blankArr[] = $j;
        }

        for ($i = 0; $i < $maxDay; $i++) {
            $z = $first + $i;
            $days[] = array("key" => $i, "key2" => $z % 7);
        }
        $nowtime = date("Y年m月d日 H:i:s") . "&nbsp;&nbsp;星期" . $weekArr[date("w")];
        $this->assign("nowtime", $nowtime);
        $this->assign("days", $days);
        $this->assign("first", $first);
        $this->assign("blankArr", $blankArr);
        $total = $first + count($days);

        for ($x = 0; $x < ceil($maxDay / 7) * 7 - $total; $x++) {
            $other[] = $x;
        }
        $this->assign("other", $other);
		
		
        $this->display();

    }

    public function signing(){
        if(empty($this->uid)){
            $this->ajaxReturn(array('code'=>'1','msg'=>'登录后签到','url'=>U('passport/login')));           
        }
		$life_id = I('life_id', 0, 'trim,intval');
        $integal = D('Usersign')->sign($this->uid,$this->_CONFIG['integral']['sign'],$this->_CONFIG['integral']['firstsign']);
		D('Users')->prestige($this->uid,'sign');
        if($integal !== false){
			$this->ajaxReturn(array('code'=>'2','msg'=>'恭喜您签到成功！系统赠送了您'.$integal.'积分'));   
        }else{
			$this->ajaxReturn(array('code'=>'0','msg'=>'很抱歉您已经签到过了'));  
        }
    }
    

}



