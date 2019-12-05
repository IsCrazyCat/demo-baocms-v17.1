<?php
class AdsiteModel extends CommonModel {
    protected $pk = 'site_id';
    protected $tableName = 'ad_site';
    protected $token = 'ad_site';
    public function getType() {
        return array(1 => '文字广告', 2 => '图片广告', 3 => '代码广告');
    }

    public function getPlace() {
        return array(
            1 => 'PC首页',
            2 => 'PC抢购',
            3 => 'PC活动',
            4 => 'PC家政',
            5 => 'PC商城',
            6 => 'PC外卖',
            7 => 'PC订座',
            8 => 'PC分类信息',
            9 => 'PC优惠券',
            10 => 'PC商家',
            11 => 'PC积分商城',
            12 => 'PC一元云购',
            13 => 'PC贴吧',
            14 => '手机首页',
            15 => '手机抢购',
            16 => '手机商家',
            17 => '手机商城',
            18 => '手机活动',
            19 => '手机家政',
            20 => '手机外卖',
            21 => '手机订座',
            22 => '手机酒店',
            23 => '手机农家乐',
            24 => '手机拼车',
            25 => '手机新闻',
            26 => '手机积分商城',
            27 => '手机分类信息',
            28 => '手机小区',
            29 => '手机乡村',
            30 => '手机股权',
            31 => '手机云购',
            32 => '手机其他',
            33 => 'PC登录注册',
        );

    }



}

