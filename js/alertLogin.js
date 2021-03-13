window.onload = function () {


    function validar() {
        var boton = document.getElementById("boton");
        var usuario = document.getElementById("input_usuario").value;
        var clave = document.getElementById("clave").value;

        boton.addEventListener("click", function (event) {

            if (usuario.length == 0 || clave.lenght == 0) {
                alert('Complete los campos vacios!');
               
            }

        })

    }


validar();


}
