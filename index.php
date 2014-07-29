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

// 最新活动
$args = array(
  'post_type' => 'page',
  'post_parent' => get_option('woo_active_page'),
);
$actives = new WP_Query($args);
$count = 0;
$result = array(
  'actives' => array(),
  'content_url' => esc_url(content_url('/')),
);
while ($actives->have_posts()) {
  $actives->the_post();
  $content = apply_filters('the_content', $content);
  $result['actives'][] = array(
    'thumbnail' => get_the_post_thumbnail(null, 'homepage-active'),
    'title' => the_title('', '', FALSE),
    'full_title' => the_title_attribute(array('echo' => FALSE)),
    'link' => apply_filters('the_permalink', get_permalink()),
    'date' => apply_filters('the_time', get_the_time('Y-m-d'), 'Y年m月d日'),
    'excerpt' => apply_filters('the_excerpt', get_the_excerpt()),
  );
  $count++;
  if ($count >= 3) {
    break;
  }
}

// 最新产品

$template = dirname(__FILE__) . '/template/index.html';
Spokesman::toHTML($result, $template);

get_footer();