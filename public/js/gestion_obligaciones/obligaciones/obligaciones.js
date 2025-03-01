function isValidId(id) {
    return typeof id === "string" && id.trim().length > 0;
}

function sanitizeInput(input) {
    const element = document.createElement("div");
    element.textContent = input;
    return element.innerHTML;
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".custom-card").forEach(function (element) {
        element.addEventListener("click", function () {
            const evidenciaId = this.dataset.evidenciaId;
            const idNotificaciones = this.dataset.idNotificaciones;
            const requisitoId = this.dataset.requisitoId;
            const numeroRequisito = this.dataset.numeroRequisito;

            const firstModal = document.getElementById("modal" + requisitoId);
            if (firstModal) {
                $(firstModal).modal("hide");
            }

            obtenerDetallesEvidencia(evidenciaId, requisitoId);

            obtenerTablaNotificaciones(
                idNotificaciones,
                requisitoId,
                evidenciaId,
                numeroRequisito
            );

            axios
                .post(approvedResultUrl, { id: requisitoId })
                .then(function (response) {
                    const aprobado = response.data.approved;
                    const elementoPrueba =
                        document.querySelector(".status-alert");
                })
                .catch(function (error) {
                    console.error(
                        "Error al obtener el estado approved:",
                        error
                    );
                });

            $("#modalDetalleContent").modal("show");
            $("#modalDetalleContent").data(
                "first-modal-id",
                "modal" + requisitoId
            );

            $(".modal").not("#modalDetalleContent").attr("inert", "true");
        });
    });

    $("#modalDetalleContent").on("hidden.bs.modal", function () {
        const firstModalId = $(this).data("first-modal-id");
        if (firstModalId) {
            const firstModal = document.getElementById(firstModalId);
            if (firstModal) {
                $(firstModal).find("form").trigger("reset");
                $(firstModal).find(".collapse").collapse("hide");
                $(firstModal).modal("show");
            }
        }

        $(".modal").removeAttr("inert");
    });

    $(".modal").on("hidden.bs.modal", function () {
        $(this).find("form").trigger("reset");
        $(this).find(".collapse").collapse("hide");
        $(this).find(".info-container").empty();
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".n_evidencia").forEach(function (element) {
        element.addEventListener("click", function () {
            const evidenciaId = this.dataset.evidenciaId;
            const idNotificaciones = this.dataset.idNotificaciones;
            const requisitoId = this.dataset.requisitoId;
            const numeroRequisito = this.dataset.numeroRequisito;

            if (
                !isValidId(evidenciaId) ||
                !isValidId(idNotificaciones) ||
                !isValidId(requisitoId)
            ) {
                Swal.fire("Error", "Datos no válidos detectados.", "error");
                return;
            }

            obtenerDetallesEvidencia(evidenciaId, requisitoId);

            obtenerNotificaciones(idNotificaciones, requisitoId);

            obtenerTablaNotificaciones(
                idNotificaciones,
                requisitoId,
                evidenciaId,
                numeroRequisito
            );
        });
    });
});

function obtenerDetallesEvidencia(evidenciaId, requisitoId) {
    const year = document.getElementById("year-select").value;

    axios
        .post(obtenerDetallesEvidenciaUrl, {
            evidencia_id: evidenciaId,
            year: year,
        })
        .then(function (response) {
            let fechasLimiteHtml =
                '<ul style="list-style-type: disc; padding-left: 20px;">';
            if (
                response.data.fechas_limite_cumplimiento &&
                response.data.fechas_limite_cumplimiento.length > 0
            ) {
                response.data.fechas_limite_cumplimiento.forEach(function (
                    fecha
                ) {
                    fechasLimiteHtml += `<li><b>${sanitizeInput(
                        fecha
                    )}</b></li>`;
                });
            } else {
                fechasLimiteHtml =
                    "<p>No hay fechas límite de cumplimiento</p>";
            }
            fechasLimiteHtml += "</ul>";

            document.getElementById("detail-info-" + requisitoId).innerHTML = `
            <div class="header">
                <h5><b>${sanitizeInput(response.data.condicion)}</b></h5>
            </div>
            <div class="details-card mt-2">
                <div class="info-section">
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar"></i>
                        <span>Periodicidad:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <li><b>${sanitizeInput(
                            response.data.periodicidad
                        )}</b></li>
                    </ul>

                    <div class="section-header bg-light-grey">
                        <i class="fas fa-user"></i>
                        <span>Responsable:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <li><b>${sanitizeInput(
                            response.data.responsable
                        )}</b></li>
                    </ul>

                    <div class="section-header bg-light-grey">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Fechas límite de cumplimiento:</span>
                    </div>
                    ${fechasLimiteHtml}

                    <div class="section-header bg-light-grey">
                        <i class="fas fa-file-alt"></i>
                        <span>Origen de la obligación:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;">
                        <li><b>${sanitizeInput(
                            response.data.origen_obligacion
                        )}</b></li>
                    </ul>

                    <div class="section-header bg-light-grey">
                        <i class="fas fa-book"></i>
                        <span>Cláusula, condicionante, o artículo:</span>
                    </div>
                    ${
                        userRole === "invitado"
                            ? `
                            <p class="text-center text-muted" style="font-size: 1.0rem;"><b>Actualmente eres un usuario invitado y no puedes acceder a esta información.</b></p>
                        `
                            : `
                            <p style="text-align: justify;"><b>${sanitizeInput(
                                response.data.clausula_condicionante_articulo
                            )}</b></p>
                        `
                    }
                </div>
            </div>
        `;
        })
        .catch(function (error) {
            console.error("Error al obtener los detalles:", error);
        });
}

