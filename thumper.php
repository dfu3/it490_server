#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require('libFiles/trade.php');
require('libFiles/exRatesForBase.php');
require('libFiles/register.php');
require('libFiles/userPos.php');
require('libFiles/verLogin.php');

$db = new mysqli("10.200.173.26","server","letMe1n","user_info");
$log = fopen( 'thump.log', 'a' );

function now()
{
    return (new \DateTime())->format('Y-m-d H:i:s') . PHP_EOL;
}

function currList()
{
    global $db;
    global $log;

    $q = "select currency, description from currency;";
    $res = $db->query($q);
    print_r($res);
    return $res;
}

function makeTrade($user, $curr1, $amount, $curr2)
{
    global $db;
    global $log;

    fwrite($log, "==>BEGIN makeTrade<== | " . now());
    return trade($user, $curr1, $amount, $curr2, $db);
}
function getUserPos($user)
{
    global $db;
    global $log;

    fwrite($log, "==>BEGIN getUserPos<== | " . now());
    $ret = userPos($user, $db);
    fwrite($log, "==>EXIT getUserPos<== | " . now());
    return $ret;
}

function getExFor($base)
{
    global $db;
    global $log;

    fwrite($log, "==>BEGIN getExFor<== | " . now());
    $ret = exRatesForBase($base);
    fwrite($log, "==>EXIT getExFor<== | " . now());

    return $ret;
}        

function register($user, $pass, $db)
{
    global $log;
    
    fwrite($log, "==>BEGIN REGISTER<==". PHP_EOL);
    $ret = regUser($user, $pass, $db);
    fwrite($log, "==>EXIT REGISTER<==". PHP_EOL);

    return $ret;
}

function login($user, $pass, $db)
{
    global $log;
    
    fwrite($log, "==>BEGIN LOGIN<== | " . now());
    $ret = verLogin($user, $pass, $db);
    fwrite($log, "==EXIT LOGIN<== | " . now());

    return $ret;
}

function requestProcessor($request)
{
  global $db;
  global $log;
  fwrite($log, "==>RECEIVED REQUEST<==" . PHP_EOL);
  
  if(!isset($request['type']))
  {
      fwrite($log, "~FAIL~  unsupported message type");
  }
  switch ($request['type'])
  {
    case "login":
        return login($request['username'],$request['password'], $db);
    case "register":
        return register($request['username'],$request['password'], $db);
    case "validate_session":
        return doValidate($request['sessionId']);
    case "get_ex_for_base":
        return (getExFor($request['base']));
    case "get_user_pos":
        return getUserPos($request['username']);
    case "make_trade":
        return makeTrade($request['username'], $request['curr1'], $request['amount'], $request['curr2']);
    case "get_curr_list":
        return currList();
  }
 
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");
fwrite($log, "==>BEGIN LOG<== | ". now());  
$server->process_requests('requestProcessor');
$db->close();
fwrite($log, "==>END LOG<== | ". now()) . PHP_EOL;
fclose($log);
exit();
?>

