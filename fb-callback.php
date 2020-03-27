<?php
session_start();

// Include the required dependencies.
require_once('vendor/autoload.php');
require_once('config.php');
$fb = new Facebook\Facebook([
    'app_id'                => $fb_app_id,
    'app_secret'            => $fb_app_secret,
    'default_graph_version' => $fb_version,

]);

$helper = $fb->getRedirectLoginHelper();

if(isset($_GET['state'])) {
    $helper->getPersistentDataHandler()->set('state', $_GET['state']);
}

try {
  $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  // When validation fails or other local issues
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

if (! isset($accessToken)) {
  if ($helper->getError()) {
    header('HTTP/1.0 401 Unauthorized');
    echo "Error: " . $helper->getError() . "\n";
    echo "Error Code: " . $helper->getErrorCode() . "\n";
    echo "Error Reason: " . $helper->getErrorReason() . "\n";
    echo "Error Description: " . $helper->getErrorDescription() . "\n";
  } else {
    header('HTTP/1.0 400 Bad Request');
    echo 'Bad request';
  }
  exit;
}
 
// Logged in
echo '<h3>Access Token</h3>';  var_dump($accessToken->getValue());

// The OAuth 2.0 client handler helps us manage access tokens
$oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
$tokenMetadata = $oAuth2Client->debugToken($accessToken);
//echo '<h3>Metadata</h3>';  var_dump($tokenMetadata);

// Validation (these will throw FacebookSDKException's when they fail)
$tokenMetadata->validateAppId($fb_app_id);
// If you know the user ID this access token belongs to, you can validate it here
//$tokenMetadata->validateUserId('123');
$tokenMetadata->validateExpiration();

if (! $accessToken->isLongLived()) {
  // Exchanges a short-lived access token for a long-lived one
  try {
    $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
  } catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
    exit;
  }
 
  echo '<h3>Long-lived</h3>';
  var_dump($accessToken->getValue());
}

$_SESSION['fb_access_token'] = (string) $accessToken;

$fb->setDefaultAccessToken($accessToken);
 
# These will fall back to the default access token
echo "<hr />";
$res = $fb->get('/me?fields=id,name,email');
var_dump($res->getDecodedBody());
$user = $res->getGraphObject();
echo $user->getProperty( 'id' );
echo $user->getProperty( 'name' );
echo $user->getProperty( 'email' );
echo "<hr />";

$res = $fb->get( '/me/picture?type=large&amp;amp;redirect=false' );
$picture = $res->getGraphObject();
var_dump( $picture );