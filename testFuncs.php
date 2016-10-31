#!/usr/bin/php
<?php

$db = new mysqli("10.200.44.105","server","letMe1n","user_info");

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

print $table;


?>