#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$db = new mysqli("10.200.44.232","server","letMe1n","user_info");
$log = fopen( '/var/log/thump.log', 'a' );
print_r($log);

function now()
{
    return (new \DateTime())->format('Y-m-d H:i:s') . PHP_EOL;
}

function register($user, $pass, $db)
{
    fwrite($log, "==>BEGIN REGISTER<==". PHP_EOL);

    if ($db->connect_errno > 0 )
    {
        fwrite($log, __FILE__.__LINE__." ERROR: ".$db->connect_error.PHP_EOL);
        return $db->connect_error;
        exit(-1);
    }
    $pass = hash('sha256', $pass);
    $user = hash('sha256', $user);

    $q = "select * from users where username='$user'";
    if($db->query($q) == TRUE)
    {
        if(mysqli_num_rows($db->query($q)) >= 1 )
        {
            $exists = TRUE;
        }
        else
        {
            $exists = FALSE;
        }
            
    }
    else
    {
        fwrite($log, "~FAIL~ " . $db->error . PHP_EOL);
        $exists = TRUE;
    }
    

    if($exists == FALSE)
    {
        $q = "insert into users (password, username) values ('$pass', '$user')";
    }
    else
    {
        fwrite($log, "~FAIL~ user already exists" . PHP_EOL);
        return "FAIL";
    }
    if($db->query($q) == TRUE)
    {
        return "SUCC";
    }
    else
    {
        fwrite($log, "~FAIL~ " . $db->error . PHP_EOL);
        return "FAIL";
    }
    
}

function login($user, $pass, $db)
{
    fwrite($log, "==>BEGIN LOGIN<== | " . now());

    if ($db->connect_error > 0 )
    {
        fwrite ($log, "~FAIL~ " . $db->connect_error . PHP_EOL);
        return $db->connect_error;
        exit(-1);
    }

    $pass = hash('sha256', $pass);
    $user = hash('sha256', $user);
    
    $q = "select * from users where username='$user' and password='$pass'";
    if($db->query($q) == TRUE)
    {
        if(mysqli_num_rows($db->query($q)) == 1 )
        {
            return "SUCC";
        }
        else
        {
            fwrite($log, "~FAIL~ login fail" . PHP_EOL);
            return "FAIL";
        }
            
    }
    else
    {
        fwrite($log, "~FAIL~ " . $db->error . PHP_EOL);
        return "FAIL";
    }
    
}

function requestProcessor($request)
{
  global $db;
  
  fwrite($log, "==>RECEIVED REQUEST<==" . PHP_EOL);
  //var_dump($request);
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
  }
  //return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

//global $log;
$server = new rabbitMQServer("testRabbitMQ.ini","testServer");
fwrite($log, "==>BEGIN LOG<== | ". now());  
$server->process_requests('requestProcessor');
$db->close();
fwrite($log, "==>END LOG<== | ". now()) . PHP_EOL;
fclose($log);
exit();
?>