function obtenerNotificaciones(idNotificaciones, requisitoId) {
    axios
        .post(obtenerNotificacionesUrl, { id_notificaciones: idNotificaciones })
        .then(function (response) {
            let notificacionesHtml = `
            <div class="info-container mt-2">
                <div class="details-card">
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-bell"></i>
                        <span>Notificación:</span>
                    </div>
                    <ul style="list-style-type: disc; padding-left: 20px;"> <!-- Lista con viñetas -->
        `;

            if (response.data.length > 0) {
                response.data.forEach(function (nombre) {
                    notificacionesHtml += `<li><b>${sanitizeInput(
                        nombre
                    )}</b></li>`;
                });
            } else {
                notificacionesHtml += "<li>No hay notificaciones</li>";
            }

            notificacionesHtml += "</ul></div></div>";
            document.getElementById(
                "notificaciones-info-" + requisitoId
            ).innerHTML = notificacionesHtml;
        })
        .catch(function (error) {
            console.error("Error al obtener las notificaciones:", error);
        });
}

function obtenerTablaNotificaciones(
    idNotificaciones,
    requisitoId,
    evidenciaId,
    numeroRequisito
) {
    axios
        .post(obtenerTablaNotificacionesUrl, {
            id_notificaciones: idNotificaciones,
        })
        .then(function (response) {
            let tablaNotificacionesHtml = `
            <div class="info-container mt-2">
                <div class="details-card">
                    <div class="section-header bg-light-grey">
                        <i class="fas fa-table"></i>
                        <span>Tabla de Notificaciones:</span>
                    </div>
            `;

            const allowedRoles = ["superUsuario"];

            if (allowedRoles.includes(userRole) && response.data.length > 0) {
                tablaNotificacionesHtml += `
                <div class="d-flex justify-content-start mt-2">
                    <button class="btn btn-dark d-flex align-items-center gap-2" id="btn-agregar-${requisitoId}" onclick="mostrarFormulario(${requisitoId})">
                        <i class="fas fa-plus-circle"></i>
                        <span>Agregar a Notificaciones</span>
                    </button>
                </div>
                `;
            }
            let isSuperUsuario = userRole === "superUsuario";
            tablaNotificacionesHtml += `
                    <div id="formulario-agregar-${requisitoId}" class="mt-4 p-4 bg-light border rounded d-none">
                        <h5 class="mb-3 text-dark text-center">Agregar a Notificaciones</h5>
                        <form>
                            <!-- Campos Hidden -->
                            <input type="hidden" id="input-requisito-id-${requisitoId}" value="${numeroRequisito}">
                            <input type="hidden" id="input-notificacion-id1-${requisitoId}" value="${evidenciaId}">
                            <input type="hidden" id="input-notificacion-id2-${requisitoId}" value="${idNotificaciones}">

                            <div class="form-row">
                                <!-- Campo Puesto -->
                                <div class="form-group col-md-6">
                                    <label for="select-tipo-${requisitoId}" class="font-weight-bold">Puesto</label>
                                    <select id="select-tipo-${requisitoId}" class="form-control">
                                        <option value="">Seleccione un usuario</option>
                                    </select>
                                </div>

                                <!-- Campo Correo -->
                                <div class="form-group col-md-6">
                                    <label for="select-correo-${requisitoId}" class="font-weight-bold">Correo</label>
                                    <select id="select-correo-${requisitoId}" class="form-control" disabled>
                                        <option value="">Seleccione un correo</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Campo Notificación -->
                            <div class="form-group">
                                <label for="select-dias-${requisitoId}" class="font-weight-bold">Tipo de Notificación</label>
                                <select id="select-dias-${requisitoId}" class="form-control">
                                    <option value="primera_notificacion">1era Notificación</option>
                                    <option value="segunda_notificacion">2da Notificación</option>
                                    <option value="tercera_notificacion">3era Notificación</option>
                                </select>
                            </div>

                            <!-- Botones Centralizados -->
                            <div class="d-flex justify-content-center gap-3 mt-4">
                                <button type="button" class="btn btn-success mx-2" onclick="guardarNotificacion(${requisitoId})">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <button type="button" class="btn btn-secondary mx-2" onclick="ocultarFormulario(${requisitoId})">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive mt-1">
                        <table class="styled-table table-bordered">
                            <thead>
                                <tr>
                                    <th>Puesto</th>
                                    <th>Notificación</th>
                                    <th>Días</th>
                                    ${isSuperUsuario ? `<th>Eliminar</th>` : ""}
                                </tr>
                            </thead>
                            <tbody>
            `;

            if (response.data.length > 0) {
                response.data.forEach(function (notificacion) {
                    tablaNotificacionesHtml += `
                    <tr>
                        <td style="text-align: center;"><b>${sanitizeInput(
                            notificacion.nombre
                        )}</b></td>
                        <td style="text-align: center;"><b>${sanitizeInput(
                            notificacion.tipo
                        )}</b></td>
                        <td ${
                            notificacion.estilo
                        } style="text-align: center;"><b>${sanitizeInput(
                        notificacion.dias
                    )}</b></td>
                        ${
                            isSuperUsuario
                                ? `
                        <td style="text-align: center;">
                            <button class="btn btn-danger btn-sm" 
                                onclick="eliminarNotificacion(${notificacion.id}, ${requisitoId}, '${idNotificaciones}', '${evidenciaId}', '${numeroRequisito}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                        `
                                : ""
                        }
                    </tr>
                    `;
                });
            } else {
                tablaNotificacionesHtml += ` 
                    <tr>
                        <td colspan="${
                            isSuperUsuario ? 4 : 3
                        }" style="text-align: center;">No hay notificaciones</td>
                    </tr>`;
            }

            tablaNotificacionesHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            `;

            document.getElementById(
                "tabla-notificaciones-info-" + requisitoId
            ).innerHTML = tablaNotificacionesHtml;
        })
        .catch(function (error) {
            console.error(
                "Error al obtener la tabla de notificaciones:",
                error
            );
        });
}

function ocultarFormulario(requisitoId) {
    document
        .getElementById(`formulario-agregar-${requisitoId}`)
        .classList.add("d-none");
}

function guardarNotificacion(requisitoId) {
    const puesto = document.getElementById(`select-tipo-${requisitoId}`).value;
    const correo = document.getElementById(
        `select-correo-${requisitoId}`
    ).value;
    const notificacion = document.getElementById(
        `select-dias-${requisitoId}`
    ).value;
    const numeroRequisito = document.getElementById(
        `input-requisito-id-${requisitoId}`
    ).value;
    const evidenciaId = document.getElementById(
        `input-notificacion-id1-${requisitoId}`
    ).value;
    const idNotificaciones = document.getElementById(
        `input-notificacion-id2-${requisitoId}`
    ).value;

    if (!puesto) {
        Swal.fire({
            icon: "warning",
            title: "Puesto no seleccionado",
            text: "No se ha elegido un puesto válido. Por favor, seleccione un puesto.",
            confirmButtonText: "Aceptar",
        });
        return;
    }

    axios
        .post(guardarNotificacionUrl, {
            requisitoId,
            numeroRequisito,
            evidenciaId,
            idNotificaciones,
            nombre: puesto,
            email: correo,
            tipoNotificacion: notificacion,
        })
        .then((response) => {
            if (!response.data.success) {
                Swal.fire({
                    icon: "info",
                    title: "Por favor, seleccione otra opción.",
                    text: response.data.message,
                    confirmButtonText: "Aceptar",
                });
            } else {
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: response.data.message,
                    confirmButtonText: "Aceptar",
                }).then(() => {
                    ocultarFormulario(requisitoId);

                    obtenerTablaNotificaciones(
                        idNotificaciones,
                        requisitoId,
                        evidenciaId,
                        numeroRequisito
                    );
                });
            }
        })
        .catch((error) => {
            console.error("Error al guardar la notificación:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Ocurrió un error inesperado al guardar.",
                confirmButtonText: "Aceptar",
            });
        });
}

