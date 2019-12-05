<?php
class LiveAction extends CommonAction{
	
    public function _initialize(){
        parent::_initialize();
        $this->assign('shopcates', $shopcates = D('Shopcate')->fetchAll());
    }

	public function index(){
        $cat = (int) $this->_param('cat');
        $this->assign('cat', $cat);
        $order = (int) $this->_param('order');
        $this->assign('order', $order);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $areas = D('Area')->fetchAll();
        $area = (int) $this->_param('area');
        $this->assign('area_id', $area);
        $biz = D('Business')->fetchAll();
        $business = (int) $this->_param('business');
        $this->assign('business_id', $business);
        $this->assign('areas', $areas);
        $this->assign('biz', $biz);
        $this->assign('nextpage', LinkTo('live/loaddata', array('cat' => $cat, 'area' => $area, 'business' => $business, 'order' => $order, 't' => NOW_TIME, 'keyword' => $keyword, 'p' => '0000')));
        $this->display();
    }
	
    public function loaddata(){
        $Shop = D('Shop');
        import('ORG.Util.Page');
        $map = array('a.closed' => 0, 'a.audit' => 1, 'a.city_id' => $this->city_id,'b.live_id!=0');
        $cat = (int) $this->_param('cat');
        if ($cat) {
            $catids = D('Shopcate')->getChildren($cat);
            if (!empty($catids)) {
                $map['a.cate_id'] = array('IN', $catids);
            } else {
                $map['a.cate_id'] = $cat;
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['a.shop_name|addr'] = array('LIKE', '%' . $keyword . '%');
        }
        $area = (int) $this->_param('area');
        if ($area) {
            $map['a.area_id'] = $area;
        }
        $business = (int) $this->_param('business');
        if ($business) {
            $map['a.business_id'] = $business;
        }
        $order = (int) $this->_param('order');
        $lat = addslashes(cookie('lat'));
        $lng = addslashes(cookie('lng'));
        if (empty($lat) || empty($lng)) {
            $lat = $this->city['lat'];
            $lng = $this->city['lng'];
        }
        switch ($order) {
            case 2:
                $orderby = array('a.orderby' => 'asc', 'a.ranking' => 'desc');
                break;
            default:
                $orderby = " (ABS(a.lng - '{$lng}') +  ABS(a.lat - '{$lat}') ) asc ";
                break;
        }
        $count = $Shop->alias('a')->where($map)->join('bao_live b on a.shop_id=b.shop_id')->count();
        $Page = new Page($count, 8);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Shop->alias('a')->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->join('bao_live b on a.shop_id=b.shop_id')->field('a.view,a.score,b.*')->select();
        foreach ($list as $k => $val) {
            $list[$k]['d'] = getDistance($lat, $lng, $val['lat'], $val['lng']);
        }
        $shop_ids = array();
        foreach ($list as $key => $v) {
            $shop_ids[$v['shop_id']] = $v['shop_id'];
        }
        $shopdetails = D('Shopdetails')->itemsByIds($shop_ids);
        foreach ($list as $k => $val) {
            $list[$k]['price'] = $shopdetails[$val['shop_id']]['price'];
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	public function view(){
        $live_id = I('get.live_id');
        $this->assign('live',$live = M('live')->where('live_id='.$live_id)->find());
        $this->assign('shop',$shop = M('shop')->where('shop_id='.$live['shop_id'])->find());
		$this->assign('detail',$detail = M('shop')->where('shop_id='.$live['shop_id'])->find());
        $this->assign('shop_details',$shop_details = M('shop_details')->where('shop_id='.$live['shop_id'])->find());
        $this->assign('list',$list = M('live')->where('shop_id='.$live['shop_id'])->select());
        $this->assign('ctl','live');
        $url = str_replace("http://","",$live['url']);
        $url = explode("/",$url);
        $this->assign('url',json_encode($url));
		D('Live')->updateCount($live_id, 'views');
        $this->display();
    }
 }
    