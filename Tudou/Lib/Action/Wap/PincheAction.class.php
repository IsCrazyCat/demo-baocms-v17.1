<?php 
class PincheAction extends CommonAction{
	
	 private $create_fields = array('cate_id','city_id','user_id','photo','start_time','start_time_more','goplace','toplace','middleplace','num_1','num_2','num_3','num_4','mobile','lng','lat','details');

   protected function _initialize(){
        parent::_initialize();
		if($this->_CONFIG['operation']['pinche'] == 0){
            $this->error('此功能已关闭');
            die;
        }
        $getPincheCate = D('Pinche')->getPincheCate();
        $this->assign('getPincheCate', $getPincheCate);
		$this->assign('areas', $areas = D('Area')->where(array('city_id'=>$this->city_id))->select());
    }
	
    public function index(){
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
		
		$keyword2 = $this->_param('keyword2', 'htmlspecialchars');
        $this->assign('keyword2', $keyword2);
		
		
        $cate_id = (int) $this->_param('cate_id');
        $this->assign('cate_id', $cate_id);
        $order = (int) $this->_param('order');
        $areas = D('Area')->fetchAll();
        $area_id = (int) $this->_param('area_id');
        $this->assign('area_id', $area_id);
        $this->assign('areas', $areas);
		
		
        $this->assign('nextpage', LinkTo('pinche/loaddata', array('cate_id' => $cate_id, 't' => NOW_TIME, 'area_id' => $area_id, 'order' => $order,  'keyword' => $keyword,  'keyword2' => $keyword2,'p' => '0000')));
		$bg_time = strtotime(TODAY);
		$counts['pinche_day'] = (int) D('Pinche')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)),'audit' => 1,'city_id' => $this->city_id,'closed'=>0))->count();
		$this->assign('counts', $counts);
        $this->display(); 
    }
	
	
    public function loaddata(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'closed' => 0,'city_id' => $this->city_id,'start_time' => array('EGT', TODAY));
		
		
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        if($keyword && $keyword !='出发地搜索'){
            $map['goplace'] = array('LIKE', '%' . $keyword . '%');
        }
		$keyword2 = $this->_param('keyword2', 'htmlspecialchars');
		if($keyword2 && $keyword2 !='目的地搜索'){
            $map['toplace'] = array('LIKE', '%' . $keyword2 . '%');
        }
		
		
		
        $area_id = (int) $this->_param('area_id');
        if($area_id){
            $map['area_id'] = $area_id;
        }
        $cate_id = (int) $this->_param('cate_id');
        if($cate_id){
            $map['cate_id'] = $cate_id;
        }
		
        $order = $this->_param('order', 'htmlspecialchars');
		$lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if(empty($lat) || empty($lng)){
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
		
        $orderby = '';
        switch ($order){
            case 3:
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
                break;
            case 2:
                $orderby = array('top_time' =>'desc','num' => 'asc', 'pinche_id' => 'desc');
                break;
            default:
                $orderby = array('top_time' =>'desc','create_time' => 'desc');
                break;
        }
     
        $this->assign('order', $order);
        $count = $pinche->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 

        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
	
        $list = $pinche->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
			if($area = D('Area')->where(array('area_id'=>$val['area_id']))->find()){
                $list[$k]['area'] = $area;
            }
        }
	
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
    public function detail($pinche_id){
        $pinche_id = (int) $pinche_id;
        if (empty($pinche_id) || !($detail = D('Pinche')->find($pinche_id))) {
            $this->error('该拼车不存在');
        }
		$this->assign('citys', D('City')->fetchAll());
		$this->assign('communitys', $communitys =  D('Community')->select());
		$detail['city'] = D('City')->where(array('city_id'=>$detail['city_id']))->find(); 
        $detail['area'] = D('Area')->where(array('area_id'=>$detail['area_id']))->find();
        $this->assign('detail', $detail);
		D('Pinche')->updateCount($pinche_id, 'views');
        $this->display();
    }
	
    public function create(){
		if(empty($this->uid)){
            header("Location:" . U('passport/login'));
            die;
        }
        if($this->isPost()){
            $data = $this->createCheck();
			$data['photo'] = $this->_post('photo', false);
            if ($pinche_id = D('Pinche')->add($data)) {
                $this->tuMsg('发布成功', U('pinche/index'));
            }
            $this->tuMsg('发布失败');
        }else{
            $lat = cookie('lat');
            $lng = cookie('lng');
            if(empty($lat) || empty($lng)){
                $lat = $this->_CONFIG['site']['lat'];
                $lng = $this->_CONFIG['site']['lng'];
            }
            $this->assign('lng', $lng);
            $this->assign('lat', $lat);
            $this->display();
        }
    }
	
    public function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if(empty($data['cate_id'])) {
            $this->tuMsg('类型不能为空');
        }
		
		$data['city_id'] = $this->city_id;
        if(empty($data['city_id'])){
            $this->tuMsg('参数错误');
        }
		$data['user_id'] = $this->uid;
        $data['start_time'] = htmlspecialchars($data['start_time']);
        if(empty($data['start_time'])){
            $this->tuMsg('出发日期不能为空');
        }
		if(!isDate($data['start_time'])){
            $this->tuMsg('出发日期格式不正确');
        }
        $data['start_time_more'] = htmlspecialchars($data['start_time_more']);
		$data['goplace'] = htmlspecialchars($data['goplace']);
        if(empty($data['goplace'])){
            $this->tuMsg('出发地不能为空');
        }
        $data['toplace'] = htmlspecialchars($data['toplace']);
        if(empty($data['toplace'])){
            $this->tuMsg('目的地不能为空');
        }
		$data['middleplace'] = htmlspecialchars($data['middleplace']);
		$data['num_1'] = htmlspecialchars($data['num_1']);
		$data['num_2'] = htmlspecialchars($data['num_2']);
		$data['num_3'] = htmlspecialchars($data['num_3']);
		$data['num_4'] = htmlspecialchars($data['num_4']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
		$data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        if(empty($data['mobile'])){
            $this->tuMsg('手机不能为空');
        }
        if(!ismobile($data['mobile'])) {
            $this->tuMsg('手机格式不正确');
        }
        $data['audit'] = 1;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
	
	
	 public function getIndexList(){
        $pinche = D('Pinche');
        import('ORG.Util.Page');
        $map = array('audit' => 1,'closed' => 0,'city_id' => $this->city_id,'start_time' => array('EGT', TODAY));
		
		
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        if($keyword && $keyword !='出发地搜索'){
            $map['goplace'] = array('LIKE', '%' . $keyword . '%');
        }
		$keyword2 = $this->_param('keyword2', 'htmlspecialchars');
		if($keyword2 && $keyword2 !='目的地搜索'){
            $map['toplace'] = array('LIKE', '%' . $keyword2 . '%');
        }
		
        $area_id = (int) $this->_param('area_id');
        if($area_id){
            $map['area_id'] = $area_id;
        }
        $cate_id = (int) $this->_param('cate_id');
        if($cate_id){
            $map['cate_id'] = $cate_id;
        }
		
        $order = $this->_param('order', 'htmlspecialchars');
		$lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if(empty($lat) || empty($lng)){
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
		
        $orderby = '';
        switch ($order){
            case 3:
                $orderby = " (ABS(lng - '{$lng}') +  ABS(lat - '{$lat}') ) asc ";
                break;
            case 2:
                $orderby = array('top_time' =>'desc','num' => 'asc', 'pinche_id' => 'desc');
                break;
            default:
                $orderby = array('top_time' =>'desc','create_time' => 'desc');
                break;
        }
     
        $this->assign('order', $order);
        $count = $pinche->where($map)->count(); 
        $Page = new Page($count,100); 
        $show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
	
        $list = $pinche->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val){
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
			$list[$k]['time'] = pincheTime($val['create_time']);
			if($area = D('Area')->where(array('area_id'=>$val['area_id']))->find()){
                $list[$k]['area'] = $area;
            }
        }
	
        $this->assign('list', $list);
        $json_arr = array('status'=>1,'msg'=>'获取成功','p'=>$p,'count'=>$count,'CPostList'=>$list);
        $json_str = json_encode($json_arr);
        exit($json_str); 
    }
	
	//获取海报
	public function banner(){
		$list = D('Ad')->where(array('site_id'=>'57','closed'=>'0'))->select();
		foreach ($list as $k => $val){
			$list[$k]['code'] = 1;
			$list[$k]['id'] = $val['ad_id'];
			$list[$k]['ImageUrl'] = strpos($val['photo'],"http")===false ?  __HOST__.$val['photo'] : $val['photo'];
		}
		$this->ajaxReturn(array('code' => '1', 'data' =>$list));
	}
	
	
	//订阅
	public function getfocus(){
		$this->ajaxReturn(array('code' => '1', 'MsgTitle' =>'嗯是的'));
	}
   
}