function eliminarNotificacion(
    notificacionId,
    requisitoId,
    idNotificaciones,
    evidenciaId,
    numeroRequisito
) {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "¡No podrás revertir esto!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            axios
                .post(eliminarNotificacionUrl, {
                    id: notificacionId,
                    requisitoId: requisitoId,
                    idNotificaciones: idNotificaciones,
                    evidenciaId: evidenciaId,
                    numeroRequisito: numeroRequisito,
                })
                .then((response) => {
                    Swal.fire("¡Eliminado!", response.data.message, "success");
                    obtenerTablaNotificaciones(
                        idNotificaciones,
                        requisitoId,
                        evidenciaId,
                        numeroRequisito
                    );
                })
                .catch((error) => {
                    console.error(error);
                    Swal.fire(
                        "Error",
                        "No se pudo eliminar la notificación.",
                        "error"
                    );
                });
        }
    });
}

function mostrarFormulario(requisitoId) {
    document
        .getElementById(`formulario-agregar-${requisitoId}`)
        .classList.remove("d-none");

    const selectPuesto = document.getElementById(`select-tipo-${requisitoId}`);
    const selectCorreo = document.getElementById(
        `select-correo-${requisitoId}`
    );

    selectPuesto.innerHTML = '<option value="">Seleccione un puesto</option>';
    selectCorreo.innerHTML = '<option value="">Seleccione un correo</option>';

    axios
        .get(usuariosUrl)
        .then((response) => {
            const usuarios = response.data;

            const puestoCorreoMap = {};

            usuarios.forEach((usuario) => {
                const optionPuesto = document.createElement("option");
                optionPuesto.value = usuario.puesto;
                optionPuesto.textContent = `${usuario.name} - ${usuario.puesto}`;
                selectPuesto.appendChild(optionPuesto);

                puestoCorreoMap[usuario.puesto] = usuario.email;
            });

            selectPuesto.addEventListener("change", function () {
                const puestoSeleccionado = this.value;
                const correoCorrespondiente =
                    puestoCorreoMap[puestoSeleccionado] || "";
                selectCorreo.innerHTML = `<option value="${correoCorrespondiente}">${
                    correoCorrespondiente || "Correo no disponible"
                }</option>`;
            });
        })
        .catch((error) => {
            console.error("Error al cargar la lista de usuarios:", error);
            alert("Ocurrió un error al cargar la lista de usuarios.");
        });
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".custom-card").forEach(function (element) {
        element.addEventListener("click", function () {
            const detalleId = this.dataset.detalleId;
            const evidenciaId = this.dataset.evidenciaId;
            const requisitoId = this.dataset.requisitoId;
            const numeroRequisito = this.dataset.numeroRequisito;
            const fechaLimiteCumplimiento =
                this.dataset.fechaLimiteCumplimiento;

            cargarArchivos(requisitoId, evidenciaId, fechaLimiteCumplimiento);

            cargarDetalleEvidencia(
                detalleId,
                evidenciaId,
                requisitoId,
                numeroRequisito
            );
        });
    });
});

