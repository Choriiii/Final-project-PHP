<?php
declare(strict_types=1);
$products = [
    [
        "name" => "Classic Burger",
        "description" => "Juicy beef patty, cheddar cheese, fresh lettuce, and tomato.",
        "price" => 8.50,
        "image" => "https://blog-content.omahasteaks.com/wp-content/uploads/2022/06/blogwp_classic-american-burger-scaled-1.jpg"
    ],
    [
        "name" => "Margherita Pizza",
        "description" => "Handmade dough, tomato sauce, mozzarella, and fresh basil.",
        "price" => 10.00,
        "image" => "https://caitsplate.com/wp-core/wp-content/uploads/2020/04/IMG_0078.jpg"
    ],
    [
        "name" => "Cesar Salad",
        "description" => "Romaine lettuce, Caesar dressing, croutons, and parmesan cheese.",
        "price" => 6.75,
        "image" => "https://images.ricardocuisine.com/services/recipes/8440.jpg"
    ],
    [
        "name" => "Tacos al Pastor",
        "description" => "Marinated pork, pineapple, onion, and cilantro in corn tortillas.",
        "price" => 7.25,
        "image" => "https://assets.tmecosys.com/image/upload/t_web_rdp_recipe_584x480/img/recipe/ras/Assets/C07AE049-11C3-4672-A96A-A547C15F0116/Derivates/FE1D05A4-0A44-4007-9A42-5CAFD9F8F798.jpg"
    ]
];
function addNewProduct(string $name, string $description, float $price){
    $products_file = fopen("new_products.txt","a") or die ("Unable to open file!");
    $new_product = [
        "name" => $name,
        "description" => $description,
        "price" => $price
    ];

    $jsonData = json_encode($new_product, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    $timestamp = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ;

    fwrite($products_file, "[$timestamp] - IP: $ip - User Agent: $userAgent - $jsonData" . PHP_EOL);
    fclose($products_file);
    }
function displayNewProducts(){
    if (!file_exists("new_products.txt") || filesize("new_products.txt"=== 0)){
        return [];
    }else{
        $open_new_products = fopen("new_products.txt","r") or die ("Unable to open file");
        $new_products = fread($open_new_products,filesize("new_products.txt"));
        fclose($open_new_products);
    }
    
}
displayNewProducts();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    addNewProduct($_POST['name'], $_POST['description'], floatval($_POST['price']));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Menu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fafafa;
        }
        header {
            background-color: #d84315;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .product {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .product img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .product h3 {
            margin: 10px 0;
        }
        .product p {
            padding: 0 10px;
            font-size: 14px;
            color: #555;
        }
        .price {
            font-size: 18px;
            color: #d84315;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>

    <header>
        <h1>Restaurant Menu</h1>
    </header>

    <section class="menu">
    <?php foreach ($products as $product) { ?>
    <div class="product">
        <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
        <h3><?= $product['name'] ?></h3>
        <p><?= $product['description'] ?></p>
        <div class="price">$<?= $product['price'] ?></div>
    </div>
<?php } ?>
<?php foreach ($new_products as $new_product) { ?>
    <div class="product">
        <img src="<?= $new_product['image'] ?? 'default.jpg' ?>" alt="<?= $new_product['name'] ?>">
        <h3><?= $new_product['name'] ?></h3>
        <p><?= $new_product['description'] ?></p>
        <div class="price">$<?= $new_product['price'] ?></div>
    </div>
<?php } ?>
       
    </section>
    <section>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <h2>Add Products</h2>
            <input type="text" name="name" id="name" placeholder="Product Name" required>
            <input type="text" name="description" id="description" placeholder="Description" required>
            <input type="text" name="price" id="price" placeholder="Price" required>
            <input type="file" value="Upload Image">
            <button type="submit" name="submit">Add Product</button>
        </form>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <h2>Edit Products</h2>
            <input type="text" name="name" id="name" placeholder="Product Name" required>
            <input type="text" name="description" id="description" placeholder="Description" required>
            <input type="text" name="price" id="price" placeholder="Price" required>
            <input type="file" value="Upload Image">
            <button type="submit" name="submit">Edit Product</button>
        </form>
    </section>

</body>
</html>