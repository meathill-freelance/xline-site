<?php
/**
 * Created by PhpStorm.
 * Date: 14-7-29
 * Time: 下午3:14
 * @overview 
 * @author Meatill <lujia.zhai@dianjoy.com>
 * @since 
 */

/**
 * 添加图片尺寸
 */
function line_setup() {
  add_image_size('homepage-active', 257, 193);
  add_image_size('single-active', 100, 100);
}
add_action('after_setup_theme', 'line_setup');

/**
 * 添加文章类型
 * feedback 业内评价
 * partner 合作伙伴
 */
function create_post_type() {
  register_post_type('faq',
    array(
      'labels' => array(
        'name' => '常见问题',
        'singular_name' => '常见问题',
        'all_items' => '全部问题',
        'add_new' => '新增问题',
        'add_new_item' => '新增问题',
        'new_item' => '常见问题',
      ),
      'public' => true,
      'rewrite' => array('slug' => 'faq'),
      'description' => '常见问题页的问答，每篇对应一条，标题是问题，内容是答案。',
      'exclude_from_search' => false,
      'show_in_nav_menus' => false,
      'publicly_queryable' => false,
      'supports' => array('title', 'editor'),
    )
  );
}
add_action('init', 'create_post_type');

/**
 * 在订单页上显示导出按钮
 * @param $actions 按钮列表
 * @param $the_order 排序
 */
function add_output_button_to_order_page($actions, $the_order = null) {
  global $post;
  $actions['view'] = array(
    'url' 		=> get_theme_root_uri() . '/api/output.php?post=' . $post->ID,
    'name' 		=> '导出',
    'action' 	=> "output",
  );
  return $actions;
}
add_filter('woocommerce_admin_order_actions', 'add_output_button_to_order_page');

/**
 * 要求Wordpress使用SMTP发送邮件
 * 从php角度来说这样就够了，不过有些SElinux里默认禁止php使用fsockopen连接外网
 * 所以需要运行 `setsebool -P httpd_can_network_connect 1` 解禁
 * @see http://yml.com/fv-b-1-619/selinux--apache-httpd--php-establishing-socket-connections-using-fsockopen---et-al.html
 * @param PHPMailer $phpmailer
 */
function configure_smtp(PHPMailer $phpmailer) {
  $phpmailer->isSMTP();
  $phpmailer->Host = 'smtp.exmail.qq.com';
  $phpmailer->SMTPAuth = true;
  $phpmailer->Port = 465;
  $phpmailer->Username = 'service@xline.com.cn';
  $phpmailer->Password = 'xline@2014';
  $phpmailer->SMTPSecure = 'ssl';
  $phpmailer->From = 'service@xline.com.cn';
  $phpmailer->FromName = 'XLINE客服';
}
add_action('phpmailer_init', 'configure_smtp');