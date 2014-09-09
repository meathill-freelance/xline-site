<?php
/**
 * Created by PhpStorm.
 * Date: 13-11-17
 * Time: 下午10:28
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */

get_header();

$page = array();
if (have_posts()) {
  the_post();
  $content = get_the_content('继续阅读');
  $post_id = get_the_ID();
  $page = array(
    'id' => $post_id,
    'is_featured' => is_sticky() && is_home() && ! is_paged(),
    'class' => join(' ', get_post_class('', $post_id)),
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
    'theme_url' => get_template_directory_uri(),
  );
}

require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');
$template = dirname(__FILE__) . '/template/page.html';
Spokesman::toHTML($page, $template);

get_footer();