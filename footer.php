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

if (!is_user_logged_in()) {
  function add_backbone_js() {
    echo '<script src="//cdn.staticfile.org/underscore.js/1.6.0/underscore-min.js"></script>';
    echo '<script src="//cdn.staticfile.org/backbone.js/1.1.2/backbone-min.js"></script>';
  }
  add_action('xline_footer', 'add_backbone_js');
}

$result = array(
  'theme_url' => get_template_directory_uri(),
);
// 主题和插件内容
ob_start();
wp_footer();
do_action('xline_footer');
$result['theme_footer'] = ob_get_clean();


Spokesman::toHTML($result, dirname(__FILE__) . '/template/footer.html');