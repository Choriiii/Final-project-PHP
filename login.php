<?php
session_start();
require("./db_connect.php");
if (isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userrole = $_POST['role'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $dbCon = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);
        if ($dbCon->connect_error) {
            throw new Exception("DB error.", 500);
        }
        $insertPrep = $dbCon->prepare("SELECT * FROM userdata WHERE EmailAddress=?");
        $insertPrep->bind_param("s", $email);
        $insertPrep->execute();
        $result = $insertPrep->get_result();
        //check if you have account
        if ($users = $result->fetch_assoc()) {
            //if the user have an account
            if (password_verify($password, $users['Password'])) {
                if ($users['Role'] === $userrole) {
                    $_SESSION['email'] = $email;
                    $_SESSION['userrole'] = $userrole;
                    header("Location: index.php");
                    exit;
                } else {
                    echo "Your role is wrong. Choose the correct role.";
                }
            } else {
                echo "Your email or password is wrong.";
            }
        } else {
            echo "You don't have account.";
        }
        $insertPrep->close(); //ここでcloseでいいのか
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
    <link rel="stylesheet" href="./style.css">
</head>

<body>
    <section class="form-register">
        <h1>Login</h1>
        <form action="login.php" method="post">
            <select name="role" id="">
                <option value="admin">Admin</option>
                <option value="editor">Editor</option>
                <option value="viewer">Viewer</option>
            </select>
            <input type="text" name="email" placeholder="email" required>
            <input type="password" name="password" placeholder="password" required>
            <button class="formButton">Login</button>
        </form>
        <p>You don't have your account? <a href="./register.php">Signup here</a></p>
    </section>
</body>

</html>