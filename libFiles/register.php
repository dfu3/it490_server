#!/usr/bin/php
<?php
function regUser($user, $pass, $db)
{
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
        $exists = TRUE;
    }
    

    if($exists == FALSE)
    {
        $q = "insert into users (password, username) values ('$pass', '$user')";
    }
    else
    {
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
        return "FAIL";
    }
}

function createPositionTable($userHash, $db)
{
    
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

?>