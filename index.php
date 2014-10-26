<?php
/**
 * Created by PhpStorm.
 * Date: 13-11-5
 * Time: 上午12:32
 * @overview
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since
 */

get_header();

require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');
$pdo = require_once(dirname(__FILE__) . '/inc/pdo.php');

function xline_rename($arr) {
  foreach ($arr as $key => $item) {
    $item['name'] = !$item['name'] || $item['name'] == '未命名设计' ? 'XLINE球队' : $item['name'];
    $arr[$key] = $item;
  }
  return $arr;
}

// 最新作品
$sql = "SELECT `id`, `userid`, `name`, `thumbnail`
        FROM `t_user_diy`
        WHERE `status`=0 AND `type`=0
        ORDER BY `id` DESC
        LIMIT 5";
$basketball = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT `id`, `userid`, `name`, `thumbnail`
        FROM `t_user_diy`
        WHERE `status`=0 AND `type`=1
        ORDER BY `id` DESC
        LIMIT 3";
$football = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT `id`, `userid`, `name`, `thumbnail`
        FROM `t_user_diy`
        WHERE `status`=0 AND `type`=2
        ORDER BY `id` DESC
        LIMIT 1";
$golf = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$result = array(
  'theme_url' => get_template_directory_uri(),
  'basketball' => xline_rename($basketball),
  'football' => xline_rename($football),
  'golf' => xline_rename($golf),
);

$template = dirname(__FILE__) . '/template/index.html';
Spokesman::toHTML($result, $template);

get_footer();