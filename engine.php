<?php
include('ringcentral.php');
session_start();
$rc = new RingCentral();

if (isset($_REQUEST['oauth2callback'])){
  if (!isset($_GET['code'])) {
      return;
  }
  $rc->authenticate($_GET['code']);
  header("Location: http://localhost:5000/test.html");
}

if (!isset($_SESSION['sessionAccessToken'])) {
    header("Location: http://localhost:5000");
    exit();
}else{

    if (isset($_REQUEST['logout'])){
        callPostRequest($rc, '/restapi/oauth/revoke', null);
        unset($_SESSION['sessionAccessToken']);
        header("Location: http://localhost:5000");
        exit();
    }elseif (isset($_REQUEST['api'])){
        if ($_REQUEST['api'] == "extension") {
            $endpoint = "/restapi/v1.0/account/~/extension";
            callGetRequest($rc, $endpoint, null);
        }elseif ($_REQUEST['api'] == "extension-call-log") {
            $endpoint = "/restapi/v1.0/account/~/extension/~/call-log";
            $params = array(
                'fromDate' => '2022-08-01T00:00:00.000Z',
              );
            callGetRequest($rc, $endpoint, $params);
        }elseif ($_REQUEST['api'] == "account-call-log") {
            $endpoint = "/restapi/v1.0/account/~/call-log";
            $params = array(
                'fromDate' => '2022-08-01T00:00:00.000Z',
              );
            callGetRequest($rc, $endpoint, $params);
        }
    }
}

function callGetRequest($rc, $endpoint, $params){
  try {
    $resp = $rc->get($endpoint, $params);
    echo "<p>".$resp."</p>";
  } catch (Exception $e) {
    print 'Expected HTTP Error: ' . PHP_EOL;
  }
}

function callPostRequest($rc, $endpoint, $params){
  try {
    $resp = $rc->post($endpoint, $params);
    echo "<p>".$resp."</p>";
  } catch (Exception $e) {
    print 'Expected HTTP Error: ' . PHP_EOL;
  }
}
?>