function cargarDetalleEvidencia(
    detalleId,
    evidenciaId,
    requisitoId,
    numeroRequisito
) {
    if (
        !isValidId(detalleId) ||
        !isValidId(evidenciaId) ||
        !isValidId(requisitoId)
    ) {
        console.error("IDs no válidos");
        return;
    }

    axios
        .post(obtenerDetalleEvidenciaUrl, {
            evidencia_id: sanitizeInput(evidenciaId),
            detalle_id: sanitizeInput(detalleId),
            requisito_id: sanitizeInput(requisitoId),
        })
        .then(function (response) {
            const modalElement = document.getElementById("modalDetalleContent");

            if (modalElement) {
                const infoSection = modalElement.querySelector(
                    ".modal-body .info-section"
                );

                if (infoSection) {
                    let content = `
                        <div class="header">
                            <h5><b>${sanitizeInput(
                                response.data.condicion
                            )}</b></h5>
                        </div>
                        <div class="details-card mt-2">
                            <div id="modal-detalles-obligacion" class="info-section">
                                <div class="logo-container" style="text-align: right;"></div>
                                <p style="display: none;"><b>${sanitizeInput(
                                    response.data.evidencia
                                )}</b></p> 
                                <p style="display: none;"><b>${sanitizeInput(
                                    response.data.nombre
                                )}</b></p>                         
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-calendar"></i>
                                    <span>Periodicidad:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(
                                        response.data.periodicidad
                                    )}</b></li>
                                </ul>
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-user"></i>
                                    <span>Responsable:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(
                                        response.data.responsable
                                    )}</b></li>
                                </ul>    
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Fechas límite de cumplimiento:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(
                                        response.data.fecha_limite_cumplimiento
                                    )}</b></li>
                                </ul>
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Origen de la obligación:</span>
                                </div>
                                <ul style="list-style-type: disc; padding-left: 20px;">
                                    <li><b>${sanitizeInput(
                                        response.data.origen_obligacion
                                    )}</b></li>
                                </ul>
                                <div class="section-header bg-light-grey">
                                    <i class="fas fa-book"></i>
                                    <span>Cláusula, condicionante, o artículo:</span>
                                </div>
                                                            ${
                                                                userRole ===
                                                                "invitado"
                                                                    ? `
                                    <p class="text-center text-muted" style="font-size: 1.0rem;"><b>Actualmente eres un usuario invitado y no puedes acceder a esta información.</b></p>
                                `
                                                                    : `
                                    <p style="text-align: justify;"><b>${sanitizeInput(
                                        response.data
                                            .clausula_condicionante_articulo
                                    )}</b></p>
                                `
                                                            }
                            </div>
                        </div>
                        <br>
                    `;

                    const allowedRoles = ["admin", "superUsuario"];

                    if (allowedRoles.includes(userRole)) {
                        content += `
        <button class="btn btn-secondary btnMarcarCumplido w-100" id="btnMarcarCumplido" data-requisito-id="${sanitizeInput(
            response.data.id
        )}" data-responsable="${sanitizeInput(response.data.responsable)}">
            <i class=""></i> Cambiar estado de evidencia
        </button>
    `;
                    }

                    infoSection.innerHTML = content;

                    const btnMarcarCumplido =
                        document.getElementById("btnMarcarCumplido");
                    if (btnMarcarCumplido) {
                        btnMarcarCumplido.addEventListener(
                            "click",
                            function () {
                                axios
                                    .post(verificarArchivosUrl, {
                                        requisito_id:
                                            sanitizeInput(requisitoId),
                                        fecha_limite_cumplimiento:
                                            sanitizeInput(
                                                response.data
                                                    .fecha_limite_cumplimiento
                                            ),
                                        nombre_archivo: sanitizeInput(
                                            response.data.nombre_archivo
                                        ),
                                    })
                                    .then(function (verifyResponse) {
                                        if (verifyResponse.data.conteo === 0) {
                                            Swal.fire({
                                                title: "¡No hay archivos adjuntos para esta evidencia!",
                                                text: "Para poder cambiar el estatus de la evidencia se requiere mínimo un archivo adjunto.",
                                                icon: "error",
                                            });
                                        } else {
                                            actualizarEstado(
                                                detalleId,
                                                requisitoId,
                                                sanitizeInput(
                                                    response.data.responsable
                                                ),
                                                sanitizeInput(numeroRequisito)
                                            );
                                        }
                                    })
                                    .catch(function (error) {
                                        console.error(
                                            "Error al verificar los archivos:",
                                            error
                                        );
                                    });
                            }
                        );
                    }
                } else {
                    console.error(
                        "No se encontró la sección de información en el modal"
                    );
                }
            } else {
                console.error(
                    "No se encontró el modal con ID modalDetalle" +
                        sanitizeInput(detalleId)
                );
            }
        })
        .catch(function (error) {
            console.error("Error al obtener los detalles:", error);
        });
}

document.querySelectorAll(".custom-card").forEach(function (element) {
    element.addEventListener("click", function () {
        const requisitoId = this.dataset.requisitoId;
        const evidenciaId = this.dataset.evidenciaId;
        const fechaLimite = this.dataset.fechaLimiteCumplimiento;

        document.querySelector('#uploadForm input[name="requisito_id"]').value =
            requisitoId;
        document.querySelector('#uploadForm input[name="evidencia"]').value =
            evidenciaId;
        document.querySelector(
            '#uploadForm input[name="fecha_limite_cumplimiento"]'
        ).value = fechaLimite;
    });
});

