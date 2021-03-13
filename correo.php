﻿<?php
/*
 * Utilizamos la librería de terceros PHPMailer proporcionada por Composer
 */
use PHPMailer\PHPMailer\PHPMailer;



require dirname(__FILE__) . "/vendor/autoload.php";





/**
 * Función que envía un correo de confirmación al restaurante que ha realizado el pedido
 * y al departamento de pedidos. El correo incluye el número del pedido, el 
 * restaurante que lo realiza y una tabla HTML con los productos del pedido.
 * 
 * @param int[] $carrito Array de enteros con los códigos
 * @param int[] $pedido Array entero
 * @param string $correo Una cadena de texto, es el correo del usuario
 * 
 * @return [type] Devuelve la función enviar_correo_multiples
 */
function enviar_correos($carrito, $pedido, $correo) {
   
    $cuerpo = crear_correo($carrito, $pedido, $correo);
    $correo_Departamento_Pedidos = ""; //Poner al responsable del departamento de pedidos
    return enviar_correo_multiples("$correo, $correo_Departamento_Pedidos",
            $cuerpo, "Pedido $pedido confirmado");
}






/**
 *  Crea la tabla HTML con los productos que se piden, incluyendo el peso
 * 
 * @param int[] $carrito Array de enteros
 * @param int[] $pedido Array de enteros con el codigo del pedido
 * @param string $correo Cadena de texto, correo del usuario
 * 
 * @return [type] Devuelve la estrucutura de la tabla con sus valores correspondientes
 */
function crear_correo($carrito, $pedido, $correo) {
    
    $pesoTotal = 0;
    $texto = "<h1>Pedido nº $pedido[0]</h1><h2>Restaurante: $correo </h2>";
    $texto .= "Detalle del pedido:";
    //Los datos de los productos de los pedido que irán en el cuerpo del mensaje como una tabla
    $productos = cargar_productos(array_keys($carrito));
    $texto .= "<table>"; //abrir la tabla
    $texto .= "<tr><th>Nombre</th><th>Descripción</th><th>Peso</th><th>Unidades</th></tr>";
    foreach ($productos as $producto) {
        $cod = $producto['CodProd'];
        $nom = $producto['Nombre'];
        $des = $producto['Descripcion'];
        $peso = $producto['Peso'];
        $unidades = $_SESSION['carrito'][$cod];
        $pesoTotal += $peso * $unidades;
        $texto .= "<tr><td>$nom</td><td>$des</td><td>$peso</td><td>$unidades</td>
		<td> </tr>";
    }
    $texto .= "<tr><td>Peso Total</td><td>$pesoTotal</td> </tr>";
    $texto .= "</table>";
    return $texto;
}





/**
 *  Recibe un array de direcciones de correo, el cuerpo del correo y el asunto.
 *  Envía el correo a todas las direcciones.
 * 
 * @param array $lista_correos Array con la lista de correos
 * @param string $cuerpo Cadena de texto, que será el cuerpo del correo
 * @param string $asunto Cadena que describe el asunto del correo
 * 
 * @return boolean Devuelve false si hay algun error en los correos o no se pudo enviar, true si todo ha salido bien.
 */
function enviar_correo_multiples($lista_correos, $cuerpo, $asunto = "") {
    /*
     *
     */
    $res = leer_configCorreo(dirname(__FILE__) . "/config/correo.xml", dirname(__FILE__) . "/config/correo.xsd");
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPDebug = 0;  // cambiar a 1 o 2 para ver errores
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = "tls";
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->Username = $res[0];  //usuario de gmail
    $mail->Password = $res[1]; //contraseña de gmail          
    $mail->SetFrom('usuario_correo@gmail.com', 'Sistema de pedidos');
    $mail->Subject = utf8_decode($asunto);
    $mail->MsgHTML($cuerpo);
    /* Divide la lista de correos por la coma */
    $correos = explode(",", $lista_correos);
    foreach ($correos as $correo) {
        $mail->AddAddress($correo, $correo);
    }
    if (!$mail->Send()) {
        return $mail->ErrorInfo;
    } else {
        return TRUE;
    }
}




/**
 * Función que recibe dos parámetros: 
 * 1. $nombre: fichero de configuración con los datos (usuario y clave) para enviar un correo
 * 2. $esquema: fichero de validación,para comprobar la estructura que se espera
 * del fichero de configuración 
 * 
 * @param mixed $nombre Un fichero de configuración
 * @param mixed $esquema Un fichero de validación
 * 
 * @return int[] Devuelve un array con el usuario y su clave
 */
function leer_configCorreo($nombre, $esquema) {
    
    $config = new DOMDocument();
    $config->load($nombre);
    $res = $config->schemaValidate($esquema);
    if ($res === FALSE) {
        throw new InvalidArgumentException("Revise fichero de configuración");
    }
    $datos = simplexml_load_file($nombre);
    $usu = $datos->xpath("//usuario");
    $clave = $datos->xpath("//clave");
    $resul = [];
    $resul[] = $usu[0];
    $resul[] = $clave[0];
    return $resul;
}
