<?php
session_start();

// Verificar si el usuario es admin
if (!isset($_SESSION['user_id'], $_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ud06_store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener los usuarios
$sql = "SELECT user_id, first_name, last_name, email_id, rol FROM user";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Usuarios</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .user-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <h1>Administración de Usuarios</h1>

    <div class="user-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class='user-item'>
                    <strong>Usuario:</strong> <?php echo htmlspecialchars($row["first_name"] . " " . $row["last_name"]); ?>
                    &nbsp;&nbsp;<strong>E-mail:</strong> <?php echo htmlspecialchars($row["email_id"]); ?>
                    &nbsp;&nbsp;<strong>Rol:</strong> <?php echo htmlspecialchars($row["rol"]); ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No se encontraron usuarios.</p>
        <?php endif; ?>
    </div>
    <p><a href="index.php">Volver a la tienda</a></p>
</body>
</html>

<?php
$conn->close();
?>
