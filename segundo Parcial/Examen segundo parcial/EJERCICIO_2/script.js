function obtenerFecha() {
    fetch("fecha.php")
        .then(function(respuesta) {
            return respuesta.json();
        })
        .then(function(datos) {
            document.getElementById("resultado").innerHTML =
                "Fecha: " + datos.fecha + "<br>" +
                "Hora: " + datos.hora;
        })
        .catch(function(error) {
            document.getElementById("resultado").innerHTML =
                "Ocurrió un error al consultar la fecha.";
        });
}
