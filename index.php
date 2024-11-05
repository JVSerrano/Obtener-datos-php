<?php
session_start();

$session_duration = 60; // Duración de la sesión en segundos

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $session_duration) {
    session_unset();
    session_destroy();
    $_SESSION['cart'] = [];
}

$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['cart_count'])) {
    $_SESSION['cart_count'] = 0;
}

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "ud06_store"
);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM product");

function imageExists($imagePath)
{
    return file_exists($imagePath);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Grocery Store</title>
</head>

<body>
    <div class="cart-login-container">
        <div class="cart-count">
            <img src="carro.png" alt="Carrito">
            <span><?php echo $_SESSION['cart_count'] . ' producto'; ?></span>
        </div>
        <div class="login-icon">
            <img src="log.png" alt="Usuario">
            <a href="login.php">
                <span><?php echo isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['first_name']) : 'Login'; ?></span>
            </a>
        </div>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['rol'] === 'admin'): ?>
            <div class="admin-icon">
                <a href="users.php">
                    <img src="usuarios.svg" alt="Users">
                    <span>Users</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <h1>Grocery Store</h1>

    <div class="product-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class='product-details'>
                    <div class='product-header'>
                        <h2 class='product-name'><?php echo htmlspecialchars($row["Product_name"]); ?></h2>
                        <?php
                        $imagePath = "img/" . $row["Picture"];
                        $imgSrc = imageExists($imagePath) ? htmlspecialchars($imagePath) : "img/No_image_available.svg";
                        ?>
                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($row["Product_name"]); ?>"
                            class='product-image'>
                    </div>
                    <p class='product-price'>
                        <?php echo htmlspecialchars($row["Price"]) . "€ (" . htmlspecialchars($row["Units"]) . " unidades disponibles)"; ?>
                    </p>
                    <p class='product-description'><?php echo htmlspecialchars($row["Product_description"]); ?></p>
                    <form action='inf.php' method='get'>
                        <input type='hidden' name='product_id' value='<?php echo $row["Product_id"]; ?>'>
                        <input type='submit' class='add-button' value='Añadir'>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
        <?php $conn->close(); ?>
    </div>
</body>

</html>