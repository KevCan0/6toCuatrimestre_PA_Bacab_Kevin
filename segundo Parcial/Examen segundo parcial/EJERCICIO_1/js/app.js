const formBusqueda = document.getElementById('formBusqueda');
const mostrarTodos = document.getElementById('mostrarTodos');
const tablaAlumnos = document.getElementById('tablaAlumnos');
const mensajeApi = document.getElementById('mensajeApi');

formBusqueda.addEventListener('submit', function (evento) {
    evento.preventDefault();

    const id = document.getElementById('buscarId').value.trim();
    const apellido = document.getElementById('buscarApellido').value.trim();
    const parametros = new URLSearchParams();

    if (id !== '') {
        parametros.append('id_alumno', id);
    }

    if (apellido !== '') {
        parametros.append('apellido', apellido);
    }

    let url = 'api/alumnos.php';
    if (parametros.toString() !== '') {
        url += '?' + parametros.toString();
    }

    consultarApi(url);
});

mostrarTodos.addEventListener('click', function () {
    document.getElementById('buscarId').value = '';
    document.getElementById('buscarApellido').value = '';
    consultarApi('api/alumnos.php');
});

function consultarApi(url) {
    fetch(url)
        .then(function (respuesta) {
            return respuesta.json();
        })
        .then(function (respuesta) {
            tablaAlumnos.innerHTML = '';

            if (!respuesta.ok) {
                mensajeApi.textContent = respuesta.mensaje;
                return;
            }

            mensajeApi.textContent = 'Resultados encontrados: ' + respuesta.total;

            respuesta.datos.forEach(function (alumno) {
                const fila = document.createElement('tr');

                fila.innerHTML = `
                    <td>${alumno.id_alumno}</td>
                    <td>${escapar(alumno.matricula)}</td>
                    <td>${escapar(alumno.nombre)}</td>
                    <td>${escapar(alumno.apellido_paterno + ' ' + (alumno.apellido_materno || ''))}</td>
                    <td>${escapar(alumno.correo)}</td>
                    <td>${escapar(alumno.carrera)}</td>
                    <td>${escapar(alumno.cuatrimestre)}</td>
                    <td>${escapar(alumno.estatus)}</td>
                    <td>
                        <a class="boton" href="index.php?editar=${alumno.id_alumno}">Editar</a>
                        <a class="boton rojo eliminar" href="eliminar.php?id=${alumno.id_alumno}">Eliminar</a>
                    </td>
                `;

                tablaAlumnos.appendChild(fila);
            });

            activarConfirmacion();
        })
        .catch(function () {
            mensajeApi.textContent = 'No fue posible consultar la API.';
        });
}

function escapar(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

function activarConfirmacion() {
    document.querySelectorAll('.eliminar').forEach(function (enlace) {
        enlace.onclick = function (evento) {
            if (!confirm('¿Seguro que deseas eliminar este alumno?')) {
                evento.preventDefault();
            }
        };
    });
}

activarConfirmacion();
