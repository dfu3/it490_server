#!/usr/bin/php
<?php

$db = new mysqli("10.200.45.127","server","letMe1n","user_info");



function getExFor($base)
{
    global $db;
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
                            $rate = int($rate);
                            $rate = (1/$rate);

                            $rates[$r['currency_1']] = string($rate);
                        }
                }

            return $rates;

        }

}

$arr = getExFor('EUR');

$table = '<table>';
$table.= '<tr> <th> Currency </th> <th> Rate </th> </tr>';

foreach($arr as $curr=>$rate)
    {
        $table.= '<tr> <td> ' . $curr . ' </td> <td> ' . $rate . '</td> </tr>';
    }
$table.= '</table>';

function getUserPos($user)
{
    global $db;
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

            return $table;
        }
}

function trade($user, $curr1, $amount, $curr2)
{
    global $db;
    $q = "select * from $user;";

    if($db->query($q) == TRUE)
        {
            $pos = $db->query($q);
            #$posArr = $pos->fetch_array(MYSQLI_ASSOC);
            
            $fromExist = false;
            $toExist = false;

            while($r = $pos->fetch_array(MYSQLI_ASSOC))
                {
                    
                    if($r['currency'] == $curr1)
                        {
                            $fromExist = true;
                            $fromPos = $r['position'];
                        }

                    if($r['currency'] == $curr2)
                        {
                            $toExist = true;
                            $toPos = $r['position'];
                        }
                }
            mysqli_data_seek($pos, 0);
            if($fromExist == false) {return "no money in that curr";}

            while($pr = $pos->fetch_array(MYSQLI_ASSOC))
                {
                    if($pr['currency'] == $curr1)
                        {
                            if(floatval($pr['position']) < floatval($amount))
                                {
                                    return "IF";
                                }
                            else
                                {
                                    $q = "select * from exchange where currency_1='" . $curr1 . "' or currency_2='" . $curr2 . "';"; #todo figure out if reversed

                                    if($db->query($q) == TRUE)
                                        {
                                            $ex= $db->query($q);
                                            while($er = $ex->fetch_array(MYSQLI_ASSOC))
                                                {
                                                    if($er['currency_1'] == $curr1)
                                                        {
                                                            $rate = floatval($er['rate']); #if reversed: rate = 1/rate
                                                        }
                                                    else
                                                        {
                                                            $rate = floatval(1 / floatval($er['rate']) );
                                                        }
                                                    
                                                }
                                                                                        
                                            if($toExist == true)
                                                {
                                                    $newPos = floatval(floatval($amount) * floatval($rate)) + $toPos;
                                                    $q1 = "update $user set position='" . $newPos . "' where currency='" . $curr2 .  "';";
                                                }
                                            else
                                                {
                                                    $newPos = floatval($amount * $rate);
                                                    $q1 = "insert into $user (currency, position) values ('" . $curr2 . "', '" . $newPos . "');";
                                                }

                                            $q2 = "update $user set position='" . (floatval($fromPos) - floatval($amount)) . "' where currency='" . $curr1 .  "';"; 
                                            $db->query($q1) or die($db->error);
                                            $db->query($q2) or die($db->error);
                                        }

                                }
                        }
                    
                }
        }
    else
        return $db->error;
}

print trade('cheesecake', 'USD', '5.000', 'CHY');

?>