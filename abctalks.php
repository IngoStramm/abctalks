<?php

/**
 * Plugin Name: ABC Talks
 * Plugin URI: https://agencialaf.com
 * Description: Descrição do ABC Talks.
 * Version: 0.0.6
 * Author: Ingo Stramm
 * Text Domain: abctalks
 * License: GPLv2
 */

defined('ABSPATH') or die('No script kiddies please!');

define('ABC_TALK_DIR', plugin_dir_path(__FILE__));
define('ABC_TALK_URL', plugin_dir_url(__FILE__));

function abctalk_debug($debug)
{
    echo '<pre>';
    var_dump($debug);
    echo '</pre>';
}

require_once 'tgm/tgm.php';
require_once 'classes/classes.php';
require_once 'scripts.php';
require_once 'abctalks-functions.php';
require_once 'abctalks-settings.php';
require_once 'abctalks-shortcodes.php';
require_once 'abctalks-adminbar.php';

require 'plugin-update-checker-4.10/plugin-update-checker.php';
$updateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://raw.githubusercontent.com/IngoStramm/abctalks/master/info.json',
    __FILE__,
    'abctalks'
);
