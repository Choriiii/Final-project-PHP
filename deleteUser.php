<?php
require("./db_connect.php");
if($_SERVER["REQUEST_METHOD"]==="POST"){
    $db = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);
    $manager= new UserManager($db);
    $delete=$manager->user_delete($actingUser,$userID);
    
}
?>