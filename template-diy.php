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

// 将定制界面的js加入
function add_diy_js() {
  echo '<script src="//cdn.staticfile.org/swfobject/2.2/swfobject.js"></script>';
  echo '<script src="/wp-content/themes/xline/js/diy.js"></script>';
}
add_action('xline_footer', add_diy_js);

function add_diy_css() {
  echo '<link rel="stylesheet" href="/wp-content/themes/xline/css/line.css">';
}
add_action('xline_head', add_diy_css);

get_header();

$result = array(
  'full_title' => '定制球衣',
);
require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');
$template = dirname(__FILE__) . '/template/diy.html';
Spokesman::toHTML($result, $template);

get_footer();