function handleFileUpload(formSelector) {
    const form = document.querySelector(formSelector);
    const formData = new FormData(form);

    let requisitoId, evidenciaId, fechaLimite;
    let archivoAdjunto = form.querySelector('input[type="file"]').files[0];

    if (!archivoAdjunto) {
        Swal.fire(
            "Error",
            "Favor de verificar ya que no se tiene ningún archivo adjunto.",
            "warning"
        );
        return;
    }

    const maxFileSize = 20 * 1024 * 1024;
    if (archivoAdjunto.size > maxFileSize) {
        Swal.fire(
            "Error",
            "El archivo es demasiado grande. Comuníquese con el administrador del sistema.",
            "warning"
        );
        return;
    }

    const validFileTypes = [
        "application/pdf",
        "image/jpeg",
        "image/png",
        "image/gif",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/vnd.ms-excel",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.ms-powerpoint",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "text/plain",
        "application/zip",
        "application/x-rar-compressed",
    ];
    if (!validFileTypes.includes(archivoAdjunto.type)) {
        Swal.fire(
            "Error",
            "Tipo de archivo no permitido. Los formatos permitidos son PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, JPEG, PNG, GIF, ZIP y RAR.",
            "warning"
        );
        return;
    }

    for (var pair of formData.entries()) {
        if (pair[0] === "requisito_id") {
            requisitoId = pair[1];
        } else if (pair[0] === "evidencia") {
            evidenciaId = pair[1];
        } else if (pair[0] === "fecha_limite_cumplimiento") {
            fechaLimite = pair[1];
        }
    }

    Swal.fire({
        title: "¿Estás seguro?",
        html: "Al subir este archivo se enviará una notificación vía correo a: <br>Gerente Jurídico, <br>Jefa de Cumplimiento.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, subir archivo",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            const loader = document.getElementById("loader");
            loader.style.display = "block";

            axios
                .post(form.action, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then(function (response) {
                    Swal.fire(
                        "Éxito",
                        "El archivo se subió correctamente.",
                        "success"
                    );
                    cargarArchivos(requisitoId, evidenciaId, fechaLimite);
                    form.reset();
                })
                .catch(function (error) {
                    let errorMessage = "Error al subir el archivo.";
                    if (error.response && error.response.status === 413) {
                        errorMessage = "El archivo es demasiado grande.";
                    }
                    Swal.fire("Error", errorMessage, "error");
                })
                .finally(function () {
                    loader.style.display = "none";
                });
        }
    });
}

function correoEnviar() {
    const datosRecuperados = obtenerDatosInfoSection();

    axios
        .post(enviarCorreoDatosEvidenciaUrl, datosRecuperados)
        .then(function (response) {
            Swal.fire("Éxito", "El correo se envió correctamente.", "success");
        })
        .catch(function (error) {
            console.error("Error al enviar el correo:", error);
            Swal.fire(
                "Error",
                "Hubo un problema al enviar el correo.",
                "error"
            );
        });
}

