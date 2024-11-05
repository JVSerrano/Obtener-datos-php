<?php
session_start();

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ud06_store";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$error = '';
$show_register_form = false;

// Verificar si el usuario ya ha iniciado sesión
if (isset($_SESSION['user_id'])) {
    // Obtener información del usuario
    $sql = "SELECT user_id, first_name, last_name, email_id, mobile_no, rol FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_info = $result->fetch_assoc();
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['login'])) {
            // Inicio de sesión
            $email = $_POST['email'];
            $password = $_POST['password'];

            $sql = "SELECT user_id, password, first_name, rol FROM user WHERE email_id = ? OR user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['rol'] = $user['rol']; // Guardar el rol en la sesión
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Usuario o contraseña incorrectos";
                }
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        } elseif (isset($_POST['register'])) {
            // Registro de nuevo usuario
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $mobile_no = $_POST['mobile_no'];

            $user_id = $first_name . "_" . $last_name;
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO user (user_id, email_id, password, rol, first_name, last_name, mobile_no) 
                    VALUES (?, ?, ?, 'user', ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $user_id, $email, $hashed_password, $first_name, $last_name, $mobile_no);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['first_name'] = $first_name;
                header("Location: index.php");
                exit();
            } else {
                $error = "Error al registrar: " . $conn->error;
            }
        }
    }
}

if (isset($_GET['register'])) {
    $show_register_form = true;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Login / Registro</title>
    <style>
        .error {
            color: red;
        }

        #registerForm {
            display: none;
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <h2>Información del Usuario</h2>
        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($user_info['user_id']); ?></p>
        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($user_info['email_id']); ?></p>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user_info['first_name']); ?></p>
        <p><strong>Apellido:</strong> <?php echo htmlspecialchars($user_info['last_name']); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($user_info['mobile_no']); ?></p>
        <p><a href="logout.php">Cerrar sesión</a></p>
    <?php else: ?>
        <h2>Iniciar Sesión</h2>
        <form method="post">
            <input type="text" name="email" placeholder="Usuario o e-mail" required><br>
            <input type="password" name="password" placeholder="Contraseña" required><br>
            <input type="submit" name="login" value="Entrar">
        </form>

        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <p>O <a href="?register=true" id="showRegister">registrarse</a>...</p>

        <div id="registerForm" <?php echo $show_register_form ? 'style="display:block;"' : ''; ?>>
            <h2>Registrarse</h2>
            <form method="post">
                <input type="text" name="first_name" placeholder="Nombre" required><br>
                <input type="text" name="last_name" placeholder="Apellido" required><br>
                <input type="email" name="email" placeholder="Email" required><br>
                <input type="password" name="password" placeholder="Contraseña" required><br>
                <input type="text" name="mobile_no" placeholder="Teléfono" required><br>
                <input type="submit" name="register" value="Registrarse">
            </form>
        </div>

        <script>
            document.getElementById('showRegister').addEventListener('click', function (e) {
                e.preventDefault();
                document.getElementById('registerForm').style.display = 'block';
            });
        </script>
    <?php endif; ?>
    <p><a href="index.php">Volver a la tienda</a></p>
</body>

</html>

<?php
$conn->close();
?>