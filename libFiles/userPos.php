#!/usr/bin/php
<?php

function userPos($user, $db)
{
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

?>