function cargarArchivos(requisitoId, evidenciaId, fechaLimite) {
    axios
        .post(listarArchivosUrl, {
            requisito_id: sanitizeInput(requisitoId),
            evidencia_id: sanitizeInput(evidenciaId),
            fecha_limite: sanitizeInput(fechaLimite),
        })
        .then(function (response) {
            const archivos = response.data.archivos;
            const currentUserId = response.data.currentUserId;

            let container = document.getElementById("archivosContainer");
            container.innerHTML = "";

            if (archivos.length === 0) {
                container.innerHTML = `<p class="text-center text-muted">No hay archivos adjuntos</p>`;
                return;
            }

            archivos.forEach((archivo) => {
                let card = document.createElement("div");
                card.classList.add("card", "mb-3", "shadow-sm", "position-relative");

                // Generar HTML de los comentarios existentes
                let comentariosHTML = archivo.comments.length > 0 
                    ? archivo.comments.map(comentario => `
                        <div class="mb-3 p-2 bg-light rounded" id="comentario-${comentario.id}">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-comment text-primary mr-2"></i>
                                <strong>${sanitizeInput(comentario.user.name)}</strong> - 
                                <span class="text-muted">${sanitizeInput(comentario.user.puesto)}</span>
                            </div>
                            <p class="mb-1">${sanitizeInput(comentario.comment)}</p>
                            <small class="text-muted d-block">${new Date(sanitizeInput(comentario.created_at)).toLocaleString()}</small>
                
                            ${comentario.user_id === currentUserId ? `
                                <button class="btn btn-link text-danger p-0 mt-1" 
                                    onclick="eliminarComentario(${comentario.id}, ${archivo.id})"
                                    style="font-size: 0.9rem; text-decoration: none;">
                                    <span class="text-danger">Eliminar comentario</span>
                                </button>` 
                            : ""}
                        </div>
                    `).join("")
                    : `<p class="text-muted">No hay comentarios aún.</p>`;

                card.innerHTML = `
                    <div class="card-body">
                        <span class="badge badge-secondary position-absolute" style="top: 10px; right: 10px;">
                            ID: ${sanitizeInput(archivo.id)}
                        </span>

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-grow-1">
                                <h5 class="mb-1">
                                    <i class="fas fa-file-alt"></i> 
                                    ${sanitizeInput(archivo.nombre_archivo.split("_").slice(1).join("_"))}
                                </h5>
                                <p class="mb-1 text-muted">
                                    <i class="fas fa-paperclip"></i> <strong>Archivo adjunto por:</strong> 
                                    <i class="fas fa-user"></i> ${sanitizeInput(archivo.usuario)} - 
                                    <i class="fas fa-briefcase"></i> ${sanitizeInput(archivo.puesto)}
                                </p>
                                <p class="mb-1 text-muted">
                                    <i class="fas fa-calendar-alt"></i> ${new Date(sanitizeInput(archivo.created_at)).toLocaleString()}
                                </p>
                            </div>
                            
                            <!-- Botones de archivo (Ver, Descargar, Eliminar) -->
                            <div class="d-flex">
                                <button 
                                    class="btn btn-sm btn-info btn-ver-archivo mr-2" 
                                    data-url="${storageUploadsUrl}/${sanitizeInput(archivo.nombre_archivo)}"
                                    ${userRole === "invitado" ? "disabled" : ""}
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button 
                                    class="btn btn-sm btn-success btn-descargar-archivo mr-2" 
                                    data-url="${storageUploadsUrl}/${sanitizeInput(archivo.nombre_archivo)}"
                                    ${userRole === "invitado" ? "disabled" : ""}
                                >
                                    <i class="fas fa-download"></i>
                                </button>
                                <button 
                                    class="btn btn-sm btn-danger btn-eliminar-archivo" 
                                    data-id="${sanitizeInput(archivo.id)}" 
                                    data-url="${storageUploadsUrl}/${sanitizeInput(archivo.nombre_archivo)}"
                                    data-requisito-id="${sanitizeInput(requisitoId)}" 
                                    data-evidencia-id="${sanitizeInput(evidenciaId)}" 
                                    data-fecha-limite="${sanitizeInput(fechaLimite)}"
                                    ${(["admin", "superUsuario"].includes(userRole) || archivo.user_id === currentUserId ? "" : "disabled")}
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Botón para mostrar comentarios -->
                        <div class="mt-2 d-flex">
                            <button class="btn btn-sm btn-secondary mr-2" data-toggle="collapse" 
                                data-target="#comentarios-${archivo.id}" aria-expanded="false">
                                <i class="fas fa-comments"></i>
                                Comentarios (${archivo.comments_count}) 
                            </button>
                        </div>

                        <!-- Sección colapsable de Comentarios -->
                        <div class="collapse mt-2" id="comentarios-${archivo.id}">
                            <div class="card card-body bg-light p-2">
                                <!-- Formulario para agregar un nuevo comentario (ahora en la parte superior) -->
                                <div class="mb-3">
                                    <textarea class="form-control mb-2" rows="2" id="comentario-texto-${archivo.id}" placeholder="Escribe un comentario..."></textarea>
                                    <button class="btn btn-sm btn-success" onclick="agregarComentario(${archivo.id})">
                                        <i class="fas fa-paper-plane"></i> Agregar comentario
                                    </button>
                                </div>

                                <!-- Lista de comentarios -->
                                <div id="lista-comentarios-${archivo.id}">
                                    ${comentariosHTML}
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                container.appendChild(card);
            });

            // **Asignar eventos a los botones después de cargar los archivos**
            agregarEventos();
        })
        .catch(function (error) {
            console.error("Error al cargar los archivos:", error);
        });
}


function agregarComentario(archivoId) {
    let comentarioTexto = document.getElementById(`comentario-texto-${archivoId}`).value;

    if (!comentarioTexto.trim()) {
        alert("El comentario no puede estar vacío.");
        return;
    }

    axios.post(guardarComentarioUrl, { 
        archivo_id: archivoId,
        comment: comentarioTexto
    })
    .then(response => {
        let nuevoComentario = response.data.comment;

        // Construir el HTML del nuevo comentario
        let comentarioHTML = document.createElement("div");
        comentarioHTML.classList.add("mb-3", "p-2", "bg-light", "rounded");
        comentarioHTML.id = `comentario-${nuevoComentario.id}`;
        comentarioHTML.style.opacity = "0"; // 🔥 Iniciar con opacidad 0 para animación

        comentarioHTML.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-comment text-primary mr-2"></i>
                <strong>${nuevoComentario.user}</strong> - <span class="text-muted">${nuevoComentario.puesto}</span>
            </div>
            <p class="mb-1">${nuevoComentario.text}</p>
            <small class="text-muted d-block">${nuevoComentario.fecha}</small>
            <button class="btn btn-link text-danger p-0 mt-1" 
                onclick="eliminarComentario(${nuevoComentario.id}, ${archivoId})"
                style="font-size: 0.9rem; text-decoration: none;">
                <span class="text-danger">Eliminar comentario</span>
            </button>
        `;

        // Obtener el contenedor de comentarios
        let listaComentarios = document.getElementById(`lista-comentarios-${archivoId}`);

        // Si el mensaje "No hay comentarios aún" está visible, eliminarlo
        if (listaComentarios.innerHTML.includes("No hay comentarios aún")) {
            listaComentarios.innerHTML = ""; // Limpiar el mensaje
        }

        // Agregar el nuevo comentario con una animación de aparición
        listaComentarios.prepend(comentarioHTML); // Agregar comentario al inicio

        setTimeout(() => {
            comentarioHTML.style.transition = "opacity 0.5s ease-in"; 
            comentarioHTML.style.opacity = "1"; // 🔥 Aparecer lentamente
        }, 50); // Un pequeño retraso para que la animación funcione

        // Actualizar el contador de comentarios
        actualizarContadorComentarios(archivoId, 1);

        // Limpiar el textarea
        document.getElementById(`comentario-texto-${archivoId}`).value = '';
    })
    .catch(error => {
        console.error("Error al agregar el comentario:", error);
        alert("Ocurrió un error al agregar el comentario. Inténtalo de nuevo.");
    });
}





function eliminarComentario(comentarioId, archivoId) {
    let url = eliminarComentarioUrl.replace(':id', comentarioId);

    axios.delete(url)
        .then(response => {
            let comentarioElemento = document.getElementById(`comentario-${comentarioId}`);

            if (comentarioElemento) {
                
                comentarioElemento.style.transition = "opacity 0.5s ease-out"; 
                comentarioElemento.style.opacity = "0";

                
                setTimeout(() => {
                    comentarioElemento.remove();

                    
                    actualizarContadorComentarios(archivoId, -1);

                    
                    let listaComentarios = document.getElementById(`lista-comentarios-${archivoId}`);
                    if (listaComentarios.children.length === 0) {
                        listaComentarios.innerHTML = `<p class="text-muted">No hay comentarios aún.</p>`;
                    }
                }, 500); 
            }
        })
        .catch(error => {
            console.error("Error al eliminar el comentario:", error);
        });
}




function actualizarContadorComentarios(archivoId, cambio) {
    let contadorComentarios = document.querySelector(`[data-target="#comentarios-${archivoId}"]`);
    if (contadorComentarios) {
        let countTexto = contadorComentarios.innerText.match(/\d+/); 
        let count = countTexto ? parseInt(countTexto[0]) : 0; 
        let nuevoCount = Math.max(0, count + cambio); 
        contadorComentarios.innerHTML = `<i class="fas fa-comments"></i> Comentarios (${nuevoCount})`;
    }
}




function agregarEventos() {
    // Evento para ver archivos
    document.querySelectorAll(".btn-ver-archivo").forEach((button) => {
        button.addEventListener("click", function () {
            const fileUrl = this.dataset.url;
            window.open(fileUrl, "_blank");
        });
    });

    // Evento para descargar archivos
    document.querySelectorAll(".btn-descargar-archivo").forEach((button) => {
        button.addEventListener("click", function () {
            const fileUrl = this.dataset.url;
            const fileName = fileUrl.split("/").pop();

            const link = document.createElement("a");
            link.href = fileUrl;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });

    // Evento para eliminar archivos
    document.querySelectorAll(".btn-eliminar-archivo").forEach((button) => {
        button.addEventListener("click", function () {
            const archivoId = this.dataset.id;
            const archivoUrl = this.dataset.url;
            const requisitoId = this.dataset.requisitoId;
            const evidenciaId = this.dataset.evidenciaId;
            const fechaLimite = this.dataset.fechaLimite;

            Swal.fire({
                title: "¿Estás seguro?",
                text: "Este archivo se eliminará permanentemente.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    axios
                        .post(eliminarArchivoUrl, {
                            id: archivoId,
                            ruta_archivo: archivoUrl,
                        })
                        .then((response) => {
                            if (response.data.success) {
                                Swal.fire(
                                    "Eliminado",
                                    response.data.message,
                                    "success"
                                );
                                cargarArchivos(
                                    requisitoId,
                                    evidenciaId,
                                    fechaLimite
                                );
                            } else {
                                Swal.fire(
                                    "Error",
                                    response.data.message,
                                    "error"
                                );
                            }
                        })
                        .catch((error) => {
                            console.error(
                                "Error al eliminar el archivo:",
                                error
                            );
                            Swal.fire(
                                "Error",
                                "Ocurrió un problema al intentar eliminar el archivo.<br>Póngase en contacto con el administrador del sistema.",
                                "error"
                            );
                        });
                }
            });
        });
    });
}

function actualizarEstado(
    detalleId,
    requisitoId,
    responsable,
    numero_requisito
) {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Está a punto de modificar el estatus de esta obligación. Se notificará al responsable correspondiente por correo electrónico.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, cambiar estatus",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                position: "center",
                icon: "success",
                title: "El estado de la obligación ha sido cambiado.<br>Se notificará al responsable por correo electrónico.",
                showConfirmButton: false,
                timer: 6000,
            });

            actualizarPorcentaje(detalleId);
            actualizarPorcentajeSuma(detalleId, numero_requisito);

            axios
                .post(cambiarEstadoRequisitoUrl, {
                    id: detalleId,
                })
                .then(function (response) {
                    if (response.data.success) {
                        const button = document.querySelector(
                            `.btnMarcarCumplido[data-requisito-id="${requisitoId}"]`
                        );

                        const aprobado = response.data.approved;
                        const elementoPrueba =
                            document.querySelector(".status-alert");

                        if (aprobado) {
                            elementoPrueba.classList.remove("alert-danger");
                            elementoPrueba.classList.add("alert-success");
                            elementoPrueba.innerHTML =
                                '<strong><i class="fas fa-check"></i></strong> Esta evidencia ha sido marcada como revisada.';
                        } else {
                            elementoPrueba.classList.remove("alert-success");
                            elementoPrueba.classList.add("alert-danger");
                            elementoPrueba.innerHTML =
                                '<strong><i class="fas fa-times"></i></strong> Esta obligación volvió a su estatus inicial.';
                        }
                    } else {
                        console.error(
                            "Error al actualizar el estado:",
                            response.data.error
                        );
                    }
                })
                .catch(function (error) {
                    console.error("Error en la solicitud:", error);
                });
        }
    });
}

