<?php
namespace NAdmin\Model;
use Think\Model;
class MessageModel extends Model {
    protected $tableName = 'n_message';

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

}