<?php
require_once('vendor/autoload.php');

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

class RingCentral {
    function __construct() {}

    private function get_access_token(){
      if (isset($_SESSION['sessionAccessToken'])){
          $tokensObj = json_decode($_SESSION['sessionAccessToken']);
          $date = new DateTime();
          $expire_time= $date->getTimestamp() - $tokensObj->timestamp;
          if ($expire_time < $tokensObj->tokens->expires_in){
              // Access token is still valid => Use it
              return $tokensObj->tokens->access_token;
          }else if ($expire_time <  $tokensObj->tokens->refresh_token_expires_in) {
              // Refresh token is valid => Get new access token using the refresh token
              $accessToken = $this->refresh_token($tokensObj->tokens->refresh_token);
              return $accessToken;
          }else{
              // Refresh token expired => Ask a user to login
              header("Location: http://localhost:5000");
              exit();
          }
      }else{
        // Not yet authenticated => Ask a user to login
        header("Location: http://localhost:5000");
        exit();
      }
    }

    public function authenticate($code){
      $url = $_ENV["RC_SERVER_URL"] . "/restapi/oauth/token";
      $basic = $_ENV["RC_CLIENT_ID"] .":". $_ENV["RC_CLIENT_SECRET"];
      $headers = array (
              'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
              'Accept: application/json',
              'Authorization: Basic '.base64_encode($basic)
            );
      $body = http_build_query(array (
              'grant_type' => 'authorization_code',
              'code' => $code,
              'redirect_uri' => $_ENV["RC_REDIRECT_URL"]
            ));
      try {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $strResponse = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        if ($curlErrno) {
          throw new Exception($curlErrno);
        } else {
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);
          if ($httpCode == 200) {
            $date = new DateTime();
            $jsonObj = json_decode($strResponse);
            $tokensObj = array(
              "tokens" => $jsonObj,
              "timestamp" => $date->getTimestamp()
            );
            // Logged in successfully => Save tokens in session or write to file/database for reuse
            $_SESSION['sessionAccessToken'] =  json_encode($tokensObj, JSON_PRETTY_PRINT);
            return;
          }else{
            throw new Exception($strResponse);
          }
        }
      } catch (Exception $e) {
        throw $e;
      }
    }

    private function refresh_token($refreshToken){
      $url = $_ENV["RC_SERVER_URL"] . "/restapi/oauth/token";
      $basic = $_ENV["RC_CLIENT_ID"] .":". $_ENV["RC_CLIENT_SECRET"];
      $headers = array (
          'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
          'Accept: application/json',
          'Authorization: Basic ' . base64_encode($basic)
        );
        $body = http_build_query(array (
          'grant_type' => 'refresh_token',
          'refresh_token' => $refreshToken
        ));
        try {
          $ch = curl_init($url);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_POST, TRUE);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
          curl_setopt($ch, CURLOPT_TIMEOUT, 600);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

          $strResponse = curl_exec($ch);
          $curlErrno = curl_errno($ch);
          if ($curlErrno) {
            throw new Exception($curlErrno);
          } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpCode == 200) {
              $date = new DateTime();
              $jsonObj = json_decode($strResponse);
              $tokensObj = array(
                "tokens" => $jsonObj,
                "timestamp" => $date->getTimestamp()
              );
              // Refresh token successfully => Save new tokens in session or write to file/database for reuse
              $_SESSION['sessionAccessToken'] = json_encode($tokensObj, JSON_PRETTY_PRINT);
              return $jsonObj->access_token;
            }else{
              throw new Exception($strResponse);
            }
          }
        } catch (Exception $e) {
          throw $e;
        }
    }

    public function get($endpoint, $params=null, $callback=""){
        try {
            $accessToken =  $this->get_access_token();
            $url = $_ENV["RC_SERVER_URL"] . $endpoint;
            if ($params != null)
              $url .= "?".http_build_query($params);
            $headers = array (
                  'Accept: application/json',
                  'Authorization: Bearer ' . $accessToken
                );
            try {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 600);

                $strResponse = curl_exec($ch);
                $curlErrno = curl_errno($ch);
                if ($curlErrno) {
                    throw new Exception($ecurlError);
                } else {
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($httpCode == 200) {
                      return ($callback == "") ? $strResponse : $callback($strResponse);
                    }else{
                        throw new Exception($strResponse);
                    }
                }
            } catch (Exception $e) {
                throw $e;
            }
        }catch (Exception $e) {
            throw $e;
        }
    }

    public function post($endpoint, $params=null, $callback=""){
        try {
            $accessToken = $this->get_access_token();
            $url = $_ENV["RC_SERVER_URL"] . $endpoint;
            $body = array();
            if ($params != null)
                $body = json_encode($params);

            $headers = array (
                  'Content-Type: application/json',
                  'Accept: application/json',
                  'Authorization: Bearer ' . $accessToken
                );
            try {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 600);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

                $strResponse = curl_exec($ch);
                $curlErrno = curl_errno($ch);
                if ($curlErrno) {
                    throw new Exception($curlErrno);
                } else {
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($httpCode == 200) {
                        return ($callback == "") ? $strResponse : $callback($strResponse);
                    }else{
                        throw new Exception($strResponse);
                    }
                }
            }catch (Exception $e) {
                throw $e;
            }
        }catch (Exception $e) {
            throw $e;
        }
    }
}
