<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE' =>'mysql',
    'DB_HOST' =>'127.0.0.1',
    'DB_NAME'   => 'tlay', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'root', // 密码
    'DB_PORT' =>'3306',
    'DB_PREFIX' =>'',

    'SESSION_AUTO_START'=>true,
    'USER_AUTH_KEY'=>'authId',
    'ADMIN_AUTH_KEY'=>'adminId',
    'N_ADMIN_AUTH_KEY'=>'N_adminId',
    'AYI_AUTH_KEY'=>'AyiId',
    'ORDER_AUTH_KEY'=>'OrderId',
    'TRAINEE_AUTH_KEY'=>'TraineeId',
    'TRAINING_AUTH_KEY'=>'Training',
    'GOAL_AUTH_KEY'=>'GoalId',
    //'URL_CASE_INSENSITIVE'  =>  true,

    'TMPL_PARSE_STRING' => array(
        '__UPLOADS__'=>'/Uploads',
        '__PUBLIC__'=>'/NAdmin/View/Public',
    ),
);