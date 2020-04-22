<?php

use WHMCS\Database\Capsule;

function avang_mail_config() {
    $configarray = array(
        "name" => "AvangEmail, Email Delivery Service",
        "description" => "AvangEmail, Email Delivery Service for Marketers and Developers",
        "version" => "1.1",
        "author" => "<a href='https://avangemail.com/' target='_blank'><strong>AvangEmail</strong></a>",
        "language" => "english",
        'fields' => [
            'License' => [
                'FriendlyName' => 'License code',
                'Type' => 'text',
                'Size' => '48',
                'Default' => '',
                'Description' => 'No need for license code',
            ]
        ]
    );
    return $configarray;
}

function avang_mail_output($vars) {
    global $CONFIG;
    define('mname', basename(__DIR__));
    $D = new stdclass();
    $D->settings = AVANG_MAIL_HOOK\get_settings();
    $D->mlink = $vars['modulelink'];
    $D->murl = rtrim($CONFIG['SystemURL'], "/") . '/modules/addons/' . basename(__DIR__) . '/';


    $D->tabs = array(
        'settings' => array('fa fa-cogs', 'Settings'),
        'send' => array('fa fa-send', 'Send users'),
    );
    $D->tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
    $D->current = $D->mlink . '&tab=' . $D->tab;

    $D->here = __DIR__;

    /* @var D $D */
    $D->table_form = '<table class="form" width="100%" >';
    $D->td_label = '<td class="fieldlable" width="20%">';
    $D->td_area = '<td class="fieldarea" >';
    $D->close_table = '</table>';
    $D->close_td = '</td>';




    ob_start();
    include(__DIR__ . '/admin/output.php');
    echo ob_get_clean();
}

function avang_mail_activate() {
    $sql = "CREATE TABLE IF NOT EXISTS `mod_avang_mail_settings` (
  `word` varchar(64) COLLATE utf8_persian_ci NOT NULL,
  `value` text COLLATE utf8_persian_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_persian_ci NOT NULL,
  `help` text COLLATE utf8_persian_ci NOT NULL,
  `type` varchar(32) COLLATE utf8_persian_ci NOT NULL DEFAULT 'text',
  `ltr` tinyint(1) NOT NULL DEFAULT '0',
  `sorting` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci
/********#######SQL########*******/
INSERT INTO `mod_avang_mail_settings` (`word`, `value`, `label`, `help`, `type`, `ltr`, `sorting`) VALUES
('public_key', '', 'Public key', '', 'text', 1, 1),
('lists', '', 'Please insert list unique id', 'Email list unique id', 'text', 1, 3),
('private_key', '', 'Private key', '', 'text', 1, 2),
('host', 'https://send.avangemail.com/', 'Email host address', '', 'text', 1, 4),
('apikey', '', 'Send Email API', '', 'text', 1, 5)
/********#######SQL########*******/
ALTER TABLE `mod_avang_mail_settings` ADD UNIQUE KEY `word` (`word`)
/********#######SQL########*******/
INSERT INTO `mod_avang_mail_settings` (`word`, `value`, `label`, `help`, `type`, `ltr`, `sorting`) VALUES ('SMTP_ACTIVE', '1', 'Enable sending system emails', '', 'checkbox', '0', '6'), ('ARCHIVE_ACTIVE', '1', 'Enable email registration in the avangemail system', '', 'checkbox', '0', '7')";
    foreach (explode('/********#######SQL########*******/', $sql) as $s) {

        full_query($s);
    }
    return array('status' => 'success', 'description' => 'The module successfully activated');
}

function avang_mail_deactivate() {
    return array('status' => 'success', 'description' => 'The module successfully disabled');
}
