<?php
session_start();
require("./classes/UserManager.php");
require("./db_connect.php");
$db = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);
$userID = (int)$_GET["UserID"];

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
};

$manager = new UserManager($db);
if(!$actingUser->can("userEdit",$userID)){
    echo "You don't have accessibility.";
}
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $sql = $db->prepare("SELECT * FROM userdata WHERE UserID=?");
    $sql->bind_param("i", $userID);
    $sql->execute();
    $result = $sql->get_result();
    $userData = $result->fetch_assoc();
    $sql->close();
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['username'];
    $email=$_POST['email'];
    $role=$_POST['role'];

    $update=$manager->user_edit($actingUser,$userID,[
        "UserName"=>$name,
        "EmailAddress"=>$email,
        "Role"=>$role
    ]);
    if($update && $userID === $actingUserID){
        $_SESSION['email']=$email;
        $_SESSION['userrole']=$role;
    }

    header("Location: userManagement.php");
    exit;
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
    <form method="post" action="userEdit.php?UserID=<?php echo htmlspecialchars($userData['UserID']); ?>">
        <?php
        echo "<label>Name: <input type='text' name='username'value=" . htmlspecialchars($userData['UserName']) . "></label><br>";
        echo "<label>EmailAddress: <input type='text' name='email'value=" . htmlspecialchars($userData['EmailAddress']) . "></label><br>";
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