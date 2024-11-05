<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ud06_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

function imageExists($imagePath) {
    return file_exists($imagePath);
}

$product = null;
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $stmt = $conn->prepare("SELECT Product_id, Product_name, Price, Units, Product_description, Picture FROM product WHERE Product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

if (isset($_SESSION['user_id']) && isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
    $_SESSION['cart_count'] = array_sum($_SESSION['cart']);
    header("Location: chart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles del Producto</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php if ($product): ?>
        <div class="product-details">
            <div class="product-header">
                <h1><?php echo htmlspecialchars($product['Product_name']); ?></h1>
                <?php
                $imagePath = "img/" . $product['Picture'];
                $imageToShow = imageExists($imagePath) ? $imagePath : 'img/No_image_available.svg';
                echo "<img src='" . htmlspecialchars($imageToShow) . "' alt='" . htmlspecialchars($product['Product_name']) . "' class='product-image'>";
                ?>
            </div>
            <p class="product-price"><?php echo htmlspecialchars($product['Price']); ?>€ (<?php echo htmlspecialchars($product['Units']); ?> unidades disponibles)</p>
            <p><?php echo htmlspecialchars($product['Product_description']); ?></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form action="inf.php" method="post">
                    <input type="hidden" name="product_id" value="<?php echo $product['Product_id']; ?>">
                    <label for="quantity">¿Cuántos productos deseas añadir?</label>
                    <div class="form-group">
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['Units']; ?>" required>
                        <input type="submit" name="add_to_cart" value="Añadir">
                    </div>
                </form>
            <?php else: ?>
                <label for="quantity">¿Cuántos productos deseas añadir?</label>
                <div class="form-group">
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['Units']; ?>" required>
                    <button onclick="location.href='http://localhost/UD06/login.php'">Añadir</button>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>Producto no encontrado.</p>
    <?php endif; ?>

    <p><a href="index.php">Volver a la tienda</a></p>
</body>
</html>

<?php
$conn->close();
?>
