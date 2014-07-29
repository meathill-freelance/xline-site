<?php
/**
 * Created by PhpStorm.
 * Date: 13-11-14
 * Time: 下午11:38
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */
get_header();

$blog = array();
if (have_posts()) {the_post();
  $content = get_the_content('继续阅读');
  $blog = array(
    'id' => get_the_ID(),
    'is_featured' => is_sticky() && is_home() && ! is_paged(),
    'class' => join(' ', get_post_class($class, $post_id)),
    'full_title' => the_title_attribute(array('echo' => FALSE)),
    'is_search' => is_search(),
    'link' => apply_filters('the_permalink', get_permalink()),
    'date' => apply_filters('the_time', get_the_time('Y-m-d'), 'Y年m月d日'),
    'excerpt' => apply_filters('the_excerpt', get_the_excerpt()),
    'content' => apply_filters('the_content', $content),
    'category' => get_the_category_list(' <span class="divider">/</span></li><li>'),
    'tags' => get_the_tag_list('', '，'),
    'author' => get_the_author(),
    'author_url' => get_author_posts_url(get_the_author_meta('ID')),
    'author_avatar' => get_avatar(get_the_author_meta('email'), '70'),
    'author_description' => get_the_author_meta('description'),
  );
}

// 最新活动
$args = array(
  'post_type' => 'page',
  'post_parent' => 2,
);
$actives = new WP_Query($args);
$count = 0;
$blog['actives'] = array();
while ($actives->have_posts()) {
  $actives->the_post();
  $content = apply_filters('the_content', $content);
  $blog['actives'][] = array(
    'thumbnail' => get_the_post_thumbnail(null, 'single-active', array('class' => 'img-thumbnail')),
    'full_title' => the_title_attribute(array('echo' => FALSE)),
    'link' => apply_filters('the_permalink', get_permalink()),
  );
  $count++;
  if ($count >= 3) {
    break;
  }
}

require_once(dirname(__FILE__) . '/inc/mustache.php');
$tpl = new Mustache_Engine();
$template = dirname(__FILE__) . '/template/single.html';
$template = file_get_contents($template);
$template = str_replace('"../', '"{{theme_url}}wp-content/themes/line/', $template);

$home_url = esc_url(home_url('/'));
$html = $tpl->render($template, $blog);
$html_fragments = explode('<!-- comments -->', $html);

echo $html_fragments[0];

comments_template();

echo $html_fragments[1];

get_footer();