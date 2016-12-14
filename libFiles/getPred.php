#!/usr/bin/php
<?php

function getPred($db, $user) //get suggestions for currPairs of user
{

    //find all currencies in the user's tables
    //use those as curr1 and curr2 (bidir) for searching trade_limits
    //use the result^ as the set to test against the current rate
    //if upper/lower limit is met-> add that trade (either x->y or y->x) to list of suggestions
    //return list

    $q = "select currency from $user;";
    $myCurrs = array();

    if($db->query($q) == true)
        {
            $ex= $db->query($q);
            while($res = $ex->fetch_array(MYSQLI_ASSOC))
                {
                    array_push($myCurrs, $res['currency']);
                }
        }
    
    $q = "select * from trade_limits where currency_1 IN (select currency from $user) or currency_2 IN (select currency from $user)";
    $suggs= array();
    
    if($db->query($q) == true)
        {
            $ex= $db->query($q);
            while($res = $ex->fetch_array(MYSQLI_ASSOC))
                {

                    $rate = floatval($res['rate']);
                    $upper = floatval($res['upper']);
                    $lower = floatval($res['lower']);
                    $curr1 = ($res['currency_1']);
                    $curr2 = ($res['currency_2']);
                    
                    if($rate > $upper)
                        {
                            if(in_array($curr1, $myCurrs))
                                {
                                    $sugg = "Trade $curr1 for $curr2";
                                    array_push($suggs, $sugg);
                                }
                        }
                    elseif($rate < $lower)
                        {
                            if(in_array($curr2, $myCurrs))
                                {
                                    $sugg = "Trade $curr2 for $curr1";
                                    array_push($suggs, $sugg);
                                }  
                        }
                    
                    

                }     
        }
    else { return "FAIL"; }


    $table = "<table id='exRates'>";
    $table.= '<thead> <tr> <th> Trade Predictions </th> </thead>';
    $table.= '<tbody>';
    foreach($suggs as $sugg)
        {
            $table.= '<tr> <td> ' . $sugg . ' </td> </tr>';
        }
    $table.= '</tbody>';
    $table.= '</table>';

    return $table;
    
}

//$db = new mysqli("10.200.173.68","server","letMe1n","user_info");
//print_r(getPred($db, 'paul'));

?>