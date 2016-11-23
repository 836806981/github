<?php
namespace Trainee\Model;
use Think\Model;
class LoginlogModel extends Model {
    protected $tableName = 'login_log';

    public function add_mod($map)
    {
        $result = $this->add($map);
        return $result;

    }

    public function login_mod($map)
    {
        $user_info = $this->where($map)->find();
        return $user_info;
    }
}