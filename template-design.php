<?php
/**
 * Template Name: 球衣展示页
 *
 * Created by PhpStorm.
 * Date: 14-8-1
 * Time: 上午1:04
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */
require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');
$pdo = require_once(dirname(__FILE__) . '/inc/pdo.php');

// 取设计内容
$id = (int)array_pop(explode('/', $_SERVER['REQUEST_URI']));
$sql = "SELECT *
        FROM `t_user_diy`
        WHERE `id`=$id";
$design_data = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);

// 取作者内容
$user_data = get_user_by('id', $design_data['userid']);

// 取其它设计4枚
$sql = "SELECT *
        FROM `t_user_diy`
        WHERE `id`!=$id AND `status`=0
        ORDER BY `id` DESC
        LIMIT 4";
$other = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 设置body class
function add_design_body_class($classes) {
  $classes[] = 'design';
  return $classes;
}

add_filter('body_class', 'add_design_body_class');

get_header();

$result = $design_data;
$result['author'] = $user_data->user_login;
$result['other'] = $other;
Spokesman::toHTML($result, dirname(__FILE__) . '/template/design.html');

get_footer();