<?php

/**
 * Función que devuelve el puntero a la conexión a la bbdd.
 * @return [type] puntero de la bbdd.
 */
function loadBBDD() {
    
    try {
        $res = leer_config(dirname(__FILE__) . "/config/configuracion.xml", dirname(__FILE__) . "/config/configuracion.xsd");
        $bd = new PDO($res[0], $res[1], $res[2]);
        return $bd;
    } catch (\Exception $e) {
        echo $e->getMessage();
        exit();
    }
}




/**
 * Función que recibe dos rutas de ficheros, con el fin de obtener la cadena de conexion a la bbdd, el nombre de usuario y la contraseña.
 * 
 * La función comprobara que las datos para conectarse a la bbdd son correctos.
 * 
 * 1 Recibe la ruta del fichero de configuración que tiene los datos a la bbdd.
 * 2 Recibe la ruta del fichero XSD para validar los datos de la ruta del fichero anterior.
 * 3 Si todo está correcto, se obtiene mediante un array los datos para la conexión a la bbdd.
 * 
 * 
 * @param string $fichero_config_BBDD Ruta del fichero con los datos de la conexión a la bbdd.
 * @param string $esquema Ruta del fichero XSD para validar la estructura del fichero anterior.
 * 
 * @return mixed Si el fichero de configuración existe y es válido, devuelve un array con tres
 *  valores: la cadena de conexión, el nombre de usuario y la clave.
 * Si no encuentra el fichero o no es válido, lanza una excepción.
 */
function leer_config($fichero_config_BBDD, $esquema) {
    

    $config = new DOMDocument();
    $config->load($fichero_config_BBDD);
    $res = $config->schemaValidate($esquema);
    if ($res === FALSE) {
        throw new InvalidArgumentException("Revise el fichero de configuración");
    }
    $datos = simplexml_load_file($fichero_config_BBDD);
    $ip = $datos->xpath("//ip");
    $nombre = $datos->xpath("//nombre");
    $usu = $datos->xpath("//usuario");
    $clave = $datos->xpath("//clave");
    $cad = sprintf("mysql:dbname=%s;host=%s", $nombre[0], $ip[0]);
    $resul = [];
    $resul[] = $cad;
    $resul[] = $usu[0];
    $resul[] = $clave[0];
    return $resul;
}



/**
 * Función que recupera la contraseña encriptada de la bbdd, por medio del parametro nombre.
 * 
 * 
 * @param string $nombre Es el correo electronico del usuario que va a realizar el pedido
 * 
 * @return mixed Devuelve la contraseña, que se obtiene por medio de la variable resul donde se utiliza la función fetch para obtener este dato, 
 * en caso contrario devuelve false.
 */
function loadPass($nombre) {

    $bd = loadBBDD();
    $ins = "select clave from restaurantes where correo= '$nombre'";
    $stmt = $bd->query($ins);
    $resul = $stmt->fetch();
    $devol = false;
    if ($resul !== false) {
        $devol = $resul['clave'];
    }
    return $devol;
}



/**
 * Función que comprueba los datos recibidos por el login.
 * 
 * La función comprobara cada dato introducido:
 * 
 * 1 Comprueba el nombre
 * 2 Comprueba la contraseña 
 * 3 Si los datos son correctos, devuelve un array con dos campos: codRes (el código del restaurante) y correo 
 * con su correo. En caso de error devuelve false.
 * 
 * 
 * @param string $nombre Nombre del usuario formato cadena de texto
 * @param string $clave Clava introducida por el usuario, una cadena
 * 
 * @return mixed Devuelve un array con dos campos, codRes (codigo del restaurante) y su correo electronico. Caso contrario devuelve false.
 */
function comprobar_usuario($nombre, $clave) {

   
    $devol = FALSE;
    $bd = loadBBDD();
    $hash = loadPass($nombre);
    if (password_verify($clave, $hash)) {
        $ins = "select codRes, correo from restaurantes where correo = '$nombre' ";
        $resul = $bd->query($ins);
        if ($resul->rowCount() === 1) {
            $devol = $resul->fetch();
        }
    }
    return $devol;
}




/**
 * Función que devuelve un puntero con el código y nombre de las categorías de la bbdd, o falso
 * si se produjo un error.
 * 
 * 
 * @return mixed Devuelve el puntero con el codigo de las categoras de la bbdd, o false en caso de error.
 */
function cargar_categorias() {
   
    $bd = loadBBDD();
    $ins = "select codCat, nombre from categoria";
    $resul = $bd->query($ins);
    if (!$resul) {
        return FALSE;
    }
    if ($resul->rowCount() === 0) {
        return FALSE;
    }
    //si hay 1 o más
    return $resul;
}



