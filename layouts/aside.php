<head>
    <link rel="stylesheet" href="../css/admin/time.css">
</head>

<aside class="sidebar-modern">
    <div class="sidebar-content">
        <nav class="sidebar-nav">
            <ul class="nav flex-column" >
                <li class="nav-item logo-container">
                    <img class="logo" src="../img/logo.webp" alt="Gregorio Torres Logo">
                </li>
                <li class="nav-item">
                    <a href="../admin/dashboard.php" class="nav-link">
                        <i class="bi bi-house-door-fill me-2"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <div class="nav-link collapsible-link" data-bs-toggle="collapse" href="#usuariosMenu" role="button"
                        aria-expanded="false" aria-controls="usuariosMenu">
                        <i class="bi bi-people-fill" style="margin-left: 0px;"></i> Usuarios <i class="bi bi-chevron-down ms-2" style="font-size: 1rem;"></i>
                    </div>
                    <div class="collapse" id="usuariosMenu">
                        <a href="../admin/teachers.php" class="nav-link sub-link pt-2">
                            <i class="bi bi-person-fill me-2"></i> Docentes
                        </a>
                        <a  href="../admin/students.php" class="nav-link sub-link pt-2">
                            <i class="bi bi-person-fill me-2"></i> Alumnos
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="../admin/assignments.php" class="nav-link">
                        <i class="bi bi-list-task me-2"></i> Asignaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal"
                        data-bs-target="#modalFechaLimite">
                        <i class="bi bi-calendar-date-fill me-2"></i> Plazo de Calificaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal"
                        data-bs-target="#modalAñoEscolar">
                        <i class="bi bi-calendar-event-fill me-2"></i> Ciclo escolar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#modalGrupos">
                        <i class="bi bi-diagram-3-fill me-2"></i> Grupos
                    </a>
                </li>
                <div class="modal fade" id="modalFechaLimite" tabindex="-1" aria-labelledby="modalFechaLimiteLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 id="tituloModal" class="modal-title fs-5">Configurar Plazo de Calificaciones</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <?php
                                require_once "../conection.php";
                                $fechaLimite = null;
                                $res = $conexion->query("SELECT limitDate FROM limitDate WHERE idLimitDate = 1 LIMIT 1");
                                if ($row = $res->fetch_assoc()) {
                                    $fechaLimite = $row['limitDate'];
                                }
                                ?>
                                <div class="mb-3">
                                    <label for="inputFechaLimite" class="form-label small text-muted">Fecha límite de
                                        calificaciones:</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control form-control-sm" id="inputFechaLimite"
                                            value="<?php echo $fechaLimite; ?>">
                                        <button class="btn buttonCheck btn-sm" type="button" id="btnGuardarFecha"
                                            title="Guardar fecha límite"><i class="bi bi-check-circle-fill"></i></button>
                                        <button class="btn buttonDelete1 btn-sm" type="button" id="btnQuitarFecha"
                                            title="Quitar fecha límite"><i class="bi bi-x-circle-fill"></i></button>
                                    </div>
                                    <div id="fechaLimiteInfo" class="form-text text-success mt-1 small"></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="botonEnter" type="button"
                                    data-bs-dismiss="modal">Cancelar
                                    <i class="bi bi-x-circle-fill ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalAñoEscolar" tabindex="-1" aria-labelledby="modalAñoEscolarLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content" style="width: 120%;">
                            <div class="modal-header">
                                <h1 id="tituloModal" class="modal-title fs-5">Administrar Años Escolares</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered align-middle text-center small">
                                    <thead>
                                        <tr>
                                            <th>Inicio</th>
                                            <th>Fin</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaAniosEscolares"></tbody>
                                </table>
                                <hr>
                                <div class="row g-2">
                                    <div class="col">
                                        <input type="date" class="form-control form-control-sm" id="nuevoInicio"
                                            placeholder="Inicio">
                                    </div>
                                    <div class="col">
                                        <input type="date" class="form-control form-control-sm" id="nuevoFin"
                                            placeholder="Fin">
                                    </div>
                                    <div class="col-auto">
                                        <button class="buttonAdd" id="btnAgregarAnio">Añadir<i
                                                class="bi bi-plus-lg ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="anioEscolarInfo" class="form-text text-success mt-1 small"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalGrupos" tabindex="-1" aria-labelledby="modalGruposLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 id="tituloModal" class="modal-title fs-5">Administrar Grupos</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered align-middle text-center small">
                                    <thead>
                                        <tr>
                                            <th>Grupo</th>
                                            <th>Grado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaGrupos"></tbody>
                                </table>
                                <div class="row g-2 mt-3">
                                    <div class="col">
                                        <input type="text" class="form-control form-control-sm" id="nuevoGrupo"
                                            maxlength="2" placeholder="Grupo (EJ: A)">
                                    </div>
                                    <div class="col">
                                        <input type="text" class="form-control form-control-sm" id="nuevoGrado"
                                            maxlength="2" placeholder="Grado (EJ: 1)">
                                    </div>
                                    <div class="col-auto">
                                        <button class="buttonAdd" id="btnAgregarGrupo">Agregar<i
                                                class="bi bi-plus-lg ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="grupoInfo" class="form-text text-success mt-1 small"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </ul>
        </nav>
    </div>
