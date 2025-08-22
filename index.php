<?php
session_start();
//if you aren't login---it changes the page automatically to the login-page
//(But now I wrote this separatly with the html-file, so it doesn't work...)
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
}


?>