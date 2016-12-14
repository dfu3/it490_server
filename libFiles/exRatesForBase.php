#!/usr/bin/php
<?php

function exRatesForBase($base, $db)
{
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

            $table = "<table id='exRates'>";
            $table.= '<thead> <tr> <th> Currency </th> <th> Rate </th> </tr> </thead>';
            $table.= '<tbody>';
            foreach($rates as $curr=>$exRate)
                {
                    $table.= '<tr> <td> ' . $curr . ' </td> <td> ' . $exRate . '</td> </tr>';
                }
            $table.= '</tbody>';
            $table.= '</table>';
            
            return $table;

        }
}

?>