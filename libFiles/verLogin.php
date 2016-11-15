#!/usr/bin/php
<?php

function verLogin($user, $pass, $db)
{
     if ($db->connect_error > 0 )
    {
        //fwrite ($log, "~FAIL~ " . $db->connect_error . PHP_EOL);
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
            //fwrite($log, "~FAIL~ login fail" . PHP_EOL);
            return "FAIL";
        }
            
    }
    else
    {
        //fwrite($log, "~FAIL~ " . $db->error . PHP_EOL);
        return "FAIL";
    }
}

?>