function abrirModalDetalle(detalleId, requisitoId) {
    if (!detalleId || !requisitoId) {
        console.error("detalleId o requisitoId no están definidos");
        return;
    }

    $("#modalDetalleContent").modal("show");

    axios
        .post(obtenerEstadoAprobadoUrl, {
            id: detalleId,
        })
        .then(function (response) {
            let aprobado = response.data.approved;
            const elementoPrueba = document.querySelector(".status-alert");

            if (aprobado) {
                elementoPrueba.classList.remove("alert-danger");
                elementoPrueba.classList.add("alert-success");
                elementoPrueba.innerHTML =
                    '<strong><i class="fas fa-check"></i></strong> Esta evidencia ha sido marcada como revisada.';
            } else {
                elementoPrueba.classList.remove("alert-success");
                elementoPrueba.classList.add("alert-danger");
                elementoPrueba.innerHTML =
                    '<strong><i class="fas fa-times"></i></strong> Esta evidencia no ha sido revisada o volvió a su estatus inicial.';
            }
        })
        .catch(function (error) {
            console.error("Error al obtener el estado approved:", error);
        });
}

function obtenerDatosInfoSection() {
    const infoSection = document.querySelector("#modal-detalles-obligacion");
    const datos = {};

    if (infoSection) {
        const evidenciaElement = infoSection.querySelector(
            'p[style*="display: none;"] b'
        );
        const nombreElement = infoSection.querySelector("p:nth-child(2) b");
        const periodicidadElement = infoSection.querySelector(
            ".section-header + p"
        );
        const responsableElement = infoSection.querySelector(
            ".section-header + p + .section-header + p"
        );
        const fechaLimiteElement = infoSection.querySelector(
            ".section-header + p + .section-header + p + .section-header + p"
        );
        const origenObligacionElement = infoSection.querySelector(
            ".section-header + p + .section-header + p + .section-header + p + .section-header + p"
        );
        const clausulaElement = infoSection.querySelector(
            ".section-header + p + .section-header + p + .section-header + p + .section-header + p + .section-header + p"
        );

        datos.evidencia = evidenciaElement
            ? evidenciaElement.textContent.trim()
            : "";
        datos.nombre = nombreElement ? nombreElement.textContent.trim() : "";
        datos.periodicidad = periodicidadElement
            ? periodicidadElement.textContent.trim()
            : "";
        datos.responsable = responsableElement
            ? responsableElement.textContent.trim()
            : "";
        datos.fecha_limite_cumplimiento = fechaLimiteElement
            ? fechaLimiteElement.textContent.trim()
            : "";
        datos.origen_obligacion = origenObligacionElement
            ? origenObligacionElement.textContent.trim()
            : "";
        datos.clausula_condicionante_articulo = clausulaElement
            ? clausulaElement.textContent.trim()
            : "";
    } else {
        console.error(
            'No se encontró la sección de información con la clase "info-section".'
        );
    }

    return datos;
}

