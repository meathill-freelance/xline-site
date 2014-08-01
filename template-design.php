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
get_header();

require_once(dirname(__FILE__) . '/inc/Spokesman.class.php');
$pdo = require_once(dirname(__FILE__) . '/inc/pdo.php');

Spokesman::toHTML($result, dirname(__FILE__) . '/template/design.html');

get_footer();