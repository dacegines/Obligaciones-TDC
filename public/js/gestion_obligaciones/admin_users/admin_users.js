$(document).ready(function () {
    $("#usersTable").DataTable().destroy();
    $("#usersTable").DataTable({
        language: {
            lengthMenu:
                "Mostrar " +
                `<select class="custom-select custom-select-sm form-control form-control-sm" style="font-size: 15px;">
                    <option value='10'>10</option>
                    <option value='25'>25</option>
                    <option value='50'>50</option>
                    <option value='100'>100</option>
                    <option value='-1'>Todo</option>
                </select>` +
                " registros por página",
            zeroRecords: "No se encontró ningún registro",
            info: "Mostrando la página _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search: "Buscar:",
            paginate: {
                next: "Siguiente",
                previous: "Anterior",
            },
        },
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        dom: '<"top"Bfl>rt<"bottom"ip><"clear">',
        buttons: [
            {
                extend: "pdfHtml5",
                text: '<i class="fas fa-file-pdf"></i>',
                className: "btn btn-danger",
                titleAttr: "Exportar PDF",
            },
        ],
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "Todo"],
        ],
        pageLength: 10,
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const deleteForms = document.querySelectorAll(".delete-user-form");

    deleteForms.forEach((form) => {
        form.addEventListener("submit", function (event) {
            event.preventDefault();

            Swal.fire({
                title: "¿Estás seguro?",
                text: "Esta acción eliminará al usuario y todos sus permisos y roles asociados.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});

$(document).on("click", ".role-btn", function () {
    const userId = $(this).data("id");
    const userName = $(this).data("name");
    const userEmail = $(this).data("email");

    $("#modelIdRoleInput").val(userId);

    $("#userNameEmailRoleInput").val(`${userName} - ${userEmail}`);
});

$(document).on("click", ".area-btn", function () {
    const userId = $(this).data("id");
    const userName = $(this).data("name");
    const userEmail = $(this).data("email");

    $("#modelIdInput").val(userId);

    $("#userNameEmailInput").val(`${userName} - ${userEmail}`);
});

$(document).on("click", ".authorization-btn", function () {
    const userId = $(this).data("id");
    const userName = $(this).data("name");
    const userEmail = $(this).data("email");

    $("#modelIdAuthorization").val(userId);

    $("#userNameAuthorization").val(`${userName} - ${userEmail}`);
});

$(document).ready(function () {
    $("#create-user-form").on("submit", function (e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $("#submit-btn");

        submitBtn.prop("disabled", true);

        $.ajax({
            url: form.attr("action"),
            method: "POST",
            data: form.serialize(),
            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Usuario creado",
                    text: "El usuario se ha creado correctamente.",
                    confirmButtonText: "Aceptar",
                }).then(() => {
                    location.reload();
                });

                form[0].reset();

                submitBtn.hide();

                $("#createUserModal").modal("hide");
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors || {};
                let errorMessage =
                    "Ocurrió un error. Por favor, inténtalo nuevamente.";

                if (Object.keys(errors).length > 0) {
                    errorMessage = Object.values(errors).join("\n");
                }

                Swal.fire({
                    icon: "error",
                    title: "Error al crear usuario",
                    text: errorMessage,
                    confirmButtonText: "Aceptar",
                });
            },
            complete: function () {
                submitBtn.prop("disabled", false);
            },
        });
    });
});

$(document).ready(function () {
    $("#email").on("blur", function () {
        const email = $(this).val();
        const feedback = $("#email-feedback");

        if (email) {
            $.ajax({
                url: checkEmailUrl,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    email: email,
                },
                success: function (response) {
                    if (response.status === "error") {
                        feedback
                            .text(response.message)
                            .addClass("text-danger")
                            .removeClass("text-success");
                    } else {
                        feedback
                            .text(response.message)
                            .addClass("text-success")
                            .removeClass("text-danger");
                    }
                },
                error: function (xhr) {
                    feedback
                        .text("Ocurrió un error al validar el correo.")
                        .addClass("text-danger");
                },
            });
        } else {
            feedback.text("");
        }
    });
});

$(document).ready(function () {
    const emailField = $("#email");
    const nameField = $('input[name="name"]');
    const puestoField = $('input[name="puesto"]');
    const passwordField = $('input[name="password"]');
    const passwordConfirmField = $('input[name="password_confirmation"]');
    const feedback = $("#email-feedback");
    const submitBtn = $("#submit-btn");

    function allFieldsFilled() {
        return (
            nameField.val().trim() !== "" &&
            puestoField.val().trim() !== "" &&
            emailField.val().trim() !== "" &&
            passwordField.val().trim() !== "" &&
            passwordConfirmField.val().trim() !== ""
        );
    }

    $("input").on("input", function () {
        if (allFieldsFilled() && feedback.hasClass("text-success")) {
            submitBtn.show();
        } else {
            submitBtn.hide();
        }
    });
});

$(document).ready(function () {
    const emailField = $("#email");
    const nameField = $('input[name="name"]');
    const puestoField = $('input[name="puesto"]');
    const passwordField = $('input[name="password"]');
    const passwordConfirmField = $('input[name="password_confirmation"]');
    const feedback = $("#email-feedback");
    const submitBtn = $("#submit-btn");

    const requirements = $("#password-requirements");
    const lengthReq = $("#length");
    const uppercaseReq = $("#uppercase");
    const numberReq = $("#number");
    const specialReq = $("#special");

    const uppercaseRegex = /[A-Z]/;
    const numberRegex = /[0-9]/;
    const specialCharRegex = /[!@#$%^&*]/;

    function validateForm() {
        const emailValid = feedback.hasClass("text-success");
        const password = passwordField.val();
        const confirmPassword = passwordConfirmField.val();

        const passwordValid =
            password.length >= 8 &&
            uppercaseRegex.test(password) &&
            numberRegex.test(password) &&
            specialCharRegex.test(password);

        const passwordsMatch = password === confirmPassword;

        return (
            nameField.val().trim() !== "" &&
            puestoField.val().trim() !== "" &&
            emailField.val().trim() !== "" &&
            passwordValid &&
            passwordsMatch &&
            emailValid
        );
    }

    function toggleSubmitButton() {
        if (validateForm()) {
            submitBtn.show();
        } else {
            submitBtn.hide();
        }
    }

    passwordField.on("input", function () {
        const password = $(this).val();

        lengthReq
            .toggleClass("text-success", password.length >= 8)
            .toggleClass("text-danger", password.length < 8);

        uppercaseReq
            .toggleClass("text-success", uppercaseRegex.test(password))
            .toggleClass("text-danger", !uppercaseRegex.test(password));

        numberReq
            .toggleClass("text-success", numberRegex.test(password))
            .toggleClass("text-danger", !numberRegex.test(password));

        specialReq
            .toggleClass("text-success", specialCharRegex.test(password))
            .toggleClass("text-danger", !specialCharRegex.test(password));

        toggleSubmitButton();
    });

    passwordConfirmField.on("input", function () {
        const password = passwordField.val();
        const confirmPassword = $(this).val();
        const feedback = $("#password-feedback");

        if (password !== confirmPassword) {
            feedback
                .text("Las contraseñas no coinciden.")
                .addClass("text-danger")
                .removeClass("text-success");
        } else {
            feedback
                .text("Las contraseñas coinciden.")
                .addClass("text-success")
                .removeClass("text-danger");
        }

        toggleSubmitButton();
    });

    $("input").on("input", toggleSubmitButton);
});

$(document).ready(function () {
    const passwordField = $("#password");
    const submitBtn = $("#submit-btn");

    const requirements = $("#password-requirements");
    const lengthReq = $("#length");
    const uppercaseReq = $("#uppercase");
    const numberReq = $("#number");
    const specialReq = $("#special");

    const uppercaseRegex = /[A-Z]/;
    const numberRegex = /[0-9]/;
    const specialCharRegex = /[!@#$%^&*]/;

    passwordField.on("focus", function () {
        requirements.removeClass("d-none");
    });

    passwordField.on("input", function () {
        const password = $(this).val();

        lengthReq
            .toggleClass("text-success", password.length >= 8)
            .toggleClass("text-danger", password.length < 8);

        uppercaseReq
            .toggleClass("text-success", uppercaseRegex.test(password))
            .toggleClass("text-danger", !uppercaseRegex.test(password));

        numberReq
            .toggleClass("text-success", numberRegex.test(password))
            .toggleClass("text-danger", !numberRegex.test(password));

        specialReq
            .toggleClass("text-success", specialCharRegex.test(password))
            .toggleClass("text-danger", !specialCharRegex.test(password));

        const allValid =
            password.length >= 8 &&
            uppercaseRegex.test(password) &&
            numberRegex.test(password) &&
            specialCharRegex.test(password);
        submitBtn.toggle(allValid);
    });

    passwordField.on("blur", function () {
        const password = $(this).val();

        if (
            password.length >= 8 &&
            uppercaseRegex.test(password) &&
            numberRegex.test(password) &&
            specialCharRegex.test(password)
        ) {
            requirements.addClass("d-none");
        }
    });
});

$(document).ready(function () {
    const passwordField = $('input[name="password"]');
    const passwordConfirmField = $('input[name="password_confirmation"]');
    const feedback = $("#password-feedback");
    const submitBtn = $("#submit-btn");

    passwordConfirmField.on("input", function () {
        const password = passwordField.val();
        const confirmPassword = $(this).val();

        if (password !== confirmPassword) {
            feedback
                .text(
                    "Las contraseñas no coinciden. Por favor verifica e inténtalo nuevamente."
                )
                .addClass("text-danger")
                .removeClass("text-success");
            submitBtn.hide();
        } else {
            feedback
                .text("Las contraseñas coinciden.")
                .addClass("text-success")
                .removeClass("text-danger");
            if (validateForm()) {
                submitBtn.show();
            }
        }
    });

    function validateForm() {
        const password = passwordField.val();
        const confirmPassword = passwordConfirmField.val();

        return (
            $('input[name="name"]').val().trim() !== "" &&
            $('input[name="puesto"]').val().trim() !== "" &&
            $('input[name="email"]').val().trim() !== "" &&
            password.trim() !== "" &&
            confirmPassword.trim() !== "" &&
            password === confirmPassword
        );
    }
});

$(document).ready(function () {
    const passwordField = $('input[name="password"]');
    const passwordConfirmField = $('input[name="password_confirmation"]');
    const feedback = $("#password-feedback");
    const submitBtn = $("#submit-btn");

    passwordConfirmField.on("blur", function () {
        const password = passwordField.val();
        const confirmPassword = $(this).val();

        if (password !== confirmPassword) {
            feedback
                .text("Las contraseñas no coinciden.")
                .addClass("text-danger")
                .removeClass("text-success");
            submitBtn.hide();
        } else if (password === "") {
            feedback.text("");
            submitBtn.hide();
        } else {
            feedback
                .text("Las contraseñas coinciden.")
                .addClass("text-success")
                .removeClass("text-danger");
            if (allFieldsFilled()) {
                submitBtn.show();
            }
        }
    });

    function allFieldsFilled() {
        return (
            $('input[name="name"]').val().trim() !== "" &&
            $('input[name="puesto"]').val().trim() !== "" &&
            $('input[name="email"]').val().trim() !== "" &&
            passwordField.val().trim() !== "" &&
            passwordConfirmField.val().trim() !== ""
        );
    }

    $("input").on("input", function () {
        if (allFieldsFilled() && feedback.hasClass("text-success")) {
            submitBtn.show();
        } else {
            submitBtn.hide();
        }
    });
});

$(document).on("click", ".toggle-password", function () {
    const target = $(this).data("target");
    const input = $(target);
    const icon = $(this).find("i");

    if (input.attr("type") === "password") {
        input.attr("type", "text");
        icon.removeClass("fa-eye").addClass("fa-eye-slash");
    } else {
        input.attr("type", "password");
        icon.removeClass("fa-eye-slash").addClass("fa-eye");
    }
});

$(document).ready(function () {
    $(document).on("click", ".edit-user-btn", function () {
        const userId = $(this).data("id");
        const userName = $(this).data("name");
        const userEmail = $(this).data("email");
        const userPuesto = $(this).data("puesto");

        $("#editUserId").val(userId);
        $("#editUserName").val(userName);
        $("#editUserEmail").val(userEmail);
        $("#editUserPuesto").val(userPuesto);

        cargarObligaciones(userId);

        $("#editUserModal").modal("show");
    });
});

function cargarObligaciones(userId) {
    $.ajax({
        url: `${getObligacionesUsuarioUrl}/${userId}`,
        type: "GET",
        dataType: "json",
        success: function (obligaciones) {
            const container = $("#switchContainer");
            container.empty();

            let groupedObligaciones = {};

            obligaciones.forEach((obligacion) => {
                if (!groupedObligaciones[obligacion.nombre]) {
                    groupedObligaciones[obligacion.nombre] = [];
                }
                groupedObligaciones[obligacion.nombre].push(obligacion);
            });

            Object.keys(groupedObligaciones).forEach((nombre, index) => {
                let obligacionesGrupo = groupedObligaciones[nombre];
                let collapseId = `collapse-${index}`;

                let button = $(`
                    <button class="btn btn-secondary  btn-block text-center mb-3 border rounded p-2 font-weight-bold shadow-sm" 
                        type="button" data-toggle="collapse" data-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
                        ${nombre}
                    </button>
                `);

                let collapseDiv = $(
                    `<div class="collapse border rounded p-3 bg-white shadow-sm mb-3" id="${collapseId}"></div>`
                );
                let rowDiv = $("<div>").addClass("row w-100");

                obligacionesGrupo.forEach((obligacion) => {
                    let numeroEvidencia = obligacion.numero_evidencia;
                    let evidenciaTexto = `${numeroEvidencia} - ${obligacion.evidencia}`;
                    let isChecked = obligacion.view == 1 ? "checked" : "";

                    let colDiv = $("<div>").addClass(
                        "col-md-6 d-flex flex-column align-items-center mb-3"
                    );

                    colDiv.html(`
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-switch" id="switch-${numeroEvidencia}" 
                                name="${numeroEvidencia}" data-user="${userId}" ${isChecked}>
                            <label class="custom-control-label font-weight-bold" for="switch-${numeroEvidencia}">${evidenciaTexto}</label>
                        </div>
                    `);

                    rowDiv.append(colDiv);
                });

                if (obligacionesGrupo.length % 2 !== 0) {
                    let emptyCol = $("<div>").addClass("col-md-6");
                    rowDiv.append(emptyCol);
                }

                collapseDiv.append(rowDiv);
                container.append(button);
                container.append(collapseDiv);
            });

            setTimeout(() => {
                $(".toggle-switch").each(function () {
                    let isChecked = $(this).attr("checked") ? true : false;
                    $(this).prop("checked", isChecked);
                });
            }, 300);
        },
        error: function (xhr, status, error) {},
    });
}

$(document).on("change", ".toggle-switch", function () {
    const numeroEvidencia = $(this).attr("id").replace("switch-", "");
    const checked = $(this).is(":checked") ? 1 : 0;
    const userId = $("#editUserId").val();

    $.ajax({
        url: toggleObligacionUrl,
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            user_id: userId,
            numero_evidencia: numeroEvidencia,
            checked: checked,
        },
        success: function (response) {},
        error: function (xhr, status, error) {},
    });
});

$(document).on("click", "#deleteRole", function () {
    const userId = $("#modelIdRoleInput").val();

    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Deseas eliminar el rol actual de este usuario?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: removeRoleUrl,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    model_id: userId,
                    model_type: "AppModelsUser",
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            title: "¡Éxito!",
                            text: "Rol eliminado correctamente.",
                            icon: "success",
                            confirmButtonText: "Aceptar",
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        title: "Error",
                        text: "Hubo un error al eliminar el rol. Por favor, inténtalo de nuevo.",
                        icon: "error",
                        confirmButtonText: "Aceptar",
                    });
                },
            });
        }
    });
});

