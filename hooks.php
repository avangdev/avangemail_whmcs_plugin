<?php

namespace AVANG_MAIL_HOOK;

use WHMCS\Database\Capsule;
use WHMCS\ClientArea;
use AvangPhpApi\Base;
use AvangPhpApi\ComposeMessage;

/* function pr($s){
  ob_start();
  echo '<pre style="direction:ltr;text-align:left;padding:12px;font-family:tahoma;background:#f6f6f6;"  >';
  print_r($s);
  echo '</pre>';

  echo ob_get_clean();
  } */

function get_settings() {

    $c = Capsule::table('mod_avang_mail_settings')->orderBy('sorting', 'ASC')->get();

    if (empty($c)) {
        return false;
    }
    $o = array();
    foreach ($c as $v) {

        $o[$v->word] = $v;
    }

    return $o;
}

function get($index = "", $html_escape = true) {
    if ($index) {
        $o = (isset($_GET[$index])) ? trim(mysql_real_escape_string($_GET[$index])) : "";

        return ($html_escape ? htmlspecialchars($o) : $o);
    }
    return "";
}

function post($index = "", $html_escape = true) {
    if ($index) {
        $o = (isset($_POST[$index])) ? trim(mysql_real_escape_string($_POST[$index])) : "";

        return ($html_escape ? htmlspecialchars($o) : $o);
    }
    return "";
}

function obclean() {

    if (ob_get_length()){
        ob_end_clean();
    }
}

function send_mail($subject, $message, $to) {
    global $CONFIG;

    $settings = get_settings();
    $private_key = '';
    $public_key = '';
    if ($settings && isset($settings['private_key'], $settings['public_key'])) {
        $private_key = $settings['private_key']->value;
        $public_key = $settings['public_key']->value;
    }
    require_once(__DIR__ . '/api/AvangSendApi/autoload.php');


    $host = $settings['host']->value; //  host address
    $key = $settings['apikey']->value;  // example key
    $base = new Base($host, $key);
    $composeMessage = new ComposeMessage($base);
    $composeMessage->to($to);
    $composeMessage->from($CONFIG['SystemEmailsFromName'] . '<' . $CONFIG['SystemEmailsFromEmail'] . '>');
    $composeMessage->sender($CONFIG['SystemEmailsFromEmail']);
    $composeMessage->subject($subject);
    $composeMessage->replyTo($CONFIG['SystemEmailsFromEmail']);
    $composeMessage->plainBody(strip_tags($message));
    $composeMessage->htmlBody($message);
//$composeMessage->attach('test.png', 'application/octet-stream', 'test');
    $result = $composeMessage->send();



    return $result;
}

function & list_obj() {
    $settings = get_settings();
    $private_key = '';
    $public_key = '';
    if ($settings && isset($settings['private_key'], $settings['public_key'])) {
        $private_key = $settings['private_key']->value;
        $public_key = $settings['public_key']->value;
    }
    require_once(__DIR__ . '/api/AvangEmailApi/autoload.php');

    $avang_api_list_config = new \AvangEmailApi_Config(array(
        'publicKey' => $public_key,
        'privateKey' => $private_key,
        'components' => array(
            'cache' => array(
                'class' => 'AvangEmailApi_Cache_File',
                'filesPath' => __DIR__ . 'api/cache', // make sure it is writable by webserver
            )
        ),
    ));
    \AvangEmailApi_Base::setConfig($avang_api_list_config);
    $avang_api_list = new \AvangEmailApi_Endpoint_ListSubscribers();

    return $avang_api_list;
}

add_hook('ClientAdd', 1, function($vars) {
    $settings = get_settings();
    if ((int) $settings['ARCHIVE_ACTIVE'] != 1) {
        return $vars;
    }
    $AL = & \AVANG_MAIL_HOOK\list_obj();
    $response = $AL->create($settings['lists']->value, array(
        'EMAIL' => $vars['email'],
        'FNAME' => $vars['firstname'],
        'LNAME' => $vars['lastname']
    ));
});

add_hook('ClientEdit', 1, function($vars) {


    $settings = get_settings();
    if ((int) $settings['ARCHIVE_ACTIVE'] != 1) {
        return $vars;
    }
    $AL = & \AVANG_MAIL_HOOK\list_obj();
    $response = $AL->create($settings['lists']->value, array(
        'EMAIL' => $vars['email'],
        'FNAME' => $vars['firstname'],
        'LNAME' => $vars['lastname']
    ));
});

add_hook('EmailPreSend', 1, function($vars) {


    $settings = get_settings();
    if ((int) $settings['SMTP_ACTIVE'] != 1) {
        return $vars;
    }

    global $CONFIG;
    global $smarty;
    /*
     * EmailCSS
     * EmailGlobalHeader
     * EmailGlobalFooter
     */

    define('CLIENTAREA', true);
    $ca = new ClientArea();
    $ca->initPage();

    $t = Capsule::table('tblemailtemplates')->where('name', '=', $vars['messagename'])->first();
    if (!$t) {
        return $vars;
    }



    foreach ($vars['mergefields'] as $k => $v) {

        $smarty->assign($k, $v);
    }
    $message = '';
    $m = htmlspecialchars_decode($CONFIG['EmailGlobalHeader']);
    $m .= $t->message;
    $m .= htmlspecialchars_decode($CONFIG['EmailGlobalFooter']);
    ob_start();

    $smarty->display("string:" . $m);
    $message = ob_get_clean();

    $message = str_replace('[EmailCSS]', $CONFIG['EmailCSS'], $message);

    foreach ($vars['mergefields'] as $k => $v) {

        $smarty->assign($k, $v);
    }
    $subject = '';

    ob_start();

    $smarty->display("string:" . $t->subject);
    $subject = ob_get_clean();

    \AVANG_MAIL_HOOK\send_mail($subject, $message, $vars['mergefields']['client_email']);


    $merge_fields['abortsend'] = true;


    return $merge_fields;
});
