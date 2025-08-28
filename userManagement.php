<?php
date_default_timezone_set("America/Vancouver");
session_start();
require("./config.php");
$actingUserRole=$_SESSION['userrole'];
$userID=$_SESSION['UserID'];
if($actingUserRole!=="admin"){
    header("Location: index.php");
    exit;
}
try {
    $dbCon = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);
    if ($dbCon->connect_error) {
        throw new Exception("DB error.", 500);
    }
    $sql = $dbCon->prepare("SELECT * FROM userdata");
    $sql->execute();
    $result = $sql->get_result();
    $sql->close();
} catch (Exception $err) {
    echo "Some error occured.";
    http_response_code($err->getCode());

    $logfile = __DIR__ . "/errorLog.txt";
    $currentTime=date("Y-m-d H:i:s");
    $errorMsg="[{$currentTime}] Code: {$err->getCode()} - {$err->getMessage()}\n";

    file_put_contents($logfile, $errorMsg, FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UserManagement Page</title>
</head>

<body>
    <h1>User List</h1>
    <table>
        <tr>
            <th>Name</th>
            <th>EmailAddress</th>
            <th>role</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($users = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($users['UserName']) . "</td>";
                echo "<td>" . htmlspecialchars($users['EmailAddress']) . "</td>";
                echo "<td>" . htmlspecialchars($users['Role']) . "</td>";
                echo "<td><a href='./userEdit.php?UserID=$users[UserID]'>Edit</a></td>";
                echo "<td>
                <form method='POST' action='deleteUser.php' onSubmit=\"return confirm('You gonna realy delete this user?');\">
                <input type='hidden' name='UserID' value='".$users['UserID']."'></input>
                <button type='submit'>Delete</button></form></td>";
                echo "</tr>";
            }
        } else {
            echo "<h2>No user found.</h2>";
        };
        ?>
        <tr><a href='./userEdit.php?UserID='>Add User</a><tr>
    </table>
</body>

</html>