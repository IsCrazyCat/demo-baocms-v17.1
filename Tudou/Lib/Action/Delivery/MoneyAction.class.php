<?php
class MoneyAction extends CommonAction {
   
	
	public function index(){
		$linkArr = array();
        $keyword = I('keyword','', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $linkArr['keyword'] = $keyword;

		
		$order_id = I('order_id');
        $this->assign('order_id', $order_id);;
		$linkArr['order_id'] = $order_id;
		
		
		$type = I('type');
        $this->assign('type', $type);
		$linkArr['type'] = $type;
		
		$star = I('star');
        $this->assign('star', $star);
		$linkArr['star'] = $star;
	
        $order = I('order','', 'htmlspecialchars');
        $this->assign('order', $order);
		$linkArr['order'] = $order;
		
        $this->assign('nextpage', linkto('money/loaddata',$linkArr, array('t' => time(), 'p' => '0000')));
		$this->assign('detail', $detail);
		$this->assign('linkArr', $linkArr);
		
		
		$count = D('Runningmoney')->where(array('user_id'=>$this->uid))->sum('money');
		$this->assign('count', $count);
        $this->display(); 
    }
	
	//家政列表加载页面
	public function loaddata(){
		
		import('ORG.Util.Page');
        $map = array('delivery_id'=>$this->delivery_id);
		$order_id = I('order_id');
        if($order_id){
            $map['order_id'] = $order_id;
        }
		
		if($type = I('type')){
			$map['type'] = $type;	
			$this->assign('type', $type);
		}
		$order = I('order','','htmlspecialchars');
        $orderby = '';
         switch ($order) {
			case 5:
                $orderby = array('create_time' => 'desc');
                break;
			case 4:
                $orderby = array('create_time' => 'asc');
                break;
            case 3:
                $orderby = array('money' => 'desc');
                break;
			case 2:
                $orderby = array('money' => 'asc');
                break;
            default:
                $orderby = array('create_time' =>'desc');
                break;
        }
		$this->assign('order', $order);
        $count = D('Runningmoney')->where($map)->count(); 
       $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = D('Runningmoney')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show);
        $this->display(); 
    }
	
	
}