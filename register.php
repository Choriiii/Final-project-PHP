<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userrole = $_POST['role'];
    $username = $_POST['username'];
    $email=$_POST['email'];
    $password = $_POST['password'];

    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    //taking data to check if we already have the user data. 
    $users = [];
    if (file_exists("list.json")) {
        $jsonData = file_get_contents("list.json");
        $users = json_decode($jsonData, true) ?? [];
    }


    //registering a new account
    $newUser = [
        'role' => $userrole,
        'username' => $username,
        'email' => $email,
        'password' => $hashPassword
    ];

    $exists = false;
    foreach ($users as $user) {
        if ($user['email']===$email && $user['role'] === $userrole) {
            $exists = true;
            break;
        }
    }

    if ($exists) {
        echo "You already have your account.";
    } else {
        $users[] = $newUser;

        file_put_contents("list.json", json_encode($users, JSON_PRETTY_PRINT));
        $newUser=[];
    }
    //既存のjsonファイルにrole/name/passが一致する人物がいるか確認する。いたら、ログインにする。

} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
}
