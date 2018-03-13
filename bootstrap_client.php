<?php

/**
 * eBot - A bot for match management for CS:GO
 * @license     http://creativecommons.org/licenses/by/3.0/ Creative Commons 3.0
 * @author      Julien Pardons <julien.pardons@esport-tools.net>
 * @version     3.0
 * @date        21/10/2012
 */
$check["php"] = (function_exists('version_compare') && version_compare(phpversion(), '7', '>='));
$check["mysql"] = extension_loaded('mysqli');
$check["spl"] = extension_loaded('spl');
$check["sockets"] = extension_loaded("sockets");

define('EBOT_DIRECTORY', __DIR__);
define('APP_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once 'steam-condenser.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'websocket' . DIRECTORY_SEPARATOR . 'websocket.client.php';

echo "
      ____        _
     |  _ \      | |
  ___| |_) | ___ | |_
 / _ \  _ < / _ \| __|
|  __/ |_) | (_) | |_
 \___|____/ \___/ \__|
 " . PHP_EOL;

echo "PHP Compatibility Test" . PHP_EOL;
echo "-----------------------------------------------------" . PHP_EOL;
echo "| PHP 7 or newer        -> required  -> " . ($check["php"] ? ("[\033[0;32m Yes \033[0m]" . phpversion()) : "[\033[0;31m No \033[0m]") . PHP_EOL;
echo "| Standard PHP Library  -> required  -> " . ($check["spl"] ? "[\033[0;32m Yes \033[0m]" : "[\033[0;31m No \033[0m]") . PHP_EOL;
echo "| MySQL                 -> required  -> " . ($check["mysql"] ? "[\033[0;32m Yes \033[0m]" : "[\033[0;31m No \033[0m]") . PHP_EOL;
echo "| Sockets               -> required  -> " . ($check["sockets"] ? "[\033[0;32m Yes \033[0m]" : "[\033[0;31m No \033[0m]") . PHP_EOL;
echo "-----------------------------------------------------" . PHP_EOL;

if (in_array(false, $check)) {
    echo "| Your php configuration missed, please make sure that you have all feature !" . PHP_EOL;
    echo '-----------------------------------------------------' . PHP_EOL;
    exit();
}

// better checking if timezone is set
if (!ini_get('date.timezone')) {
    $timezone = @date_default_timezone_get();
    echo '| Timezone is not set in php.ini. Please edit it and change/set "date.timezone" appropriately. '
    . 'Setting to default: \'' . $timezone . '\'' . PHP_EOL;
    echo '-----------------------------------------------------' . PHP_EOL;
    date_default_timezone_set($timezone);
}

// enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);
gc_enable();

function handleShutdown()
{
    global $webSocketProcess;

    if (PHP_OS == "Linux" || PHP_OS == "Darwin") {
        proc_terminate($webSocketProcess, 9);
    }

    $error = error_get_last();
    if (!empty($error)) {
        $info = "[SHUTDOWN] date: " . date("d.m.y H:m", time()) . " file: " . $error['file'] . " | ln: " . $error['line'] . " | msg: " . $error['message'] . PHP_EOL;
        file_put_contents(APP_ROOT . 'logs' . DIRECTORY_SEPARATOR . 'error.log', $info, FILE_APPEND);
    }
}

echo "| Registerung Shutdown function !" . PHP_EOL;
register_shutdown_function('handleShutdown');

echo '-----------------------------------------------------' . PHP_EOL;

error_reporting(E_ERROR);
\eBot\Application\ApplicationClient::getInstance()->run();
