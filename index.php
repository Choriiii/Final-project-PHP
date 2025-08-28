<?php
declare(strict_types=1);
session_start();
$userEmail=$_SESSION['email'];
//if you aren't login---it changes the page automatically to the login-page
if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit;
};

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
    
function addNewProduct(string $name, string $description, float $price,array $image){
    try {
    if (!isset($image) || $image['error'] !== UPLOAD_ERR_OK) {//UPLOAD_ERR_OK es una constante de PHP que significa 0, es decir, la subida fue exitosa.
            throw new Exception("Error to upload the image.");
        }

    //data for the image
    $uploadDir = 'uploads/';

//Cuando subimos un archivo en PHP, el array $_FILES['image'] tiene varios elementos:

    $fileName = basename($image['name']); // name → nombre original del archivo.
        $fileTmp  = $image['tmp_name']; // tmp_name → ruta temporal en el servidor.
        $fileSize = $image['size'];  // size → tamaño del archivo.
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));//strtolower convierte las letras en minusculas -- 
        // pathinfo es una funcion de PHP que extra info de la ruta de la imagen o nombre del archivo Puede devolver varias partes:

// dirname → ruta del directorio.

// basename → nombre del archivo con extensión.

// filename → nombre del archivo sin extensión.

// extension → extensión del archivo.

// PATHINFO_EXTENSION le indica a pathinfo() que solo queremos la extensión del archivo.
    // error → código de error de la subida.
     $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("not allowed type of image. only JPG, JPEG, PNG or GIF.");
        }

        if ($fileSize > 5 * 1024 * 1024) {//1 kilobyte (KB) = 1024 bytes
        // 1 megabyte (MB) = 1024 KB = 1024 × 1024 bytes = 1,048,576 bytes
            throw new Exception("the image its too large. Máx 5MB.");
        }
        $newFileName = uniqid('img_') . '.' . $fileType;
        $destination = $uploadDir . $newFileName;

        if (!move_uploaded_file($fileTmp, $destination)) {
            throw new Exception("unable to save the image.");
        }
        $products_file = fopen("new_products.txt","a") or die ("Unable to open file!");
    // data for the audit record
    $new_product = [
        "name" => $name,
        "description" => $description,
        "price" => $price,
        "image" => $destination 
    ];

    $timestamp = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    require("./db_connect.php");
    $dbcon = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASS, DB_NAME);
            if($dbcon->connect_error){
                throw new Exception("DB error", 500);
            }
             $name = $new_product["name"];
            $description = $new_product["description"];
            $price = $new_product["price"];
            $image = $new_product["image"];

            $insertPrep = $dbcon->prepare("INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)");
            $insertPrep->bind_param("ssds", $name, $description, $price, $image);
            $insertPrep->execute();
            $insertPrep->close();

            /*get the userID from DB(this doesn't work properly yet. I wanted to get $userID for the audit record)
            $insertPrep=$dbcon->prepare("SELECT UserID from userdata WHERE EmailAddress=?");
            $insertPrep->bind_param("s",$userEmail);
            $insertPrep->execute();
            $insertPrep->bind_result($userID);
            $insertPrep->fetch();
            $insertPrep->close();*/

            // Insert en audit_record
            $action = "added: " . $new_product["name"];
            $insertPrep = $dbcon->prepare("INSERT INTO audit_record (timestamp, ip, userAgent, action, /*UserID*/) VALUES (?, ?, ?, ?, /*?*/)");
            $insertPrep->bind_param("sssss", $timestamp, $ip, $userAgent, $action/*, $userID*/);//I add $userID here, but I'm not sure this will works....
            $insertPrep->execute();

            $insertPrep->close();
            $dbcon->close();

    }catch(Exception $err){
    echo $err->getMessage();
    http_response_code($err->getCode());
}
    }
function displayNewProducts(){
    if (!file_exists("new_products.txt") || filesize("new_products.txt")=== 0){
        return [];
    }else{
        $open_new_products = fopen("new_products.txt","r") or die ("Unable to open file");
        $new_products = fread($open_new_products,filesize("new_products.txt"));
        fclose($open_new_products);

         $lines = explode(PHP_EOL, trim($new_products));//explode sirve para dividir una string usando un delimitador y se usa asi explode(string $delimitador, string $texto, int $limite = PHP_INT_MAX): array

        $products = [];
        foreach ($lines as $line) {
            if (trim($line) === "") continue;
            $parts = explode(" - ", $line, 4);
            if (isset($parts[3])){
                $json = $parts[3];
                $productData = json_decode($json, true);
                if ($productData) {
                    $products[] = $productData;
                }
            }
        }
        return $products;
    }
    
}
function deleteProduct(){
   

}
function editProduct(string $name, string $description, float $price,array $image){
    

}
// calling functions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    addNewProduct($_POST['name'], $_POST['description'], floatval($_POST['price']), $_FILES['image'] );
}
$new_products=displayNewProducts();

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
    <!--This form/button is for testing the logout function is working, you can erase or change the place!-->
        <form action="logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </header>

    <section class="menu">
    <?php foreach ($products as $product) { ?>
    <div class="product">
        <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
        <h3><?= $product['name'] ?></h3>
        <p><?= $product['description'] ?></p>
        <div class="price">$<?= $product['price'] ?></div>
        <button >delete</button>
    </div>
<?php } ?>
<?php foreach ($new_products as $new_product) { ?>
    <div class="product">
        <img src="<?= $new_product['image'] ?? 'default.jpg' ?>" alt="<?= $new_product['name'] ?>">
        <h3><?= $new_product['name'] ?></h3>
        <p><?= $new_product['description'] ?></p>
        <div class="price">$<?= $new_product['price'] ?></div>
        <button>delete</button>
    </div>
<?php } ?>
       
    </section>
    <section>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <h2>Add Products</h2>
            <input type="text" name="name" id="name" placeholder="Product Name" required>
            <input type="text" name="description" id="description" placeholder="Description" required>
            <input type="text" name="price" id="price" placeholder="Price" required>
            <input type="file" name="image" id="image" accept="image/*" required>
            <button type="submit" name="submit">Add Product</button>
        </form>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <h2>Edit Products</h2>
            <input type="text" name="name" id="name" placeholder="Product Name" required>
            <input type="text" name="description" id="description" placeholder="Description" required>
            <input type="text" name="price" id="price" placeholder="Price" required>
            <input type="file" name="image" id="image" accept="image/*" required>
            <!-- Básicamente, limita la selección a archivos que el navegador reconoce como imágenes. -->
            <button type="submit" name="submit">Edit Product</button>
        </form>
    </section>

</body>
</html>