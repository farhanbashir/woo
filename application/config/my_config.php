<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$config["google_key"] = 'AIzaSyA1F-p0YGdeqzEA5V49WbFOqAxn1Rj-1T4';
$config["REMOTE_SOCKET_GOOGLE"] = 'https://android.googleapis.com/gcm/send';
$config["PASS_PHRASE"] = "12345678";
$config["REMOTE_SOCKET_APPLE"] = 'ssl://gateway.push.apple.com:2195';
$config['pagination_limit'] = 10;
$config["pem"] = '\assets\files\ck.pem';
$config["admin_emails"] = "farhan.bashir2002@gmail.com,syed.sami@createmedia-group.com";
$config['default_email'] = "admin@woo.com";
$config["allowed_calls_without_token"] = array("login","forgetPassword","signup", "facebookLogin");
$config["receiption_phone"] = "026731111";
$config["android_notification_title"] = "The-Woo";