#!/usr/bin/php
<?php

$db = new mysqli("10.200.173.154","server","letMe1n","user_info");

$q = "select currency, description from currencies;";
$res = $db->query($q);

$out = array();

while($r = $res->fetch_array(MYSQLI_ASSOC))
{
    $out[$r['currency']] = $r['description'];
}

return $out;

?>