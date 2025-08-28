<?php
session_start();
require("./db_connect.php");
require("./classes/UserManager.php");
try{
    if($_SERVER["REQUEST_METHOD"]==="POST"){
    $userID=$_POST['UserID'];

    $db = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);
    
    if($db->connect_error){
        throw new Exception("DB error.", 500);
    }
    $actingUserID=$_SESSION['UserID'];
    $actingUserRole=$_SESSION['userrole'];

    switch($actingUserRole){
    case 'admin':
        $actingUser=new Admin ($actingUserID, $actingUserRole);
        break;
    case 'editor':
        $actingUser=new Editor ($actingUserID, $actingUserRole);
        break;
    case 'viewer':
        $actingUser=new Viewer ($actingUserID, $actingUserRole);
        break;
    default:
        die("Invalid session user.");
    }

    $manager= new UserManager($db);
    $delete=$manager->user_delete($actingUser,$userID);
    if($delete){
        header("Location: userManagement.php");
        exit;
    }
}
}catch(Exception $err){
    echo $err->getMessage();
    http_response_code($err->getCode());
}

?>