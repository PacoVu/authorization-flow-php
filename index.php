<?php
require_once(__DIR__ . '/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createMutable(__DIR__, '.env');
$dotenv->load();


$env_file = "./environment/";
if ($_ENV['ENVIRONMENT'] == "sandbox"){
  $env_file .= ".env-sandbox";
}else{
  $env_file .= ".env-production";
}

$dotenv = Dotenv\Dotenv::createMutable(__DIR__, $env_file);
$dotenv->load();

$redirectUri = $_ENV["RC_SERVER_URL"] . '/restapi/oauth/authorize?';
$redirectUri .= 'client_id='.$_ENV["RC_CLIENT_ID"];
$redirectUri .= '&redirect_uri='.$_ENV["RC_REDIRECT_URL"];
$redirectUri .= '&response_type=code&prompt=login';
//session_start();
?>
<!DOCTYPE html>
<html>
  <head>
      <meta charset="UTF-8">
      <title>RingCentral Authorization Code Flow Authentication</title>
  </head>
  <body>
    <h2>
      RingCentral Authorization Code Flow Authentication DEMO
    </h2>
    <a href="<?php echo($redirectUri); ?>">Login RingCentral Account</a>
  </body>
</html>
