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

$id = (int)array_pop(explode('/', $_SERVER['REQUEST_URI']));
if (!$id) {
  readfile(dirname(__FILE__) . '/template/design-error.html');
  exit();
}

// 取设计内容
$sql = "SELECT *
        FROM `t_user_diy`
        WHERE `id`=$id";
$design_data = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
if (!$design_data) {
  readfile(dirname(__FILE__) . '/template/design-error.html');
  exit();
}

// 取模板
$sql = "SELECT `cloth1`, `cloth2`
        FROM `t_diy_detail`
        WHERE `id`=$id";
$clothes = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
// 去模板价格
$price = (int)get_post_meta($clothes['cloth1'], '_price', true);
if (isset($clothes['cloth2'])) {
  $price += (int)get_post_meta($clothes['cloth2'], '_price', true);
}
$products = array($clothes['cloth1'], $clothes['cloth2']);
$design_data['pid'] = implode(',', array_filter($products));

if (isset($_REQUEST['m'])) {
  switch ($_REQUEST['m']) {
    case 'buy':
      foreach ($clothes as $cloth) {
        $buy = $woocommerce->cart->add_to_cart($cloth, 1, null, null, array(
          'design_id' => $id,
        ));
      }
      $buy = $buy ? array(
        'success' => true,
        'msg' => '添加成功',
      ) : array(
        'success' => false,
        'msg' => '添加失败',
      );
      break;
  }
}

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

// 分享设置
$share = array(
  'share_url' => get_site_url() . $_REQUEST,
  'share_title' => '哇塞，这个设计好霸道，大家都来看看',
);
// 引入js
function add_share_js() {
  echo '<script src="/wp-content/themes/xline/js/share.js"></script>';
  echo '<script src="/wp-content/themes/xline/js/shop.js"></script>';
}
add_action('xline_footer', add_share_js);

get_header();

$result = array_merge($design_data, $share, array(
  'author' => $user_data->user_login,
  'other' => $other,
  'price' => $price,
  'buy' => $buy,
));
Spokesman::toHTML($result, dirname(__FILE__) . '/template/design.html');

get_footer();