function ejecutarAccionConDatos() {
    const datosRecuperados = obtenerDatosInfoSection();

    axios
        .post(enviarCorreoDatosEvidenciaUrl, datosRecuperados)
        .then(function (response) {
            Swal.fire("Éxito", "El correo se envió correctamente.", "success");
        })
        .catch(function (error) {
            console.error("Error al enviar el correo:", error);
            Swal.fire(
                "Error",
                "Hubo un problema al enviar el correo.",
                "error"
            );
        });
}

function cambiarEstadoEvidencia(requisitoId, evidenciaId) {
    axios
        .post(cambiarEstadoRequisitoUrl, {
            id: requisitoId,
        })
        .then(function (response) {
            if (response.data.success) {
                Swal.fire(
                    "Éxito",
                    "El estado de la evidencia ha sido cambiado.",
                    "success"
                );
            } else {
                throw new Error("Error al cambiar el estado");
            }
        })
        .catch(function (error) {
            console.error("Error:", error);
            Swal.fire("Error", "Hubo un problema durante el proceso.", "error");
        });
}

function actualizarPorcentaje(detalleId) {
    if (!isValidId(detalleId)) {
        console.error("ID no válido");
        return;
    }

    axios
        .post(actualizarPorcentajeUrl, {
            id: sanitizeInput(detalleId),
        })
        .then(function (response) {
            if (response.data.success) {
            } else {
                throw new Error("Error al actualizar el porcentaje");
            }
        })
        .catch(function (error) {
            console.error("Error al actualizar el porcentaje:", error);
        });
}

function actualizarPorcentajeSuma(detalleId, numeroRequisito) {
    if (!isValidId(detalleId) || !isValidId(numeroRequisito)) {
        console.error("IDs no válidos");
        return;
    }

    axios
        .post(actualizarSumaPorcentajeUrl, {
            requisito_id: sanitizeInput(detalleId),
            numero_requisito: sanitizeInput(numeroRequisito),
        })
        .then(function (response) {})
        .catch(function (error) {
            console.error("Error al contar los registros:", error);
        });
}

document.addEventListener("DOMContentLoaded", function () {
    const statusIndicators = document.querySelectorAll(".status-indicator");

    statusIndicators.forEach(function (indicator) {
        if (indicator.textContent.trim() === "Completo") {
            indicator.style.backgroundColor = "green";
        }
    });

    const avances = document.querySelectorAll(".avance-obligacion");

    avances.forEach(function (avance) {
        const valorAvance = parseInt(avance.getAttribute("data-avance"), 10);
        let colorClase = "";

        if (valorAvance >= 0 && valorAvance <= 15) {
            colorClase = "avance-rojo";
        } else if (valorAvance >= 16 && valorAvance <= 50) {
            colorClase = "avance-naranja";
        } else if (valorAvance >= 51 && valorAvance <= 99) {
            colorClase = "avance-amarillo";
        } else if (valorAvance == 100) {
            colorClase = "avance-verde";
        }

        avance.classList.add(colorClase);
    });
});

$("#modalDetalleContent").on("show.bs.modal", function () {
    $(".modal").not(this).attr("inert", "true");
});

$("#modalDetalleContent").on("hidden.bs.modal", function () {
    $(".modal").removeAttr("inert");
});

function enviarAlertaCorreo(diasRestantes) {
    axios
        .post(enviarCorreoAlertaUrl, {
            dias_restantes: diasRestantes,
        })
        .then((response) => {
            Swal.fire({
                title: "Correo Enviado",
                text: `Se ha enviado un correo para la alerta de ${diasRestantes} días.`,
                icon: "success",
            });
        })
        .catch((error) => {
            console.error("Error al enviar el correo:", error);
            Swal.fire({
                title: "Error",
                text: "Hubo un problema al enviar el correo.",
                icon: "error",
            });
        });
}
