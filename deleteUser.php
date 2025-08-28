<?php
date_default_timezone_set("America/Vancouver");
session_start();
require("./config.php");
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
    $action="deleted the user userID=$userID";
    $delete=$manager->user_delete($actingUser,$userID);
    if($delete){
        //audit record data
        $timestamp = date("Y-m-d H:i:s");
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        //for the audit record
        $sql = $db->prepare("INSERT INTO audit_record(timestamp, ip, userAgent, action, UserID) VALUES (?,?,?,?,?)");
        $sql->bind_param("ssssi", $timestamp, $ip, $userAgent, $action, $actingUserID);
        $sql->execute();
        $sql->close();

        header("Location: userManagement.php");
        exit;
    }
}
}catch(Exception $err){
    echo "Some error occured.";
    http_response_code($err->getCode());

    $logfile = __DIR__ . "/errorLog.txt";
    $currentTime=date("Y-m-d H:i:s");
    $errorMsg="[{$currentTime}] Code: {$err->getCode()} - {$err->getMessage()}\n";

    file_put_contents($logfile, $errorMsg, FILE_APPEND);
}

?>