<?php
require_once('db.php');

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

header('Content-Type: application/json');

$BCRYPT_COST = 10;
$HTTP_HEADERS = apache_request_headers();
$REGEX_DATETIME = '/^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d)$/';
$REGEX_EMAIL = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix';

/**
 * Utility function to initialize PHPMailer object
 * @return PHPMailer\PHPMailer|boolean "false" if error, or a pure PHPMailer object if success
 */
function initialize_smtp(){
  // Setup PHPMailer
  $mail = ($_ENV['SMTP_ENABLED'] == true) ? new PHPMailer\PHPMailer(true) : false;
  try {
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->Port = intval($_ENV['SMTP_PORT']);
    $mail->SMTPAuth = true;
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    $mail->setFrom($_ENV['SMTP_USERNAME'], $_ENV['SMTP_SENDER_NAME']);
  } catch (PHPMailerException $e){
    $mail = false;
  }
  return $mail;
}

/**
 * Utility function to decode the given JWT token
 * @param string $token The JWT token
 * @return array The JWT's payload as a PHP array
 * @throws InvalidArgumentException Provided JWT was empty
 * @throws UnexpectedValueException Provided JWT was invalid
 * @throws SignatureInvalidException Provided JWT was invalid because the signature verification failed
 * @throws BeforeValidException Provided JWT is trying to be used before it's eligible as defined by 'nbf'
 * @throws BeforeValidException Provided JWT is trying to be used before it's been created as defined by 'iat'
 * @throws ExpiredException Provided JWT has since expired, as defined by the 'exp' claim
 */
function jwt_decode($token){
  return json_decode(json_encode(JWT::decode($token, new Key($_ENV['JWT_SECRET_TOKEN'], 'HS256'))), true);
}

/**
 * Utility function to generate JWT based on User ID
 * @param int $user_id The user ID
 * @return string The JWT string
 */
function jwt_generate($user_id){
  $user = DB::table('users')->where('id', $user_id)->first();
  if (!$user) return false;
  unset($user->password);

  // TODO: Insert JWT integration
  $payload = [
    'data' => json_decode(json_encode($user), true),
    'iss' => $_ENV['APP_URL'],
    'iat' => time(),
    'exp' => time() + (30 * 24 * 60 * 60)
  ];

  return JWT::encode($payload, $_ENV['JWT_SECRET_TOKEN'], 'HS256');  
}

/**
 * Utility function to hash password
 * @param string $password The plaintext password string
 * @return string The hashed password
 */
function password($password){
  global $BCRYPT_COST;
  return password_hash($password, PASSWORD_BCRYPT, ['cost' => $BCRYPT_COST]);
}

/**
 * Utility function to always require a valid JWT
 * @return boolean|array The JWT payload as PHP array, or "false" if missing or invalid
 */
function require_jwt(){
  global $HTTP_HEADERS;
  if (!isset($HTTP_HEADERS['Authorization']) || !str_starts_with($HTTP_HEADERS['Authorization'], 'Bearer ')){
    http_response_code(401);
    echo json_encode(['errorCode' => 'INVALID_JWT']);
    return false;
  }
  $token = substr($HTTP_HEADERS['Authorization'], 7);
  try {
    return jwt_decode($token);
  } catch (BeforeValidException $e){
    http_response_code(401);
    echo json_encode(['errorCode' => 'INVALID_JWT']);
    return false;
  } catch (ExpiredException $e){
    http_response_code(401);
    echo json_encode(['errorCode' => 'INVALID_JWT']);
    return false;
  } catch (SignatureInvalidException $e){
    http_response_code(401);
    echo json_encode(['errorCode' => 'INVALID_JWT']);
    return false;
  }
}

/**
 * Utility function to validate parameters
 * @param string $param The parameter name
 * @param string $data_type The expected data type
 * @return bool "false" if not validated or else, "true"
 */
function validate_item($param, $data_type){
  global $REGEX_DATETIME;
  global $REGEX_EMAIL;

  $param = strtolower($param);
  $data_type = strtoupper($data_type);
  $validated = isset($_POST[$param]) && is_string($_POST[$param]) && strlen($_POST[$param]) > 0;
  
  switch ($data_type){
    case 'DATETIME':
      $validated = $validated && preg_match($REGEX_DATETIME, $_POST[$param]);
      break;
    case 'EMAIL':
      $validated = $validated && preg_match($REGEX_EMAIL, $_POST[$param]);
      break;
    case 'INTEGER':
    case 'NUMBER':
      $validated = $validated && ctype_digit($validated);
      break;
  }

  if (!$validated){
    http_response_code(400);
    echo json_encode(['error' => 'INVALID_' . strtoupper($param)]);
  }
  
  return $validated;
}

/**
 * Utility function to calculate the BMI index
 * @param int $weight the user weight in hectograms
 * @param int $height the user height in centimeters
 */
function calculate_bmi($weight, $height){
  $weight = $weight / 10;
  $height = $height / 100;
  return ceil($weight / $height / $height * 10) / 10;
}
