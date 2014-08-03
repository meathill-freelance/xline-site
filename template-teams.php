<?php
/**
 * Template Name: XLINE球队页
 *
 * Created by PhpStorm.
 * Date: 14-8-2
 * Time: 下午10:08
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */
require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');
$pdo = require_once(dirname(__FILE__) . '/inc/pdo.php');

// 取设计内容
$alias = array('basketball', 'football', 'golf');
$path = array_slice(explode('/', $_SERVER['REQUEST_URI']), 2);
$type = is_numeric($path[0]) ? '' : $path[0];
$index = array_search($type, $alias);
$page = (int)array_pop($path);
$pagesize = 16;
$start = $page * $pagesize;

// 取设计总数
$sql = "SELECT COUNT('X')
        FROM `t_user_diy`
        WHERE `status`=0" . ($index === false ? '' : " AND `type`=$index");
$total = $pdo->query($sql)->fetch(PDO::FETCH_COLUMN);

// 处理翻页
$pages = init_page($page, $pagesize, $total);

// 取设计16枚
$sql = "SELECT *
        FROM `t_user_diy`
        WHERE `status`=0". ($index === false ? '' : " AND `type`=$index") . "
        ORDER BY `id` DESC
        LIMIT $start, $pagesize";
$list = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 设置body class
function add_design_body_class($classes) {
  $classes[] = 'teams';
  return $classes;
}

add_filter('body_class', 'add_design_body_class');

get_header();

$result = array_merge($pages, array(
  'list' => $list,
  'is_basketball' => $index === 0,
  'is_football' => $index === 1,
  'is_golf' => $index === 2,
  'base' => "/teams" . ($type ? "/$type" : ''),
));
Spokesman::toHTML($result, dirname(__FILE__) . '/template/teams.html');

get_footer();