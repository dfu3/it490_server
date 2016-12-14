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

    $currDic = array(); //user's currency = arraym of posible trading currency

    foreach($myCurrs as $myCurr)
        {
            $currDic[$myCurr] = array();
        }

    $testArr = array();
    
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
                    $score = $upper - $lower;

                    $test = "";
                    if($rate > $upper)
                        {
                            if(in_array($curr1, $myCurrs))
                                {
                                    $test.= "upper ";
                                    $temp[$curr2] = $score;
                                    $currDic[$curr1] = $temp;
                                }
                        }
                    elseif($rate < $lower)
                        {
                            if(in_array($curr2, $myCurrs))
                                {
                                    $test.= "lower ";
                                    $temp[$curr1] = $score;
                                    $currDic[$curr2] = $temp;
                                }  
                        }

                    array_push($testArr, $test);
                    

                }     
        }
    else { return "FAIL"; }

    $toTrade = array();
    foreach($currDic as $key=>$val)
        {
            $max = 0;
            $best = "NONE";
            
            foreach($val as $inKey=>$inVal)
                {
                    //print_r($key);
                    if($inVal > $max)
                        {
                            $max = $inVal;
                            $best = $inKey;                            
                        }
                }

            $toTrade[$key] = $best;
        }

    return ($toTrade);
        
}

//$db = new mysqli("10.200.45.16","server","letMe1n","user_info");
//print_r(getPred($db, 'paul'));

?>