<?php
/**
 * Template Name: 我的衣柜页
 *
 * Created by PhpStorm.
 * Date: 14-8-3
 * Time: 下午4:11
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */
require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');
$pdo = require_once(dirname(__FILE__) . '/inc/pdo.php');

get_header();

// 取设计内容
$me = get_current_user_id();
if (!$me) {
  readfile(dirname(__FILE__) . '/template/not-login.html');
  get_footer();
  exit();
}

$path = array_pop(explode('/', $_SERVER['REQUEST_URI']));
$page = is_numeric($path) ? (int)$path : 0;
$pagesize = 12;
$start = $page * $pagesize;

// 取设计总数
$sql = "SELECT COUNT('X')
        FROM `t_user_diy`
        WHERE `status`=0 AND `userid`=$me";
$total = $pdo->query($sql)->fetch(PDO::FETCH_COLUMN);

// 处理翻页
$pages = init_page($page, $pagesize, $total);

// 取设计10枚
$sql = "SELECT *
        FROM `t_user_diy`
        WHERE `status`=0 AND `userid`=$me
        ORDER BY `id` DESC
        LIMIT $start, $pagesize";
$list = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 设置body class
function add_design_body_class($classes) {
  $classes[] = 'my';
  return $classes;
}
add_filter('body_class', 'add_design_body_class');

$result = array_merge($pages, array(
  'list' => $list,
  'has_list' => $total > 0,
  'base' => "/my",
));
Spokesman::toHTML($result, dirname(__FILE__) . '/template/my.html');

get_footer();