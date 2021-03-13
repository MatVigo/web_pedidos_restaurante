<?php
require_once 'bd.php';

/* Formulario de login. Si todo va bien abre sesión, guarda el nombre de usuario 
 * y redirige a la página para mostrar las categorías. Si algo a ocurrido un 
 * problema muestra un mensaje de error */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usu = comprobar_usuario($_POST['usuario'], $_POST['clave']);
    if ($usu === false) {
        $err = true;
        $usuario = $_POST['usuario'];
    } else {
        session_start();
        // $usu tiene campos correo y codRes, correo 
        $_SESSION['usuario'] = $usu; //array de dos elementos
        $_SESSION['carrito'] = [];
        header("Location: categorias.php");
        return;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Formulario de login</title>
        <meta charset = "UTF-8">
    </head>
    <body>	
        <?php
        if (isset($_GET["redirigido"])) {
            echo "<p>Haga login para continuar</p>";
        }
        ?>
        <?php
        if (isset($err) and $err == true) {
            echo "<p> Revise usuario y contraseña</p>";
        }
        ?>
        <form id="formulario" action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method = "POST">
            <label id="label_usuario" for = "usuario">Usuario</label><br> 
            <input id="input_usuario" value = "<?php if (isset($usuario)) echo $usuario; ?>"
                   id = "usuario" name = "usuario" type = "text"><br>		
            <label id="label_clave" for = "clave">Clave</label> <br>
            <input id = "clave" name = "clave" type = "password"><br><br>					
            <input id="boton" type = "submit">
        </form>
        <script src="js/alertLogin.js"></script>
    </body>
</html>