$(document).ready(function () {
    let table = $("#detallesTable").DataTable({
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
                extend: "excelHtml5",
                text: '<i class="fas fa-file-excel"></i>',
                className: "btn btn-success",
                titleAttr: "Exportar Excel", 
            },
            {
                text: '<i class="fas fa-file-pdf"></i>',
                className: "btn btn-danger",
                titleAttr: "Exportar PDF", 
                action: function (e, dt, node, config) {
                    const searchValue = table.search().trim();
                    const year = $("#year-select").val();

                    const form = $("<form>", {
                        method: "GET",
                        action: descargarPdfUrl,
                    });

                    if (searchValue) {
                        form.append(
                            $("<input>", {
                                type: "hidden",
                                name: "search",
                                value: searchValue,
                            })
                        );
                    }
                    form.append(
                        $("<input>", {
                            type: "hidden",
                            name: "year",
                            value: year,
                        })
                    );
                    $("body").append(form);
                    form.submit();
                },
            },
        ],
        lengthMenu: [
            [-1, 10, 25, 50, 100],
            ["Todo", 10, 25, 50, 100],
        ],
        pageLength: -1,
    });

    
    $('[data-toggle="tooltip"]').tooltip();
});
