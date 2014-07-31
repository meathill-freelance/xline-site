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

// 最新作品
$sql = "SELECT `id`, `userid`, `name`, `thumbnail`
        FROM `t_user_diy`
        WHERE `status`=0
        ORDER BY `id` DESC
        LIMIT 9";
$designs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$result = array(
  'theme_url' => get_template_directory_uri(),
  'items0' => array_slice($designs, 0, 5),
  'items1' => array_slice($designs, 5, 3),
  'items2' => array_slice($designs, 7, 1),
);

$template = dirname(__FILE__) . '/template/index.html';
Spokesman::toHTML($result, $template);

get_footer();