</aside>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputFecha = document.getElementById('inputFechaLimite');
        const btnGuardar = document.getElementById('btnGuardarFecha');
        const btnQuitar = document.getElementById('btnQuitarFecha');
        const info = document.getElementById('fechaLimiteInfo');
        if (btnGuardar) {
            btnGuardar.addEventListener('click', function () {
                const fecha = inputFecha.value;
                if (!fecha) {
                    Swal.fire({ icon: 'warning', title: 'Fecha requerida', text: 'Selecciona una fecha límite válida.' });
                    return;
                }
                fetch('../teachers/set_limit_date.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'fechaLimite=' + encodeURIComponent(fecha)
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalFechaLimite'));
                            if (modal) modal.hide();
                            setTimeout(() => {
                                Swal.fire({ icon: 'success', title: '¡Guardado!', text: 'Fecha límite guardada correctamente.' });
                                const fechaLimiteDashboard = document.getElementById('fechaLimiteDashboard');
                                if (fechaLimiteDashboard) fechaLimiteDashboard.textContent = fecha;
                            }, 400);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo guardar la fecha.' });
                        }
                    });
            });
        }
        if (btnQuitar) {
            btnQuitar.addEventListener('click', function () {
                fetch('../teachers/set_limit_date.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'quitarLimite=1'
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalFechaLimite'));
                            if (modal) modal.hide();
                            setTimeout(() => {
                                Swal.fire({ icon: 'success', title: 'Eliminado', text: 'Fecha límite eliminada.' });
                                const fechaLimiteDashboard = document.getElementById('fechaLimiteDashboard');
                                if (fechaLimiteDashboard) fechaLimiteDashboard.textContent = 'No definida';
                                inputFecha.value = '';
                            }, 400);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo eliminar la fecha.' });
                        }
                    });
            });
        }
    });
</script>

