<?php
$pdo = require_once(dirname(__FILE__) . '/../inc/pdo.php');
require_once(dirname(__FILE__) . '/../inc/API.class.php');
require_once(dirname(__FILE__) . '/../inc/Spokesman.class.php');

$api = new API(array(
  'fetch' => fetch,
  'update' => update,
  'create' => create,
  'delete' => delete,
));

function fetch() {
  global $pdo;
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

  Spokesman::say($json);
}

function update($args, $attr) {

}

function create($args, $attr) {

}

function delete($args) {
  $attr = array(
    'status' => 1,
  );
  update($args, $attr);
}