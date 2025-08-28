<?php
declare(strict_types=1);
session_start();

if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit;
}

require_once("./config.php");

$product = null;

if (isset($_GET['productID'])) {
    $productID = (int)$_GET['productID'];
    $dbcon = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);
    if ($dbcon->connect_error) {
        die("DB connection error");
    }

    $sql = "SELECT * FROM products WHERE productID = ?";
    $stmt = $dbcon->prepare($sql);
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    $dbcon->close();

    if (!$product) {
        die("Product not found");
    }
} else {
    die("Invalid request");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fafafa;
            margin: 0;
            padding: 0;
        }
        header {
            background: #d84315;
            color: white;
            text-align: center;
            padding: 20px;
        }
        form {
            max-width: 500px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        form input, form button {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
        }
        img {
            
            height: 180px;
            object-fit: cover;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<header>
    <h1>Edit Product</h1>
</header>

<form action="index.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="productID" value="<?= $product['productID'] ?>">

    <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
            <input type="text" name="name" id="name" placeholder="Product Name" value="<?= htmlspecialchars($product['name']) ?>" required>
           <input type="text" name="description" id="description" placeholder="Description" value="<?= htmlspecialchars($product['description']) ?>" required>
            <input type="text" name="price" id="price" placeholder="Price" required>
            <input type="file" name="image" id="image" accept="image/*" required>
            <button type="submit" name="update_product">Update Product</button>
</form>


</body>
</html>
