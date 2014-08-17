<?php
$pdo = require_once(dirname(__FILE__) . '/../inc/pdo.php');

$p = (int)$_REQUEST['p'];
$id = (int)$_REQUEST['id'];

$sql = "SELECT `data$p`
        FROM `t_diy_detail`
        WHERE `id`=$id";
$json = $pdo->query($sql)->fetchColumn();

$json = json_decode($json, true);
foreach ($json['steps'] as $key => $step) {
  $step['color'] = dechex($step['color']);
  $step['color2'] = dechex($step['color2']);
  $json['steps'][$key] = $step;
}

header("Content-Type:application/json;charset=UTF-8");
echo json_encode($json);