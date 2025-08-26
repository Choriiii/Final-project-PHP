<?php
require("./db_connect.php");
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
    echo $err->getMessage();
    http_response_code($err->getCode());
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
            echo "No user found.";
        };
        ?>
    </table>
</body>

</html>