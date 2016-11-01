#!/usr/bin/php
<?php
function trade($user, $curr1, $amount, $curr2, $db)
{
    $q = "select * from $user;";

    if($db->query($q) == TRUE)
        {
            $pos = $db->query($q);
            
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
            if($fromExist == false) {return "IF";}

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
                                            return "SUCC";
                                        }

                                }
                        }
                    
                }
        }
    else
        return $db->error;
}

?>