<?php
namespace Home\Widget;
use Think\Controller;
class HotWidget extends Controller  {

    public function index($date){

        print_r($date);
    }
}