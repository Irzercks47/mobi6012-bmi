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
  
  $items = 15;
  if (isset($_GET['items']) && ctype_digit($_GET['items']) && intval($_GET['items']) > 0) $items = intval($_GET['items']);

  $page = 1;
  if (isset($_GET['page']) && ctype_digit($_GET['page']) && intval($_GET['page']) > 0) $items = intval($_GET['page']);

  $skip = $items * ($page - 1);

  $data = DB::table('reports')->skip($skip)->take($items)->where('user_id', $user['data']['id'])->orderBy('timestamp_created', 'DESC')->get();

  for ($data in $report) $report->bmi = calculate_bmi($report->weight, $report->height);

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
    'weight' => $_POST['weight']
  ]);
  $report = DB::table('reports')->where('id', $new_id)->first();
  $report->bmi = calculate_bmi($report->weight, $report->height);
  
  echo json_encode(['status' => 'OK', 'data' => $report]);
  return;
}
