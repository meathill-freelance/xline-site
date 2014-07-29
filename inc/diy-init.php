<?php
/**
 * Created by PhpStorm.
 * Date: 14-7-29
 * Time: 下午3:15
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */
// 保存diy结果
function line_save() {
  header('Content-type:application/json; charset: UTF-8');
  echo json_encode(array(
    'msg' => 'line save',
  ));
  exit();
}
add_action('wp_ajax_nopriv_line_save', "line_save");
add_action('wp_ajax_line_save', "line_save");

// 保存壁纸
function line_create_wallpaper() {
  header('Content-type:application/json; charset: UTF-8');
  echo json_encode(array(
    'msg' => 'line wallpaper',
  ));
  exit();
}
add_action('wp_ajax_nopriv_line_create_wallpaper', "line_create_wallpaper");
add_action('wp_ajax_line_create_wallpaper', "line_create_wallpaper");

// 添加到购物车并结算
function line_buy() {
  header('Content-type:application/json; charset: UTF-8');
  echo json_encode(array(
    'msg' => 'line buy',
  ));
  exit();
}
add_action('wp_ajax_nopriv_line_buy', "line_buy");
add_action('wp_ajax_line_buy', "line_buy");