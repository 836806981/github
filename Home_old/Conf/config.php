<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE' =>'mysql',
    'DB_HOST' =>'127.0.0.1',
    'DB_NAME' =>'tlay',
    'DB_USER' =>'root',
    'DB_PWD'  =>'tlay111',
    'DB_PORT' =>'3306',
    'DB_PREFIX' =>'',
    'URL_MODEL'=>2,
    'ERROR_PAGE'=>__ROOT__.'/404.html',
//    'URL_PATHINFO_DEPR'=>'-',

    'SESSION_AUTO_START'=>true,
    'USER_AUTH_KEY'=>'authId',
    'ADMIN_AUTH_KEY'=>'adminId',
    'AYI_AUTH_KEY'=>'AyiId',
    'ORDER_AUTH_KEY'=>'OrderId',
    'TRAINEE_AUTH_KEY'=>'TraineeId',
    'TRAINING_AUTH_KEY'=>'Training',
    'GOAL_AUTH_KEY'=>'GoalId',

    'TMPL_PARSE_STRING' => array(
        '__UPLOADS__'=>'/TLAY/Uploads',
        '__PUBLIC__'=>'/TLAY/Home/View/Public',
    ),

    'alipay_config'=>array(
    'partner' =>'',   //这里是你在成功申请支付宝接口后获取到的PID；
    'key'=>'',//这里是你在成功申请支付宝接口后获取到的Key
    'sign_type'=>strtoupper('MD5'),
    'input_charset'=> strtolower('utf-8'),
    'cacert'=> getcwd().'\\cacert.pem',
    'transport'=> 'http',
),
        //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；

    'alipay' => array(
    //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
    'seller_email'=>'',

//这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
    'notify_url'=>'http://127.0.0.1/syl/syl/index.php/Pay/notifyurl',

//这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
    'return_url'=>'http://127.0.0.1/syl/syl/index.php/Pay/returnurl',

//支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
    'successpage'=>'User/payBack?status=payed',

//支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
    'errorpage'=>'User/payBack?status=unpay',
),
//
//    'weixin_config' => array(
//        'APPID' => 'wxb60dcc41fb8dc6d3',
//	    'MCHID' => '1266196301',
//	    'KEY' => 'diaomaoshouwomenzouhuangweihahah',
//	    'APPSECRET' => 'e5f1e3c989d7d8088525335db58b6a06',
//        'SSLCERT_PATH' => '../cert/apiclient_cert.pem',
//	    'SSLKEY_PATH' => '../cert/apiclient_key.pem',
//        'CURL_PROXY_HOST' => "0.0.0.0",//"10.152.18.220";
//	    'CURL_PROXY_PORT' => 0,//8080;
//        'REPORT_LEVENL' => 1,
//    ),
//
//    'weixin' => array(
//        define('WEB_HOST', 'localhost'),
//        'JS_API_CALL_URL' => WEB_HOST.'/syl/syl/index.php/Weixin/jsApiCall',
//        'SSLCERT_PATH' => WEB_HOST.'/syl/syl/ThinkPHP/Library/Vendor/cert/apiclient_cert.pem',
//        'SSLKEY_PATH' => WEB_HOST.'/syl/syl/ThinkPHP/Library/Vendor/cert/apiclient_key.pem',
//        'NOTIFY_URL' =>  WEB_HOST.'/syl/syl/index.php/Weixin/notify',
//        'CURL_TIMEOUT' => 30
//    ),
);