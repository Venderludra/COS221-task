<?php

require_once "config.php";

$db = Database::getInstance()->getConnection();

if($db){
    echo "Connected Successfully!";
}

?>