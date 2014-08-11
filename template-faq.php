<?php
/**
 * Template Name: 常见问题
 *
 * Created by PhpStorm.
 * Date: 13-12-4
 * Time: 下午11:51
 * @overview 用于显示常见问题
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */
get_header();

// 读取常见问题
$result = array(
  'faqs' => array()
);
$args = array('post_type' => 'faq', 'orderby' => 'ID', 'posts_per_page' => -1);
$faqs = new WP_Query($args);
$count = 0;
while ($faqs->have_posts()) {
  $faqs->the_post();
  $title = the_title('', '', FALSE);
  $content = get_the_content();
  $content = apply_filters('the_content', $content);
  $result['faqs'][] = array(
    'title' => $title,
    'content' => $content,
    'index' => $count,
    'in' => $count === 0? 'in' : '',
  );
  $count++;
}

require_once(dirname(__FILE__) . '/inc/mustache.php');
$tpl = new Mustache_Engine();
$template = dirname(__FILE__) . '/template/faq.html';
$template = file_get_contents($template);
$template = str_replace('"../', '"{{theme_url}}wp-content/themes/line/', $template);
echo $tpl->render($template, $result);

get_footer();