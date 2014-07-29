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

// 生成title等
global $page, $paged;
$page_num = $page > 2 || $paged > 2 ? ' | ' . sprintf(__('第 %s 页'), max($paged, $page)) : '';
$content_url = esc_url(content_url('/'));
// 生成主题和插件内容
ob_start();
wp_head();
do_action('xline_head');
$theme_head = ob_get_clean();
ob_start();
wp_nonce_field( 'ajax-login-nonce', 'security' );
$login_nonce = ob_get_clean();

$result = array(
  'content_url' => $content_url,
  'title' => wp_title('|', FALSE, 'right') . get_bloginfo('name') . $page_num,
  'body_class' => join( ' ', get_body_class( $class ) ),
  'theme_head' => $theme_head,
  'is_login' => is_user_logged_in(),
  'nonce' => wp_create_nonce(),
  'login_nonce' => $login_nonce,
  'lost_pwd_url' => wp_lostpassword_url(),
);

Spokesman::toHTML($result, dirname(__FILE__) . '/template/header.html');