/**
 *  Función que recibe el código de una categoría y devuelve un array con su nombre y descripción.
 * Si hay algún error en la BBDD o la categoría no existe devuelve FALSE
 * 
 * @param int $codCat Código de una categoría
 * 
 * @return mixed Si el código es correcto devuelve un array en caso contrario devuelve false.
 */
function cargar_categoria($codCat) {
   
    $bd = loadBBDD();
    $ins = "select nombre, descripcion from categoria where codcat = $codCat";
    $resul = $bd->query($ins);
    if (!$resul) {
        return FALSE;
    }
    if ($resul->rowCount() === 0) {
        return FALSE;
    }
    //si hay 1 o más
    return $resul->fetch();
}


/**
 *  Función que recibe el código de una categoría y devuelve un puntero (PDOStatement) con los 
 * productos que tienen stock, incluyendo todas las columnas de la BBDD.
 * 
 * @param int $codCat Código entero de una categoría
 * 
 * @return boolean Devuelve un puntero con los productos, caso contrario devuelve false.
 */
function cargar_productos_categoria($codCat) {
    
    $bd = loadBBDD();
    $sql = "select * from productos where codCat  = $codCat AND stock>0";
    $resul = $bd->query($sql);
    if (!$resul) {
        return FALSE;
    }
    if ($resul->rowCount() === 0) {
        return FALSE;
    }
    //si hay 1 o más
    return $resul;
}



/**
 * Nos devuelve la categoría de un producto indicando su código o FALSE si se
 * ha producido un error.
 * 
 * @param int $codProd Codigo entero de un producto.
 * 
 * @return mixed Devuelve la categoría de un producto con su código, caso contrario devuelve false.
 */
function cargar_categoria_codProducto($codProd) {
    
    $bd = loadBBDD();
    $sql = "select CodCat from productos where CodProd  = $codProd";
    $resul = $bd->query($sql);
    if (!$resul) {
        return FALSE;
    }
    if ($resul->rowCount() === 1) {
        return $resul->fetch();
    }
    //si hay 1 o más
    return false;
}






/**
 * Función que obtiene la información de los productos que se le pasa como parámetro en
 * forma de un array de códigos de productos.
 * 
 * @param int[] $codigosProductos Un array de enteros con los códigos de los productos.
 * 
 * @return mixed Devuelve la información de los productos separados por comas, puede devolver false si los codigos no son correctos.
 */
function cargar_productos($codigosProductos) {
    
    $bd = loadBBDD();
    //Para crear la lista de procutos como un texto separado por comas.
    $texto_in = implode(",", $codigosProductos);
    $ins = "select * from productos where codProd in($texto_in)";
    $resul = $bd->query($ins);
    if (!$resul) {
        return FALSE;
    }
    return $resul;
}





/**
 * Función que inserta el pedido en la BBDD. 
 * 
 * Recibe el carrito de la compra y el código del
 * restaurante que realiza el pedido. Si todo va bien, devuelve el código del nuevo 
 * pedido. Si hay algún error devuelve FALSE.
 * Para ello hay que:
 * 1. Crear una nueva fila en la tabla pedidos.
 * 2. Crear una fila en la tabla PedidosProductos por cada producto diferente que
 * se pida, usando la clave del nuevo pedido.
 * 3. Hay que actualizar el stock de cada producto por cada producto del pedido.
 * 
  * Todas las insercciones tienen que realizarse como una transacción.

 * @param int[] $carrito Array de enteros con los códigos del pedido
 * @param int $codRes Código entero del restaurante
 * 
 * @return int Devuelve el codigo del pedido
 */
function insertar_pedido($carrito, $codRes) {
   
    
    $bd = loadBBDD();
    $bd->beginTransaction();
    $pesototal = 0;
    $hora = date("Y-m-d H:i:s", time());
    // insertar el pedido
    $sql = "insert into pedidos(fecha, enviado, restaurante) 
			values('$hora',0, $codRes)";
    $resul = $bd->query($sql);
    if (!$resul) {
        return FALSE;
    }
    // coger el id del nuevo pedido para las filas detalle
    $pedido = $bd->lastInsertId();
    // insertar las filas en pedidoproductos
    foreach ($carrito as $codProd => $unidades) {
        $sql = "insert into pedidosproductos(CodPed, CodProd, Unidades) 
		             values( $pedido, $codProd, $unidades)";
        $resul = $bd->query($sql);


        $stmt = $bd->query("Select stock from productos where codprod=$codProd");
        list($stock) = $stmt->fetch();
        $sql2 = "UPDATE productos set stock=? where codProd=?";
        $stmt = $bd->prepare($sql2);
        $stock -= $unidades;
        $resultado = $stmt->execute(array($stock, $codProd));



        if (!$resul || !$resultado) {
            $bd->rollback();
            return FALSE;
        }
    }
    $bd->commit();
    return $pedido;  //devuelve el código del nuevo pedido
}
