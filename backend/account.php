<?php

use PHPMailer\PHPMailer\Exception as PHPMailerException;

require_once('utils.php');

if (!isset($_POST)){
  http_response_code(405);
  echo json_encode(['status' => 'KO', 'errorCode' => 'INVALID_METHOD']);
  return;
}
if (!validate_item('action', 'STRING')) return;
$action = strtoupper($_POST['action']);

/**
 * Utility function to generate verification email
 * @param int $user_id The user ID
 */
function generate_verification_email($user_id){
  $mail = initialize_smtp();
  $user = DB::table('users')->where('id', $user_id)->first();
  if ($mail === false || $user === false) return false;

  $verification_code = '';
  for ($i = 0; $i < 8; $i++) $verification_code .= random_int(0, 9);

  $mail->addAddress($user->email, $user->name);
  $mail->Subject = 'Verify your email for ' . getenv('APP_NAME');
  $mail->Body = 'Your email verification code is ' . $verification_code . '.\n\nPlease confirm the code at ' . (getenv('APP_URL') | 'http://localhost:8000') . '.';
  try {
    $mail->send();
    $verification_hash = hash('sha256', $verification_code, false);
    DB::table('users')->where('id', $user_id)->update([
      'verification_hash' => $verification_hash
    ]);
    return true;
  } catch (PHPMailerException $e){
    error_log($e);
    return false;
  }
}

/**
 * Creates a new account
 */
function sign_up(){
  if (!(validate_item('name', 'STRING') && validate_item('email', 'EMAIL') && validate_item('password', 'STRING'))) return;
  $name = $_POST['name'];
  $email = strtoupper($_POST['email']);
  $password = password($_POST['password']);
  
  $user_id = DB::table('users')->insertGetId([
    'email' => $email,
    'name' => $name,
    'password' => $password
  ]);

  echo json_encode([
    'status' => 'OK',
    'data' => jwt_generate($user_id)
  ]);
}

/**
 * Log in
 */
function log_in(){
  global $HTTP_HEADERS;

  if (isset($HTTP_HEADERS['Authorization']) && str_starts_with($HTTP_HEADERS['Authorization'], 'Bearer ')){
    $token = substr($HTTP_HEADERS['Authorization'], 7);
    try {
      $data = jwt_decode($token);
      $user = DB::table('users')->where('id', $data['data']['id']);
      if (!$user){
        http_response_code(401);
        echo json_encode(['error' => 'UNAUTHORIZED']);
        return;
      }
    } catch (Exception $pass){}
  }

  if (!(validate_item('email', 'EMAIL') && validate_item('password', 'STRING'))) return;
  $email = strtoupper($_POST['email']);
  $password = password($_POST['password']);

  $user = DB::table('users')->where('email', $email)->where('password', $password)->first();
  if (!$user){
    http_response_code(401);
    echo json_encode(['error' => 'UNAUTHORIZED']);
    return;
  }

  echo json_encode([
    'status' => 'OK',
    'data' => jwt_generate($user->id)
  ]);
}

/**
 * Request handler to request confirmation email
 */
function request_confirmation_email(){
  $user = require_jwt();
  if ($user === false) return false;
  $status = generate_verification_email($user['data']['id']);
  if (!$status){
    http_response_code(500);
    echo json_encode(['errorCode' => 'INTERNAL_SMTP']);
    return;
  }
  echo json_encode(['status' => 'OK']);
};

/**
 * Confirm email address
 */
function confirm_email(){
  $user = require_jwt();
  if ($user === false){
    http_response_code(401);
    echo json_encode(['error' => 'UNAUTHORIZED']);
  }
  if (!$_POST['code']) {
    http_response_code(401);
    echo json_encode(['error' => 'INVALID_CODE']);
  }
  $verification_code = $_POST['code'];
  $verification_hash = hash('sha256', $verification_code, false);
  $result = DB::table('users')->where('id', $user['data']['id'])->where('verification_hash', $verification_hash)->first();
  if (!$result) {
    http_response_code(401);
    echo json_encode(['error' => 'INVALID_CODE']);
    return;
  } else {
    echo json_encode(['status' => 'OK']);
    return;
  }
}

switch ($action){
  case 'SIGN_UP':
    sign_up();
    break;
  case 'LOG_IN':
    log_in();
    break;
  case 'REQUEST_CONFIRMATION_EMAIL':
    request_confirmation_email();
    break;
  case 'CONFIRM_EMAIL':
    confirm_email();
    break;
  default:
    http_response_code(400);
    echo json_encode(['error' => 'INVALID_ACTION']);
}
