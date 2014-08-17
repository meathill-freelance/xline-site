<?php
/**
 * Created by PhpStorm.
 * Date: 13-11-14
 * Time: 下午11:16
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */

require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');

header("Content-type: text/html; charset=UTF-8");

// 判断当前模板
$is_design = preg_match('/^\/design\/(\d+)\/?$/', $_SERVER['REQUEST_URI']);
$is_diy = preg_match('/^\/diy/', $_SERVER['REQUEST_URI']);

// 生成title等
global $page, $paged;
$page_num = $page > 2 || $paged > 2 ? ' | ' . sprintf(__('第 %s 页'), max($paged, $page)) : '';
$title = wp_title('|', FALSE, 'right') . get_bloginfo('name') . $page_num;
if ($is_design) {
  global $design_data;
  $title = "球友作品 - " . $design_data['name'];
}
if ($is_diy) {
  $title = '定制球衣';
}

// 生成主题和插件内容
ob_start();
wp_head();
do_action('xline_head');
$theme_head = ob_get_clean();
ob_start();
wp_nonce_field('ajax-login-nonce', 'security');
$login_nonce = ob_get_clean();

$result = array(
  'theme_url' => get_template_directory_uri(),
  'title' => $title,
  'body_class' => join( ' ', get_body_class( $class ) ),
  'theme_head' => $theme_head,
  'is_login' => is_user_logged_in(),
  'nonce' => wp_create_nonce(),
  'login_nonce' => $login_nonce,
  'lost_pwd_url' => wp_lostpassword_url(),
);

Spokesman::toHTML($result, dirname(__FILE__) . '/template/header.html');