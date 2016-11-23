<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE' =>'mysql',
    'DB_HOST' =>'127.0.0.1',
    'DB_NAME'   => 'tlay', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'tlay111', // 密码
    'DB_PORT' =>'3306',
    'DB_PREFIX' =>'',

    'SESSION_AUTO_START'=>true,
    'USER_AUTH_KEY'=>'authId',
    'ADMIN_AUTH_KEY'=>'adminId',
    'AYI_AUTH_KEY'=>'AyiId',
    'ORDER_AUTH_KEY'=>'OrderId',
    'TRAINEE_AUTH_KEY'=>'TraineeId',
    'TRAINING_AUTH_KEY'=>'Training',
    'GOAL_AUTH_KEY'=>'GoalId',
    'VIDEO_AUTH_KEY'=>'VideoId',
    //'URL_CASE_INSENSITIVE'  =>  true,

    'TMPL_PARSE_STRING' => array(
        '__UPLOADS__'=>'/TLAY/Uploads',
        '__PUBLIC__'=>'/TLAY/Video/View/Public',
    ),
);