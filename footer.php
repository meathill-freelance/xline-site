<?php
/**
 * Created by PhpStorm.
 * Date: 13-11-14
 * Time: 下午11:01
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */

require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');

$result = array(
  'content_url' => esc_url(content_url('/')),
);
// 公司新闻
if (have_posts()) {
  $blog = array();
  $count = 0;
  while (have_posts()) {
    the_post();
    $blog[] = array(
      'title' => the_title_attribute(array('echo' => FALSE)),
      'link' => apply_filters('the_permalink', get_permalink()),
      'date' => apply_filters('the_time', get_the_time('Y-m-d'), 'Y-m-d'),
    );
    $count ++;
    if ($count >= 2) {
      break;
    }
  }
  $result['blog'] = $blog;
}
// 主题和插件内容
ob_start();
wp_footer();
do_action('xline_footer');
$result['theme_footer'] = ob_get_clean();


Spokesman::toHTML($result, dirname(__FILE__) . '/template/footer.html');