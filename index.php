<?php

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

include_once ("mbus_client.php");
include_once ("mbus_utils.php");

mbus_utils::logToTerminal(); // View Browser in Source mode to get nice formatting.
//mbus_utils::turnLogOff();
//mbus_utils::mylog("Start\n");

/**
 * Set to true for testing, it will not bother connecting, it will just read a hard coded string of byte data.
 */
$testmode = true;

$mbusClient = new mbus_client();
$mbusClient->run($testmode);

//mbus_utils::mylog("Finshed.\n");
