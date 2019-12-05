<?php
if (!defined('BASE_PATH')) {
    exit('Access Denied');
}
class CashAction extends CommonAction
{
    public function index()
    {
        $Userscash = D('Userscash');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Userscash->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create()
    {
        $Users = D('Users');
        $data = $Users->find($this->uid);
        if (IS_POST) {
            $money = (int) $_POST['money'];
            if ($money == 0) {
                $this->tuError('提现金额不能为0');
            }
            $money *= 100;
            if ($money > $data['money'] || $data['money'] == 0) {
                $this->tuError('余额不足，无法提现');
            }
            $arr = array();
            $arr['user_id'] = $this->uid;
            $arr['money'] = $money;
            $arr['addtime'] = NOW_TIME;
            $arr['account'] = $data['account'];
            D('Userscash')->add($arr);
            //扣除余额
            $Users->addMoney($data['user_id'], -$money, '申请提现，扣款');
            $this->tuSuccess('申请成功', U('cash/index'));
        } else {
            $this->assign('money', $data['money'] / 100);
            $this->display();
        }
    }
}