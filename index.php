<?php
session_start();
require_once('vendor/autoload.php');
require_once('config.php');
// Initialize the Facebook PHP SDK v5.
$fb = new Facebook\Facebook([
    'app_id'                => $fb_app_id,
    'app_secret'            => $fb_app_secret,
    'default_graph_version' => $fb_version,

  ]);

  $helper = $fb->getRedirectLoginHelper();


  $permissions = ['email']; // Optional permissions
  $loginUrl = $helper->getLoginUrl($fb_callback_url, $permissions);
  echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';