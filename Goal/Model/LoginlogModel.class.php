<?php
namespace Goal\Model;
use Think\Model;
class LoginlogModel extends Model {
    protected $tableName = 'goal_log';

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