<?php
date_default_timezone_set("America/Vancouver");
session_start();
require("./config.php");
require("./classes/UserManager.php");
$emailSession = $_SESSION['email'];
$db = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);
$userID = isset($_GET["UserID"]) && $_GET["UserID"] !== '' ? (int)$_GET["UserID"] : null; //userID from query string

$userData = [
    'UserName' => '',
    'EmailAddress' => '',
    'Password' => '',
    'Role' => 'viewer'
];

try {
    if ($db->connect_error) {
        throw new Exception("DB error.", 500);
    }
    $actingUserID = $_SESSION['UserID']; //login userID

    $actingUserRole = $_SESSION['userrole']; //login userdata
    switch ($actingUserRole) {
        case 'admin':
            $actingUser = new Admin($actingUserID, $actingUserRole);
            break;
        case 'editor':
            $actingUser = new Editor($actingUserID, $actingUserRole);
            break;
        case 'viewer':
            $actingUser = new Viewer($actingUserID, $actingUserRole);
            break;
        default:
            die("Invalid session user.");
    };

    $manager = new UserManager($db);
    if (!$actingUser->can("userEdit", $actingUserID)) {
        echo "You don't have accessibility.";
    } elseif (!$actingUser->can("userAdd", $actingUserID)) {
        echo "You don't have accessibility";
    }
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        if ($userID) {
            $sql = $db->prepare("SELECT * FROM userdata WHERE UserID=?");
            $sql->bind_param("i", $userID);
            $sql->execute();
            $result = $sql->get_result();
            $userData = $result->fetch_assoc(); //ここのデータをhtmlでも使いたい
            $sql->close();
        }
    } elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
        $name = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];


        if ($userID) {
            $update = $manager->user_edit($actingUser, $userID, [
                "UserName" => $name,
                "EmailAddress" => $email,
                "Role" => $role
            ]);
            if ($update && $userID === $actingUserID) {
                $_SESSION['email'] = $email;
                $_SESSION['userrole'] = $role;
            };
            $action = "edit_userID=$userID's data";
        } else {
            $password = $_POST['password'];
            if (strlen($password) < 8) {
                $pass_err_mgs = "Password must have at least 8 characters.";
            } else if (!preg_match('/[0-9]/', $password)) {
                $pass_err_mgs = "You must include at least one number.";
            } else {
                $hashPassword = password_hash($password, PASSWORD_DEFAULT);
                $add = $manager->user_add($actingUser, [
                    "UserName" => $name,
                    "EmailAddress" => $email,
                    "Password" => $hashPassword,
                    "Role" => $role
                ]);
                $action = "add new user!";
            }
        }
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
} catch (Exception $err) {
    http_response_code($err->getCode());

    $logfile = __DIR__ . "/errorLog.txt";
    $currentTime = date("Y-m-d H:i:s");
    $errorMsg = "[{$currentTime}] Code: {$err->getCode()} - {$err->getMessage()}\n";

    file_put_contents($logfile, $errorMsg, FILE_APPEND);
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <p style="color: #d84315; margin:1rem 0; text-decoration: underline;"><strong>
            <?php
            if(isset($pass_err_mgs)){
                echo "⚠️".htmlspecialchars($pass_err_mgs);
            }
            ?>
    </strong></p>
    <form method="post" action="userEdit.php?UserID=<?php echo $userID ? htmlspecialchars($userID) : ''; ?>">
        <?php
        echo "<label>Name: <input type='text' name='username' value='" . htmlspecialchars($userData['UserName']) . "'></label><br>";
        echo "<label>EmailAddress: <input type='text' name='email' value='" . htmlspecialchars($userData['EmailAddress']) . "'></label><br>";
        if (!$userID) {
            echo "<label>Password: <input type='password' name='password' required></label><br>";
        }
        ?>
        <select name='role'>
            <option value='admin' <?php if ($userData['Role'] === "admin") echo 'selected'; ?>>Admin</option>
            <option value='editor' <?php if ($userData['Role'] === "editor") echo 'selected'; ?>>Editor</option>
            <option value='viewer' <?php if ($userData['Role'] === "viewer") echo 'selected'; ?>>Viewer</option>
        </select>
        <button type="submit">Update</button>
    </form>
</body>

</html>