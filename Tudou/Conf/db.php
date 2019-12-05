<?php

    return  array(
    'DB_TYPE'   =>  'mysql',
    'DB_HOST'   =>  '127.0.0.1',
    'DB_NAME'   =>  'baocms_v17',//数据库名字
    'DB_USER'   =>  'baocms',//数据库用户名
    'DB_PWD'    =>  'baocms',//数据库密码
    'DB_PORT'   =>   3306 ,
    'DB_CHARSET'=>  'utf8',
    'DB_PREFIX' =>  'tu_',
    'AUTH_KEY'  =>  '520efebc109577cc0a86de013d0164ac', //这个KEY只是保证部分表单在没有SESSION 的情况下判断用户本人操作的作用
    'TU_KEY'   => '520efebc109577cc0a86de013d0164ac',
	'COOKIE_DOMAIN' => 'www.baocmsdemo.com',      // Cookie有效域名

);