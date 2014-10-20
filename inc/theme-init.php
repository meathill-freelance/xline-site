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
    'url' 		=> get_theme_root_uri() . '/xline/api/output.php?id=' . $post->ID,
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
/*function configure_smtp(PHPMailer $phpmailer) {
  $phpmailer->isSMTP();
  $phpmailer->Host = 'smtp.exmail.qq.com';
  $phpmailer->SMTPAuth = true;
  $phpmailer->Port = 465;
  $phpmailer->Username = 'service@xline.com.cn';
  $phpmailer->Password = 'cybx227122';
  $phpmailer->SMTPSecure = 'ssl';
  $phpmailer->From = 'service@xline.com.cn';
  $phpmailer->FromName = 'XLINE客服';
}
add_action('phpmailer_init', 'configure_smtp');*/

function map_team_order($order_id, $post) {
  $pdo = require dirname(__FILE__) . "/../inc/pdo.php";
  $me = get_current_user_id();
  // 将购物车里的货品更新进去
  foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
    $quantity = $cart_item['quantity'];
    $sql = "UPDATE `t_cart_item_map`
            SET `order_id`=$order_id, `status`=2
            WHERE `cart_item_key`='$cart_item_key' AND `user_id`=$me
              AND `status`=0 AND `order_id`=0
            LIMIT $quantity";
    $check = $pdo->query($sql);
  }

}
add_action('woocommerce_checkout_order_processed', 'map_team_order', 10, 2);

// Add Password, Repeat Password and Are You Human fields to WordPress registration form
// http://wp.me/p1Ehkq-gn
add_action('register_form', 'xline_show_extra_register_fields');
function xline_show_extra_register_fields(){
  ?>
  <p>
    <label for="password">密码<br/>
      <input id="password" class="input" type="password" tabindex="30" size="25" name="password"
             placeholder="长度8~20个字符" />
    </label>
  </p>
  <p>
    <label for="repeat_password">重复密码<br/>
      <input id="repeat_password" class="input" type="password" tabindex="40" size="25" name="repeat_password" />
    </label>
  </p>
<?php
}

// Check the form for errors
add_action('register_post', 'xline_check_extra_register_fields', 10, 3);
function xline_check_extra_register_fields($login, $email, $errors) {
  if ($_POST['password'] !== $_POST['repeat_password']) {
    $errors->add('passwords_not_matched', "<strong>错误</strong>: 两次输入的密码不同");
  }
  if (strlen($_POST['password'] ) < 8) {
    $errors->add('password_too_short', "<strong>错误</strong>: 密码不能少于8个字符");
  }
}

add_action( 'user_register', 'xline_register_extra_fields', 100 );
function xline_register_extra_fields( $user_id ){
  $userdata = array();

  $userdata['ID'] = $user_id;
  if ( $_POST['password'] !== '' ) {
    $userdata['user_pass'] = $_POST['password'];
  }
  $new_user_id = wp_update_user( $userdata );
}

add_filter( 'gettext', 'xline_edit_password_email_text' );
function xline_edit_password_email_text ( $text ) {
  if ( $text == 'A password will be e-mailed to you.' ) {
    $text = '如果您此时不设密码，我们会将含有随机密码的邮件发至您的邮箱。';
  }
  return $text;
}
