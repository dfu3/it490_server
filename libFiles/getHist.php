#!/usr/bin/php
<?php

function getHist($db, $curr1, $curr2)
{

    $q = "select * from exchange_backup where (currency_1='" . $curr1 . "' and currency_2='" . $curr2 . "') or (currency_1='" . $curr2 . "' and currency_2='" . $curr1 . "') order by grouping desc limit 20;";

    $rates = array();
    
    if($db->query($q) == true)
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

                    array_push($rates, $rate);

                }     
        }
    else { return "FAIL"; }

    return $rates;
    
}

#$db = new mysqli("10.200.44.178","server","letMe1n","user_info");
#print_r(getHist($db, 'USD', 'EUR'));


?>