<script>
    function formatearFechaEspanol(fechaISO) {
        if (!fechaISO) return '';
        const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        const [anio, mes, dia] = fechaISO.split('-');
        return `${parseInt(dia)} de ${meses[parseInt(mes) - 1]} de ${anio}`;
    }

    function cargarAniosEscolares() {
        fetch('../admin/manage_school_years.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=list'
        })
            .then(r => r.json())
            .then(data => {
                const tbody = document.getElementById('tablaAniosEscolares');
                tbody.innerHTML = '';
                if (data.success && data.years.length) {
                    data.years.forEach(y => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td><span id='start_${y.idSchoolYear}'>${formatearFechaEspanol(y.startDate)}</span></td><td><span id='end_${y.idSchoolYear}'>${formatearFechaEspanol(y.endDate)}</span></td><td>
                        <button class='buttonEdit' onclick='mostrarEditarAnio(${y.idSchoolYear}, "${y.startDate}", "${y.endDate}")'>Editar</button>
                        <button style="height: 5vh;" class='buttonDelete1' onclick='eliminarAnioEscolar(${y.idSchoolYear})'>Borrar</button></td>`;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan='3'>Sin registros</td></tr>`;
                }
            });
    }

    function mostrarEditarAnio(id, start, end) {
        const fila = document.getElementById('start_' + id).parentElement.parentElement;
        fila.innerHTML = `<td><input type='date' class='form-control form-control-sm' id='editStart_${id}' value='${start}'></td>
        <td><input type='date' class='form-control form-control-sm' id='editEnd_${id}' value='${end}'></td>
        <td>
            <button class='btn btn-success btn-sm' onclick='guardarEdicionAnio(${id})'>Guardar</button>
            <button class='btn btn-secondary btn-sm mt-1' onclick='cargarAniosEscolares()'>Cancelar</button>
        </td>`;
    }

    function guardarEdicionAnio(id) {
        const start = document.getElementById('editStart_' + id).value;
        const end = document.getElementById('editEnd_' + id).value;
        if (!start || !end) {
            document.getElementById('anioEscolarInfo').textContent = 'Debes ingresar ambas fechas.';
            return;
        }
        fetch('../admin/manage_school_years.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=edit&idSchoolYear=${id}&startDate=${start}&endDate=${end}`
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById('anioEscolarInfo').textContent = 'Año escolar actualizado correctamente.';
                cargarAniosEscolares();
            } else {
                document.getElementById('anioEscolarInfo').textContent = data.error || 'Error al editar.';
            }
        });
    }

    function agregarAnioEscolar() {
        const inicio = document.getElementById('nuevoInicio').value;
        const fin = document.getElementById('nuevoFin').value;
        if (!inicio || !fin) {
            document.getElementById('anioEscolarInfo').textContent = 'Debes ingresar ambas fechas.';
            return;
        }
        fetch('../admin/manage_school_years.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&startDate=${inicio}&endDate=${fin}`
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById('anioEscolarInfo').textContent = 'Año escolar añadido correctamente.';
                cargarAniosEscolares();
            } else {
                document.getElementById('anioEscolarInfo').textContent = data.error || 'Error al añadir.';
            }
        });
    }

    function eliminarAnioEscolar(id) {
        if (!confirm('¿Seguro de borrar este año escolar?')) return;
        fetch('../admin/manage_school_years.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&idSchoolYear=${id}`
        }).then(r => r.json()).then(data => {
            if (data.success) cargarAniosEscolares();
        });
    }

    function cargarGrupos() {
        fetch('../admin/manage_groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:'action=list'
        })
            .then(r => r.json())
            .then(data => {
                const tbody = document.getElementById('tablaGrupos');
                tbody.innerHTML = '';
                if (data.success && data.groups.length) {
                    data.groups.forEach(g => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${g.group_}</td><td>${g.grade}</td>
                        <td><button style="height: 5vh;" class='buttonDelete1' onclick='eliminarGrupo(${g.idGroup})'>Borrar</button></td>`;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan='3'>Sin registros</td></tr>`;
                }
            });
    }

    function agregarGrupo() {
        const grupo = document.getElementById('nuevoGrupo').value.trim();
        const grado = document.getElementById('nuevoGrado').value.trim();
        if (!grupo || !grado) {
            document.getElementById('grupoInfo').textContent = 'Debes ingresar grupo y grado.';
            return;
        }
        fetch('../admin/manage_groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&group_=${encodeURIComponent(grupo)}&grade=${encodeURIComponent(grado)}`
        }).then(r => r.json()).then(data => {
            if (data.success) {
                document.getElementById('grupoInfo').textContent = 'Grupo añadido correctamente.';
                cargarGrupos();
            } else {
                document.getElementById('grupoInfo').textContent = data.error || 'Error al añadir.';
            }
        });
    }

    function eliminarGrupo(id) {
        if (!confirm('¿Seguro de borrar este grupo?')) return;
        fetch('../admin/manage_groups.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&idGroup=${id}`
        }).then(r => r.json()).then(data => {
            if (data.success) cargarGrupos();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modalAnioEscolar = document.getElementById('modalAñoEscolar');
        if (modalAnioEscolar) {
            modalAnioEscolar.addEventListener('show.bs.modal', cargarAniosEscolares);
            document.getElementById('btnAgregarAnio').onclick = agregarAnioEscolar;
        }
        const modalGrupos = document.getElementById('modalGrupos');
        if (modalGrupos) {
            modalGrupos.addEventListener('show.bs.modal', cargarGrupos);
            document.getElementById('btnAgregarGrupo').onclick = agregarGrupo;
        }
    });
</script>

<style>
    .sidebar-modern {
        background-color:rgb(236, 236, 236); /* Fondo gris claro */
        width: 190px; /* Ancho ligeramente mayor */
        min-height: 100vh; /* Para que ocupe toda la altura */
        box-shadow: 1px 10px 10px #192E4E; /* Sombra sutil */
        display: flex;
        flex-direction: column;
        padding-left: 5px;
    }

    .sidebar-content {
        padding: 5px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .sidebar-nav {
        flex-grow: 1;
    }

    .logo-container {
        padding-top: 9px;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
        margin-bottom: 1.5rem;
    }

    .logo {
        height: 79px;
        width: 70.6px;
        display: block;
        margin: 0 auto;
    }

    .nav-link {
        padding: 0.75rem 1rem;
        color:rgb(0, 0, 0); /* Texto gris oscuro */
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
        border-radius: 0.25rem;
    }

    .nav-link:hover {
        background-color:rgb(217, 220, 224);
        color: rgb(38, 75, 130);
    }

    .nav-link i {
        font-size: 1.5rem;
        margin-right: 0.5rem;
    }

    .collapsible-link {
        cursor: pointer;
        padding: 0.6rem 1rem;
        color:rgb(0, 0, 0);
        display: flex;
        align-items: center;
        border-radius: 0.25rem;
        font-size: 0.9rem;
    }

    .collapsible-link:hover {
        background-color:rgb(217, 220, 224);
        color: rgb(38, 75, 130);
    }

    .collapsible-link i {
        margin-left: auto;
    }

    .sub-link {
        padding-left: 2rem;
        font-size: 0.9rem;
    }
</style>