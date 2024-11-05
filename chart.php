<?php
session_start();

$session_duration = 60; // Duración de la sesión en segundos
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $session_duration) {
    session_unset();
    session_destroy();
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ud06_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function getProductDetails($product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT Product_id, Product_name, Price, Units, Product_description FROM product WHERE Product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

if (isset($_POST['remove_from_cart'], $_POST['product_id'])) {
    unset($_SESSION['cart'][$_POST['product_id']]);
}

$_SESSION['cart_count'] = array_sum($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Carrito de Compras</title>
</head>
<body>
    <h1>Carrito de Compras</h1>
    <h2>Productos en el carrito</h2>
    <div class="product-details">
        <?php if (empty($_SESSION['cart'])): ?>
            <p>El carrito está vacío.</p>
        <?php else: ?>
            <?php foreach ($_SESSION['cart'] as $product_id => $quantity): ?>
                <?php $product = getProductDetails($product_id); ?>
                <?php if ($product): ?>
                    <div class='cart-item'>
                        <h3><?php echo htmlspecialchars($product['Product_name']); ?></h3>
                        <p>Precio: <?php echo $product['Price']; ?>€ (<?php echo $quantity; ?> unidades)</p>
                        <form action='chart.php' method='post'>
                            <input type='hidden' name='product_id' value='<?php echo $product_id; ?>'>
                            <input type='submit' name='remove_from_cart' value='Eliminar producto'>
                            <img src='papelera.png' alt='Eliminar' style='width:20px; vertical-align:middle;'>
                        </form>
                        <hr>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <p><a href="index.php">Volver a la tienda</a></p>
</body>
</html>

<?php
$conn->close();
?>
