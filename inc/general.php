<?php
/**
 * Created by PhpStorm.
 * Date: 14-7-31
 * Time: 下午10:34
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */
require_once dirname(__FILE__) . "/Spokesman.class.php";

/**
 *  检查是否登录
 */
function is_login() {
  $result = array(
    'is_login' => true || is_user_logged_in(),
    'code' => 0,
  );
  Spokesman::say($result);
}
add_action('wp_ajax_nopriv_is_login', "is_login");
add_action('wp_ajax_is_login', "is_login");

function ajax_login(){
  // First check the nonce, if it fails the function will break
  $check = check_ajax_referer('ajax-login-nonce', 'security', false);
  if (!$check) {
    exit(json_encode(array(
      'code' => 1,
      'msg' => '验证码错误',
    )));
  }

  // Nonce is checked, get the POST data and sign user on
  $info = array(
    'user_login' => $_POST['log'],
    'user_password' => $_POST['pwd'],
    'remember' => $_POST['rememberme'],
  );


  $user_signon = wp_signon($info);
  Spokesman::judge(!is_wp_error($user_signon), '登录成功', '登录失败');
  exit();
}
add_action('wp_ajax_nopriv_ajax_login', "ajax_login");
add_action('wp_ajax_ajax_login', "ajax_login");
