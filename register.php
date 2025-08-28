<?php
session_start();
require("./db_connect.php");
$dbCon = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userrole = $_POST['role'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);


        if ($dbCon->connect_error) {
            throw new Exception("DB error.", 500);
        }
        $insertPrep = $dbCon->prepare("SELECT * FROM userdata WHERE EmailAddress=?");
        $insertPrep->bind_param("s", $email);
        $insertPrep->execute();
        $result = $insertPrep->get_result();
        if ($result->num_rows > 0) {
            echo "This email already has account.";
        } else {
            $prepare = $dbCon->prepare("INSERT INTO `userdata`(`UserName`, `EmailAddress`, `Password`, `Role`) VALUES (?,?,?,?)");
            $prepare->bind_param("ssss", $username, $email, $hashPassword, $userrole);
            $prepare->execute();
            $prepare->close();

            $_SESSION['email'] = $email;
            $_SESSION['userrole'] = $userrole;
            header("Location: index.php");
            exit;
        }
        $insertPrep->close();
    }
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
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="form-register">
        <h1>Sign up</h1>
        <form action="register.php" method="post">
            <select name="role" id="">
                <option value="" selected disabled>--Select--</option>
                <option value="admin">Admin</option>
                <option value="editor">Editor</option>
                <option value="viewer">Viewer</option>
            </select>
            <input type="text" name="username" placeholder="username" required>
            <input type="text" name="email" placeholder="email" required>
            <input type="password" name="password" placeholder="password" required>
            <button class="formButton">Signup</button>
        </form>
        <p>Already have your account? <a href="./login.php">Login here</a></p>
    </div>
</body>

</html>