$(function () {
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

    ini_events($("#external-events div.external-event"));

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
            $(".fc-event").hide().fadeIn(500);
        },
        eventClick: function (event) {
            $(
                "#eventTitle, #eventObligacion, #eventDate, #eventDescription, #eventResponsable, #eventApproved"
            ).fadeOut(200, function () {
                const {
                    title,
                    obligacion,
                    start,
                    description,
                    responsable,
                    approved,
                } = event;
                $("#eventTitle").text(title).fadeIn(200);
                $("#eventObligacion").text(obligacion).fadeIn(200);
                $("#eventDate").text(start.format("YYYY-MM-DD")).fadeIn(200);
                $("#eventDescription")
                    .text(description || "No hay descripción disponible.")
                    .fadeIn(200);
                $("#eventResponsable")
                    .text(responsable || "No asignado")
                    .fadeIn(200);

                const eventApproved = $("#eventApproved");
                const isApproved = approved == 1;
                eventApproved
                    .text(
                        isApproved
                            ? "Esta evidencia ha sido marcada como revisada."
                            : "Esta evidencia no ha sido revisada o volvió a su estatus inicial."
                    )
                    .removeClass(isApproved ? "bg-danger" : "bg-success")
                    .addClass(
                        isApproved
                            ? "bg-success text-white"
                            : "bg-danger text-white"
                    )
                    .fadeIn(200);
            });

            $("#eventModal").modal("show");
        },
        eventRender: function (event, element) {
            const isApproved = event.approved == 1;
            element
                .css({
                    "background-color": isApproved ? "#28a745" : "#dc3545",
                    "border-color": isApproved ? "#28a745" : "#dc3545",
                })
                .attr("title", event.title);
        },
    });

    $("#color-chooser > li > a").click(function (e) {
        e.preventDefault();
        const currColor = $(this).css("color");
        $("#add-new-event").css({
            "background-color": currColor,
            "border-color": currColor,
        });
    });

    $("#add-new-event").click(function (e) {
        e.preventDefault();
        const val = $("#new-event").val();
        if (!val.length) return;

        const event = $("<div />")
            .css({
                "background-color": "#3c8dbc",
                "border-color": "#3c8dbc",
                color: "#fff",
            })
            .addClass("external-event")
            .text(val);

        $("#external-events").prepend(event);
        ini_events(event);
        $("#new-event").val("");
    });
});
