<?php

require_once('utils.php');

// GET current BMI list
if (isset($_GET)){
  $user = require_jwt();
  if (!$user){
    http_response_code(401);
    echo json_encode(['error' => 'UNAUTHORIZED']);
    return;
  }

  $data = DB::table('reports')->where('user_id', $user['data']['id'])->get();

  echo json_encode(['status' => 'OK', 'data' => $data]);
}

if (isset($_POST)){
  $user = require_jwt();
  if (!$user){
    http_response_code(401);
    echo json_encode(['error' => 'UNAUTHORIZED']);
    return;
  }
  if (!$_POST['height'] || !ctype_digit($_POST['height'])){
    http_response_code(400);
    echo json_encode(['error' => 'INVALID_HEIGHT']);
    return;
  }
  if (!$_POST['weight'] || !ctype_digit($_POST['weight'])){
    http_response_code(400);
    echo json_encode(['error' => 'INVALID_WEIGHT']);
    return;
  }

  $new_id = DB::table('reports')->insertGetId([
    'user_id' => $user['data']['id'],
    'height' => $_POST['height'],
    'weight' => $_POST['weight'],
  ]);

}