<?php
/**
 * Created by PhpStorm.
 * Date: 14-7-19
 * Time: 上午12:40
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */

require 'config/config.php';

$includes = array(
  'inc/general.php',
  'inc/theme-init.php',
  'inc/diy-init.php',
);
foreach ($includes as $item) {
  locate_template($item, true);
}

// 下面是一些不好归类的函数
function woocommerce_support() {
  add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'woocommerce_support');


function init_page($page, $pagesize, $total) {
  $prev = $page - 1 > 0 ? $page - 1 : 0;
  $max = ceil($total / $pagesize);
  $next = $page + 1 < $max - 1 ? $page + 1 : ($max - 1);
  $from = 0 < $page - 5 ? $page - 5 : 0;
  $to = $page + 10 - ($page - $from) < $max - 1 ? $page + 10 - ($page - $from) : ($max - 1);
  $pages = array();
  for (; $from <= $to; $from++) {
    $pages[] = array(
      'to' => $from,
      'page' => $from + 1,
      'active' => $from === $page ? 'active' : '',
    );
  }

  return array(
    'prev' => $prev,
    'no-prev' => $prev == $page ? 'disabled' : '',
    'next' => $next,
    'no-next' => $next == $page ? 'disabled' : '',
    'pages' => $pages,
  );
}