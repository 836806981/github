<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE' =>'mysql',
    'DB_HOST' =>'127.0.0.1',
    'DB_NAME' =>'tlay',
    'DB_USER' =>'root',
    'DB_PWD'  =>'root',
    'DB_PORT' =>'3306',
    'DB_PREFIX' =>'',
    'URL_MODEL'=>2,
//    'URL_PATHINFO_DEPR'=>'-',

//    'URL_ROUTER_ON' => true,
//    'URL_ROUTE_RULES'=>array(
//        '/^news_content\-(\d+)$/' => 'news_content/id/:1',
//    ),

    'SESSION_AUTO_START'=>true,
    'USER_AUTH_KEY'=>'authId',
    'ADMIN_AUTH_KEY'=>'adminId',
    'AYI_AUTH_KEY'=>'AyiId',
    'ORDER_AUTH_KEY'=>'OrderId',
    'TRAINEE_AUTH_KEY'=>'TraineeId',
    'TRAINING_AUTH_KEY'=>'Training',
    'GOAL_AUTH_KEY'=>'GoalId',
    'TMPL_PARSE_STRING' => array(
        '__UPLOADS__'=>'/Uploads',
        '__PUBLIC__'=>'/Phone/View/Public',
    ),


    'alipay_config'=>array(
        'partner' =>'2088421964931498',   //这里是你在成功申请支付宝接口后获取到的PID；
        'key'=>'vo826ie8lp9zh7lyp1efwsdlnjhk7ezu',//这里是你在成功申请支付宝接口后获取到的Key
        'sign_type'=>strtoupper('MD5'),
        'input_charset'=> strtolower('utf-8'),
        'cacert'=> getcwd().'\\cacert.pem',
        'transport'=> 'http',
    ),
    //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；

    'alipay'   =>array(
        //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email'=>'wenwen@tianluoayi.com',
        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url'=>'http://www.tianluoayi.com/m/notifyurl',
        //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'return_url'=>'http://www.tianluoayi.com/m/returnurl',
        //支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
        'successpage'=>'http://www.tianluoayi.com/m/success',
        //支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
        'errorpage'=>'http://www.tianluoayi.com/m/error',
    ),


);