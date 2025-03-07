@extends('adminlte::page')

@section('title', 'Detalles')

@section('content')
    <hr>
    <div class="card">
        <div class="card-header-title card-header bg-success text-white text-center">
            <h4 class="card-title-description">Calendario de Obligaciones de TDC</h4>
        </div>

        <div class="card-body">
            <div class="divider"></div>
            <!-- Contenido del Calendario -->
            <div class="row">
                <!-- Panel de eventos -->
                <!-- <div class="col-md-3">
                                <div class="sticky-top mb-3">

                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Eventos arrastrables</h4>
                                        </div>
                                        <div class="card-body">
                                            <div id="external-events">
                                                <div class="external-event bg-success">Obligacion 1</div>
                                                <div class="external-event bg-warning">Obligacion 2</div>
                                                <div class="external-event bg-info">Obligacion 3</div>
                                                <div class="external-event bg-primary">Obligacion 4</div>
                                                <div class="external-event bg-danger">Obligacion 5</div>
                                                <div>
                                                    <label for="drop-remove">
                                                        <input type="checkbox" id="drop-remove">
                                                        Quitar después de soltar
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="card">
                                        <div class="card-header">
                                            <h3 class="card-title">Crear evento</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                                                <ul class="fc-color-picker" id="color-chooser">
                                                    <li><a class="text-primary" href="#"><i class="fas fa-square"></i></a></li>
                                                    <li><a class="text-warning" href="#"><i class="fas fa-square"></i></a></li>
                                                    <li><a class="text-success" href="#"><i class="fas fa-square"></i></a></li>
                                                    <li><a class="text-danger" href="#"><i class="fas fa-square"></i></a></li>
                                                    <li><a class="text-muted" href="#"><i class="fas fa-square"></i></a></li>
                                                </ul>
                                            </div>
                                            <div class="input-group">
                                                <input id="new-event" type="text" class="form-control"
                                                    placeholder="Título del evento">
                                                <div class="input-group-append">
                                                    <button id="add-new-event" type="button" class="btn btn-primary">Agregar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>-->

                <!-- Sección con información del módulo -->
                <div class="col-md-3">
                    <div class="sticky-top mb-3">

                        <div class="card">
                            <div class="card-header bg-success text-white text-center">
                                <h4 class="card-title container">Información del Calendario</h4>
                            </div>
                            <div class="card-body">
                                <p><strong>Calendario de Obligaciones</strong>, es una herramienta diseñada para facilitar
                                    la gestión de las obligaciones de TDC.
                                    <br>A continuación, se muestra cómo utilizarlo:
                                </p>
                                <ul>
                                    <li>
                                        <strong>Visualización:</strong> En este calendario se representan las obligaciones
                                        con sus respectivas <strong>fechas límite</strong>. Cada obligación se identifica
                                        por su título, y su color refleja su estado actual.
                                    </li>
                                    <li>
                                        <strong>Interacción:</strong> Al hacer clic en una obligación, puedes acceder a un
                                        <strong>detalle completo</strong>, que incluye su descripción, el responsable
                                        asignado y su estado de aprobación.
                                    </li>
                                    <li>
                                        <strong>Estados:</strong>
                                        <ul>
                                            <li>Los eventos <strong>aprobados</strong> se muestran en <span
                                                    style="color: green; font-weight: bold;">verde</span>.</li>
                                            <li>Los eventos <strong>pendientes</strong> o no aprobados aparecen en <span
                                                    style="color: red; font-weight: bold;">rojo</span>.</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <strong>Planificación:</strong> Este calendario te permite anticiparte a tus
                                        obligaciones y planificar tus actividades de manera eficiente, minimizando riesgos
                                        por incumplimientos.
                                    </li>
                                </ul>
                                <p>Consulta regularmente este calendario para estar al día con tus obligaciones de TDC. Esta
                                    es una herramienta para garantizar que todas las obligaciones sean atendidas de forma
                                    adecuada y oportuna.</p>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Calendario -->
                <div class="col-md-9">
                    <div class="card card-primary">
                        <div class="calendar-container card-body p-0">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->

    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Detalle de Obligación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Obligación:</strong> <span id="eventTitle"></span></p>
                    <hr>
                    <p><strong>Num. Obligación:</strong> <span id="eventObligacion"></span></p>
                    <hr>
                    <p><strong>Fecha limite de cumplimiento:</strong> <span id="eventDate"></span></p>
                    <hr>
                    <p><strong>Descripción:</strong> <span id="eventDescription"></span></p>
                    <hr>
                    <p><strong>Responsable:</strong> <span id="eventResponsable"></span></p>
                    <hr>
                    <p>
                        <span id="eventApproved" class="badge w-100"></span>
                        
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('css')
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('css/detalles/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calendario/styles.css') }}">
@stop


@section('js')

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
        $('[data-toggle="popover"]').popover();
        $('.dropdown-toggle').dropdown();
    });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js"></script>

    <script>
        const requisitosUrl = "{{ route('api.requisitos') }}";
    </script>
    <script src="{{ asset('js/gestion_obligaciones/calendario/calendario.js') }}"></script>
@stop