$(document).on("click", "#deletePermissionButton", function () {
    const userId = $("#modelIdInput").val();

    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Deseas eliminar el permiso actual de este usuario?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: removePermissionUrl,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    model_id: userId,
                    model_type: "AppModelsUser",
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            title: "¡Éxito!",
                            text: "Permiso eliminado correctamente.",
                            icon: "success",
                            confirmButtonText: "Aceptar",
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        title: "Error",
                        text: "Hubo un error al eliminar el permiso. Por favor, inténtalo de nuevo.",
                        icon: "error",
                        confirmButtonText: "Aceptar",
                    });
                },
            });
        }
    });
});

$(document).on("click", "#deleteAuthorizationButton", function () {
    const userId = $("#modelIdAuthorization").val();

    Swal.fire({
        title: "¿Estás seguro?",
        text: "¿Deseas eliminar la autorización actual de este usuario?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: removeAuthorizationUrl,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    model_id: userId,
                    model_type: "AppModelsUser",
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire({
                            title: "¡Éxito!",
                            text: "Autorización eliminada correctamente.",
                            icon: "success",
                            confirmButtonText: "Aceptar",
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        title: "Error",
                        text: "Hubo un error al eliminar la autorización. Por favor, inténtalo de nuevo.",
                        icon: "error",
                        confirmButtonText: "Aceptar",
                    });
                },
            });
        }
    });
});
