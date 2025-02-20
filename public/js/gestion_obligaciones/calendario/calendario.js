$(function () {
    // Función para inicializar eventos
    function ini_events(ele) {
        ele.each(function () {
            const eventObject = {
                title: $.trim($(this).text()),
            };
            $(this).data("eventObject", eventObject).draggable({
                zIndex: 1070,
                revert: true,
                revertDuration: 0,
            });
        });
    }

    // Inicializar eventos externos
    ini_events($("#external-events div.external-event"));

    // Configuración del calendario
    $("#calendar").fullCalendar({
        locale: "es",
        header: {
            left: "prev,next today",
            center: "title",
            right: "month,agendaWeek,agendaDay",
        },
        editable: true,
        droppable: true,
        height: 700,
        events: requisitosUrl,
        eventAfterAllRender: function () {
            // Aplicar efecto de desvanecido a los eventos del calendario
            $(".fc-event").hide().fadeIn(500); // Ocultar y luego mostrar con fade
        },
        eventClick: function (event) {
            // Ocultar elementos con desvanecido antes de mostrar nuevos datos
            $("#eventTitle, #eventObligacion, #eventDate, #eventDescription, #eventResponsable, #eventApproved").fadeOut(200, function () {
                // Una vez ocultos, actualizar el contenido y mostrarlo con desvanecido
                const { title, obligacion, start, description, responsable, approved } = event;
                $("#eventTitle").text(title).fadeIn(200);
                $("#eventObligacion").text(obligacion).fadeIn(200);
                $("#eventDate").text(start.format("YYYY-MM-DD")).fadeIn(200);
                $("#eventDescription").text(description || "No hay descripción disponible.").fadeIn(200);
                $("#eventResponsable").text(responsable || "No asignado").fadeIn(200);

                // Estilo para el mensaje de aprobación
                const eventApproved = $("#eventApproved");
                const isApproved = approved == 1;
                eventApproved.text(
                    isApproved ? "Esta evidencia ha sido marcada como revisada."
                               : "Esta evidencia no ha sido revisada o volvió a su estatus inicial."
                ).removeClass(isApproved ? "bg-danger" : "bg-success")
                 .addClass(isApproved ? "bg-success text-white" : "bg-danger text-white")
                 .fadeIn(200);
            });

            // Mostrar el modal con desvanecido
            $("#eventModal").modal("show");
        },
        eventRender: function (event, element) {
            // Estilo para eventos en el calendario
            const isApproved = event.approved == 1;
            element.css({
                "background-color": isApproved ? "#28a745" : "#dc3545",
                "border-color": isApproved ? "#28a745" : "#dc3545",
            }).attr("title", event.title);
        },
    });

    // Selector de color para nuevos eventos
    $("#color-chooser > li > a").click(function (e) {
        e.preventDefault();
        const currColor = $(this).css("color");
        $("#add-new-event").css({
            "background-color": currColor,
            "border-color": currColor,
        });
    });

    // Agregar nuevo evento externo
    $("#add-new-event").click(function (e) {
        e.preventDefault();
        const val = $("#new-event").val();
        if (!val.length) return;

        const event = $("<div />").css({
            "background-color": "#3c8dbc",
            "border-color": "#3c8dbc",
            color: "#fff",
        }).addClass("external-event").text(val);

        $("#external-events").prepend(event);
        ini_events(event);
        $("#new-event").val("");
    });
});