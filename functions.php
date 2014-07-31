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
  'inc/general.php',
  'inc/diy-static.php',
  'inc/theme-init.php',
  'inc/diy-init.php',
);
foreach ($includes as $item) {
  locate_template($item, true);
}

// 下面是一些不好归类的函数
add_theme_support( 'woocommerce' );