<?php
define('TODAY', date("Y-m-d")); //不要遗漏
$dbconfigs = require  BASE_PATH.'/'.APP_NAME.'/Conf/db.php';
$configs =  array(
    //'配置项'=>'配置值'
    'APP_GROUP_LIST' => 'Admin,Merchant,Wap,Delivery,Seller,App,Api,Wuye,User,Members,Home,Being,Weixin,Worker,Property,Village,Agent,Proxy,Grab', //项目分组设定
    'DEFAULT_GROUP'  => 'Home', //默认分组
	//Backstage后台，Merchant商家中心，Wap手机版会员，MembersPC版本会员，HomePC首页，分站Substation，物业，Property，SellerWAP商家，
    //SESSION 的设置
    'SESSION_AUTO_START'    => true,
    'SESSION_TYPE'          => 'DB',   
    'DEFAULT_APP'           => 'Tudou',
    //URL设置
    'URL_MODEL'            => 2,
    'URL_HTML_SUFFIX'      => '.html',
    'URL_ROUTER_ON'        => true,
    'URL_CASE_INSENSITIVE' => true, //url不区分大小写
    'URL_ROUTE_RULES'      => array(
    ), 
    'APP_SUB_DOMAIN_DEPLOY' => false,
    //默认系统变量
    'VAR_GROUP'            => 'g',
    'VAR_MODULE'           => 'm',
    'VAR_ACTION'           => 'a',
    'TMPL_DETECT_THEME'    => true,
    'VAR_TEMPLATE'         => 'theme',
	
	'LOG_RECORD' => false, // 开启日志记录
	'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR', // 只记录EMERG ALERT CRIT ERR 错误
	
	'SESSION_OPTIONS' => array('expire' => 3600),

    //模版设置相关
    'DEFAULT_THEME'         => 'default',
    'TMPL_L_DELIM'          => '<{',
    'TMPL_R_DELIM'          => '}>', 
    'TMPL_ACTION_SUCCESS'   => 'public/dispatch_jump',
    'TMPL_ACTION_ERROR'     => 'public/dispatch_jump',
    'TAGLIB_LOAD'           => true,
    'APP_AUTOLOAD_PATH'     => '@.TagLib',
    'TAGLIB_BUILD_IN'       => 'Cx,Calldata',

	'DATA_BACKUP_PATH' => './attachs/data/',
	'DATA_BACKUP_PART_SIZE' => 20971520,
	'DATA_BACKUP_COMPRESS' => 1,
	'DATA_BACKUP_COMPRESS_LEVEL' => 9,
	
	'LANG_SWITCH_ON' => true,   // 开启语言包功能
	'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
	'LANG_LIST'        => 'zh-cn', // 允许切换的语言列表 用逗号分隔
	'VAR_LANGUAGE'     => 'l', // 默认语言切换变量

);

return array_merge($configs,$dbconfigs);
?>