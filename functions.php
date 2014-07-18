<?php
/**
 * Created by PhpStorm.
 * Date: 14-7-19
 * Time: 上午12:40
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */

$includes = array(

);
foreach ($includes as $item) {
  locate_template($item, true);
}

// 下面是一些不好归类的函数