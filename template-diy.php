<?php
/**
 * Template Name: 球衣diy
 *
 * Created by PhpStorm.
 * Date: 14-3-30
 * Time: 下午10:04
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */

$path = explode('/', $_SERVER['REQUEST_URI']);
$id = (int)array_pop($path);

// 将定制界面的js加入
function add_diy_js() {
  global $id;
  echo '<script src="//cdn.staticfile.org/swfobject/2.2/swfobject.js"></script>';
  echo '<script src="/wp-content/themes/xline/js/diy.js"></script>';
  if ($id) {
    echo '<script src="/wp-content/themes/xline/js/diy-auto.js"></script>';
  }
}
add_action('xline_footer', 'add_diy_js');

get_header();

$result = array(
  'full_title' => '定制球衣',
  'id' => $id,
);
require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');
$template = dirname(__FILE__) . '/template/diy.html';
Spokesman::toHTML($result, $template);

get_footer();