<?php
namespace Goal\Model;
use Think\Model;
class EmployeeModel extends Model {
    protected $tableName = 'employee';

    public function add_mod($map)
    {
        $result = $this->add($map);
        return $result;
    }

    public function save_mod($where,$map)
    {
        $result = $this->where($where)->save($map);
        return $result;
    }

    public function delete_mod($where)
    {
        $result = $this->where($where)->delete();
        return $result;
    }


    public function login_mod($map)
    {
        $user_info = $this->field('id,username,permission,status,real_name')->where($map)->find();
        return $user_info;
    }
}