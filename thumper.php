#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require('trade.php');

$db = new mysqli("10.200.45.127","server","letMe1n","user_info");
$log = fopen( 'thump.log', 'a' );

function now()
{
    return (new \DateTime())->format('Y-m-d H:i:s') . PHP_EOL;
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
    
    $positions = array();

    $q = "select * from $user;";
    if($db->query($q) == TRUE)
        {
            $res = $db->query($q);

            $table = '<table>';
            $table.= '<tr> <th> Currency </th> <th> Position </th> </tr>';

            while($r = $res->fetch_array(MYSQLI_ASSOC))
                {
                    $table.= '<tr> <td> ' . $r['currency'] . ' </td> <td> ' . $r['position'] . '</td> </tr>';
                }
            $table.= '</table>';

            fwrite($log, "==>EXIT getUserPos<== | " . now());
            
            return $table;
        }
}

function getExFor($base)
{
    global $db;
    global $log;

    fwrite($log, "==>BEGIN getExFor<== | " . now());
    
    $rates = array();

    $q = "select * from exchange where currency_1='" . $base ."' or currency_2='" . $base ."';";
    if($db->query($q) == TRUE)
        {
            $res = $db->query($q);
            while($r = $res->fetch_array(MYSQLI_ASSOC))
                {
                    if($r['currency_1'] == $base)
                        {
                            $rates[$r['currency_2']] = $r['rate'];
                        }
                    else
                        {
                            $rate = $r['rate'];
                            $rate = floatval($rate);
                            $rate = (1/$rate);

                            $rates[$r['currency_1']] = ($rate);
                        }
                }

            $table = '<table>';
            $table.= '<tr> <th> Currency </th> <th> Rate </th> </tr>';

            foreach($rates as $curr=>$exRate)
                {
                    $table.= '<tr> <td> ' . $curr . ' </td> <td> ' . $exRate . '</td> </tr>';
                }
            $table.= '</table>';
           
            fwrite($log, "==>EXIT getExFor<== | " . now());
            
            return $table;

        }

}        

function createPositionTable($userHash, $db)
{
    global $log;
    
    fwrite($log, "==>BEGIN createPosition<== | " . now());

    if ($db->connect_error > 0 )
    {
        fwrite ($log, "~FAIL~ " . $db->connect_error . PHP_EOL);
        return $db->connect_error;
        exit(-1);
    }

    $q = "create table " . $userHash  . " (id int(3) auto_increment primary key, currency varchar(3), position varchar(255)); ";

    if($db->query($q) == TRUE)
    {
        $q = "insert into " . $userHash . " (currency, position) values ('USD', '50.000');";
        
        if($db->query($q) != TRUE)
        {
            fwrite($log, "~FAIL~ " . $db->error . PHP_EOL);
            return "FAIL";
        }
        else
        {
            return "SUCC";
        }
    }
    else
    {
        fwrite($log, "~FAIL~ " . $db->error . PHP_EOL);
        return "FAIL";
    }
}

function register($user, $pass, $db)
{
    global $log;
    
    fwrite($log, "==>BEGIN REGISTER<==". PHP_EOL);

    if ($db->connect_errno > 0 )
    {
        fwrite($log, __FILE__.__LINE__." ERROR: ".$db->connect_error.PHP_EOL);
        return $db->connect_error;
        exit(-1);
    }
    $pass = hash('sha256', $pass);
    //$user = hash('sha256', $user);

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
        
        $result =  createPositionTable($user, $db);
        if($result == "SUCC")
        {
            return "SUCC";
        }
    }
    else
    {
        fwrite($log, "~FAIL~ " . $db->error . PHP_EOL);
        return "FAIL";
    }
    
}

function login($user, $pass, $db)
{
    global $log;
    
    fwrite($log, "==>BEGIN LOGIN<== | " . now());

    if ($db->connect_error > 0 )
    {
        fwrite ($log, "~FAIL~ " . $db->connect_error . PHP_EOL);
        return $db->connect_error;
        exit(-1);
    }

    $pass = hash('sha256', $pass);
    //$user = hash('sha256', $user);
    
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
  global $log;
  fwrite($log, "==>RECEIVED REQUEST<==" . PHP_EOL);
  print('REQ recieved'.PHP_EOL);
  
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

