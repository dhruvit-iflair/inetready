<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: AUTHORIZATION, Origin, X-Requested-With, Content-Type, Accept');
header('HTTP/1.1 200 OK', true, 200);
$cookie_name = "user";
$cookie_value = "John Doe";
setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");

if($_GET['action']=="get"){
    echo "<pre>";
    print_r($_COOKIE);
    
}






exit;